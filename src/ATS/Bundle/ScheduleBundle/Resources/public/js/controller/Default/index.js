'use strict';

/*!
 * Home page javascript file.
 * Bootstrap for all things javascript.
 * 
 * @author Austin Shinpaugh
 */

(function ($) {
    'use strict';

    var scheduler = void 0;

    if (!$.fullCalendar) {
        console.log('FullCalendar is not loaded.');
        return;
    }

    /*
     * Setup the modal filters.
     * 
     * Using window load to ensure that the data in GlobalUtils was parsed.
     */
    $(window).on('load', function () {
        var update = void 0,
            days = void 0;
        update = GlobalUtils.getLastUpdate();
        days = moment().diff(moment(update.start), 'days');

        if (0 === update.status) {
            GlobalUtils.toggleExportBtn();

            GlobalUtils.showMessage('Our system is currently undergoing maintenance and may show limited results.', 'Notice');
        } else if (0 < days) {
            GlobalUtils.showMessage('The following data is %n day(s) old.'.replace('%n', days), 'Notice');
        }

        // Remove the loading spinner.
        $('#calendar').find('.row').remove();

        scheduler = new Scheduler('#calendar');
        scheduler.init();

        bindDelegated();
        populateFilters();
        buttonActions();

        // Setup the hovertext in the filter modal.
        $('[data-toggle="tooltip"]').tooltip();
    });

    /**
     * Fill the filter boxes with their respective data.
     */
    function populateFilters() {
        fillSelect('#subject', GlobalUtils.getSubjects());
        fillSelect('#term', GlobalUtils.getSemesters());
        fillSelectWithGroup('#instructor', GlobalUtils.getInstructors());

        bindSemesterChange();
        bindSubjectChange();
        bindChange();
    }

    /**
     * Fill a select field and set it up with Chosen.
     *
     * @param {string} id
     * @param {object} data
     * @param {string} text
     */
    function fillSelect(id, data, text) {
        var select = void 0,
            found = void 0,
            idx = void 0;
        found = false;
        select = $(id);

        for (idx in data) {
            if (!data.hasOwnProperty(idx)) {
                return;
            }

            var item = void 0,
                label = void 0,
                option = void 0;
            item = data[idx];
            label = determineChosenLabel(item);
            option = $('<option>').attr('value', item.id).text(determineChosenLabel(item));

            if (text && text === label) {
                found = true;
                option.attr('selected', 'selected');
            }

            option.appendTo(select);
        }

        if (text && !found) {
            GlobalUtils.clearSelect(select);
        } else if (!select.siblings('.chosen-container')[0]) {
            GlobalUtils.buildChosen(select);
        } else {
            select.trigger('chosen:updated');
        }
    }

    /**
     * Build a select menu with that has subgroups.
     * 
     * @param id
     * @param data
     */
    function fillSelectWithGroup(id, data) {
        var select = void 0,
            idx = void 0,
            group = void 0;
        select = $(id);

        for (idx in data) {
            if (!data.hasOwnProperty(idx)) {
                return;
            }

            if (group = _fillOptGroup(idx, data[idx])) {
                group.appendTo(select);
            }
        }

        GlobalUtils.buildChosen(select);
    }

    /**
     * Builds a optgroup subtree.
     * 
     * @param parent_id
     * @param data
     * @returns {*}
     * @private
     */
    function _fillOptGroup(parent_id, data) {
        if (!data) {
            return false;
        }

        var group = void 0,
            instructors = void 0,
            idx = void 0;

        group = $('<optgroup>').attr('label', data.name);
        instructors = data['instructors'];

        for (idx in instructors) {
            if (!instructors.hasOwnProperty(idx)) {
                continue;
            }

            var item = instructors[idx];
            if (!item.hasOwnProperty('name') || !item.name.length) {
                continue;
            }

            $('<option>').attr('value', item.id).attr('data-subject', parent_id).text(determineChosenLabel(item)).appendTo(group);
        }

        return group;
    }

    /**
     * Determines an appropriate option display text based on the information
     * provided from the entity that the user is selecting from.
     * 
     * @param {object} entity
     * 
     * @returns string
     */
    function determineChosenLabel(entity) {
        if (entity.hasOwnProperty('display_name')) {
            return entity.display_name;
        }

        if (!entity.hasOwnProperty('level')) {
            return entity.name;
        }

        return entity.number + ' | ' + entity.name;
    }

    /**
     * Whenever a change in semester selection happens, update the
     * term-block selector.
     */
    function bindSemesterChange() {
        $('#term').on('change', function (event, params) {
            var label = void 0,
                semesters = void 0,
                semester = void 0,
                block = void 0,
                idx = void 0;
            label = $('label[for="term-block"]');
            semesters = GlobalUtils.getSemesters();

            if (!params) {
                // params is undefined when you deselect a semester.
                $('#term-block').chosen('destroy');
                label.addClass('hidden');
                return;
            }

            for (idx in semesters) {
                if (!semesters.hasOwnProperty(idx)) {
                    continue;
                }

                semester = semesters[idx];
                if (semester.id != params.selected) {
                    continue;
                }

                block = $('#term-block');
                // If there are other options in the term-block selector, remove them.
                block.find('option[value]').remove();
                block.show();

                // Show the label.
                label.removeClass('hidden');

                // Fill the term-block selector.
                fillSelect('#term-block', semester.blocks, 'Full Semester');
            }

            addColorPicker('term-block');
        });

        $('#term-block').on('chosen:ready', function () {
            var block = $(this);

            setTimeout(function () {
                $('#term').trigger('chosen:close');
                block.trigger('chosen:activate');
            }, 25);
        });
    }

    /**
     * Whenever a change in subject selection happens, update the
     * course number selector.
     */
    function bindSubjectChange() {
        $('#subject').on('change', function (event, params) {
            _checkInstructorImpact(this, params);

            var number = void 0,
                subjects = void 0,
                label = void 0,
                subject = void 0,
                idx = void 0;
            number = $('#number');
            subjects = GlobalUtils.getSubjects();
            label = $('label[for="number"]');

            if (!params) {
                // params is undefined when you deselect a subject.
                number.chosen('destroy');
                label.addClass('hidden');

                return;
            }

            if (params.hasOwnProperty('deselected')) {
                number.find('option[data-subject="' + params.deselected + '"]').remove();

                if (!$(this).val().length) {
                    number.chosen('destroy');
                    label.addClass('hidden');
                } else {
                    number.trigger('chosen:updated');
                }

                return;
            }

            var changed = false;
            for (idx in subjects) {
                if (!subjects.hasOwnProperty(idx)) {
                    continue;
                }

                subject = subjects[idx];
                if (subject.id != params.selected) {
                    continue;
                }

                fillSelect('#number', subject.courses);

                number.find('option:not([data-subject])').attr('data-subject', subject.id);

                changed = true;
            }

            if (changed) {
                label.removeClass('hidden');
                number.trigger('chosen:updated');
            }

            addColorPicker('subject');
        });
    }

    /**
     * Only show instructors that have taught for the selected subjects.
     * 
     * @param target
     * @param params
     * @private
     */
    function _checkInstructorImpact(target, params) {
        var instructor = void 0,
            instructors = void 0,
            subject_ids = void 0,
            idx = void 0;
        instructor = $('#instructor');
        instructors = GlobalUtils.getInstructors();
        subject_ids = $(target).val();

        if (!params) {
            instructor.val('');
            fillSelectWithGroup(instructor, instructors);

            return;
        }

        if (params.hasOwnProperty('deselected')) {
            instructor.find('option[data-subject="' + params.deselected + '"]').remove();

            if (!subject_ids.length) {
                fillSelectWithGroup(instructor, instructors);
            }

            instructor.trigger('chosen:updated');

            return;
        }

        instructor.find('optgroup').remove();

        for (idx in subject_ids) {
            if (!subject_ids.hasOwnProperty(idx)) {
                continue;
            }

            var id = void 0,
                data = void 0;
            id = subject_ids[idx];
            data = {};
            data[id] = instructors[id];

            fillSelectWithGroup(instructor, data);
        }

        instructor.trigger('chosen:updated');
    }

    /**
     * Add a color-pickers to various fields after an item selection changes.
     */
    function bindChange() {
        $('#instructor, #term-block').on('change', function (e) {
            addColorPicker(e.currentTarget.id);
        });
    }

    /**
     * Add a color picker to a selected filter.
     * 
     * @param type
     */
    function addColorPicker(type) {
        $('#' + type.replace('-', '_') + '_chosen li.search-choice').each(function () {
            // Ignore if the element already has a color picker.
            if ($(this).children('input')[0]) {
                return;
            }

            var ele = $('<input>').attr({
                'type': 'text',
                'value': '#001505',
                'data-type': type,
                'data-unique': $(this).text()
            });

            ele.prependTo(this);
            ele.spectrum({
                change: function change(color) {
                    // Update the event background color.
                    var picker = void 0,
                        type = void 0,
                        unique = void 0;
                    picker = $(this);
                    type = picker.data('type');
                    unique = picker.data('unique');

                    scheduler.setColor(type, unique, color.toHexString());
                }
            });
        });
    }

    /**
     * Binds the page buttons to the related actions.
     */
    function buttonActions() {
        var modal = $('#filtersModal');
        modal.find('#apply-filters').on('click', function () {
            // Clear anything that was set in the tooltip box.
            $('.mobile-tooltip').html('');

            if (scheduler.fetch()) {
                modal.modal('hide');
            }
        });

        modal.find('#clear-filters').on('click', function () {
            // Clear anything that was set in the tooltip box.
            $('.mobile-tooltip').html('');

            scheduler.clearFilters();

            // Clear the course numbers.
            $('#number').find('option').remove();

            // Reset the instructor menu to its page load state.
            fillSelectWithGroup('instructor', GlobalUtils.getInstructors());
        });

        $('#clear-calendar').click(function () {
            // Clear anything that was set in the tooltip box.
            $('.mobile-tooltip').html('');

            scheduler.clear();
        });

        $('#btn-export').on('click', function () {
            fetchCsvExport();
        });

        $('#explore-slots').on('click', function () {
            goToHeatmapWithSlots(scheduler.selected);
        });
    }

    /**
     * Bind selectors that aren't on the page initially.
     */
    function bindDelegated() {
        $('.modal-body').on('keydown', function (e) {
            if (e.keyCode !== 27) {
                return;
            }

            var element = $(e.target);
            if (!element.hasClass('chosen-search-input')) {
                return;
            }

            element.blur().focus();

            e.stopImmediatePropagation();
        }).on('click touchstart mouseup mousedown', '.search-choice, .sp-replacer', function (e) {
            // Prevent the options drop-down menu when the color-picker is clicked.
            var select = $(this).parents('.chosen-container');
            select.removeClass('chosen-with-drop chosen-container-active');
            e.stopPropagation();
        });

        $(window).on('orientationchange', function () {
            $('.qtip').qtip('destroy');
        });
    }

    /**
     * Builds a URI to fetch a CSV based on the displayed events.
     */
    function fetchCsvExport() {
        location.href = GlobalUtils.getAPIUrl('download/export.json') + '?' + $.param(scheduler.getData());
    }
})(jQuery);