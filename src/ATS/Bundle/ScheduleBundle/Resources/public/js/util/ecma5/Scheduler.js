'use strict';

/*!
 * The utility wrapper that works with FullCalendar.
 * @author Austin Shinpaugh
 */

var Scheduler = function ($) {
    'use strict';

    var Scheduler = function Scheduler(calendar) {
        if (!calendar) {
            throw new Error('Missing calendar parameter.');
        }

        this.calendar = $(calendar);
        this.shades = { 'subject': {}, 'instructor': {}, 'term-block': {} };
        this.sections = [];
    };

    Scheduler.prototype = {
        /**
         * Set a background color for a grouping of events.
         * 
         * @param type
         * @param id
         * @param value
         */
        setColor: function setColor(type, id, value) {
            this.shades[type][id] = value;

            updateColors(this, type, id, value);
        },

        /**
         * Initialize the calendar.
         * 
         * @var object options The options to override the default settings.
         */
        init: function init(options) {
            var context = void 0,
                defaults = void 0;

            context = this;
            defaults = {
                startParam: null,
                endParam: null,
                lazyFetching: true,
                allDaySlot: false,
                defaultView: 'agendaWeek',
                weekends: false,
                defaultDate: moment(),
                minTime: '08:00:00',
                maxTime: '22:00:00',
                header: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'agendaWeek,agendaDay'
                },
                viewRender: function viewRender() {
                    updateHeader(false);
                    hideDateColumnHeader();

                    $('.qtip', context.calendar).qtip('destroy');
                },
                loading: function loading(isLoading) {
                    updateHeader(isLoading);
                },
                eventRender: function eventRender(event, element) {
                    if (event.hasOwnProperty('instructor')) {
                        element.find('.fc-title').append('<br/>' + event.instructor);
                    }

                    if (event.hasOwnProperty('location')) {
                        element.find('.fc-title').append('<br />' + event.location);
                    }
                },
                eventAfterRender: function eventAfterRender(event, element) {
                    var position = getTooltipPosition(element);

                    $(element).qtip({
                        style: {
                            classes: 'qtip-rounded qtip-shadow qtip-bootstrap'
                        },
                        position: position,
                        content: {
                            text: getToolTipText(event)
                        },
                        events: {
                            show: function show(event, api) {
                                if (!GlobalUtils.isMobile()) {
                                    return;
                                }

                                event.preventDefault();

                                $('.mobile-tooltip').html($(event.currentTarget).find('.qtip-content').html());
                            }
                        }
                    });
                },
                windowResize: function windowResize() {
                    // Update the tooltips position.
                    $('.qtip', context.calendar).qtip('reposition');
                }
            };

            this.calendar.fullCalendar($.extend(defaults, options));

            return this;
        },

        /**
         * Request the sections based on the applied filters.
         */
        fetch: function fetch(uri) {
            if (!requiredFields()) {
                return false;
            }

            var context = this;

            if (!uri) {
                uri = this.buildUri();
            }

            this.wipe();
            this.calendar.fullCalendar('addEventSource', {
                url: GlobalUtils.getAPIUrl(uri),
                type: 'GET',
                data: context.getData(),
                cache: canCache(),

                success: function success(data) {
                    context.loadCourseClass(data);

                    if (!data.sections || !data.sections.length) {
                        GlobalUtils.showMessage('No results matched your filter criteria.');
                    }

                    GlobalUtils.toggleExportBtn(context);
                },
                error: function error(xhr) {
                    if (409 !== xhr.status) {
                        console.log(xhr);
                        GlobalUtils.toggleExportBtn(context);
                        GlobalUtils.showMessage('An error occurred while fetching your request. Please try again.', 'Error');
                        return;
                    }

                    // An update has occurred and the page data needs to be refreshed.
                    GlobalUtils.showMessage('The system recently updated, and you must reload the page in order to receive accurate results.<br /><p>Automatically reloading the page in 10 seconds..</p>');

                    setTimeout(function () {
                        window.location.reload();
                    }, 10000);
                }
            });

            return true;
        },

        /**
         * Goofy little wrapper around URI building; future-proofing and whatnot.
         * @returns {string}
         */
        buildUri: function buildUri() {
            return 'section.json';
        },

        /**
         * Gets the raw data from the input controls and filters out
         * any unnecessary data so that the query string isn't filled
         * with empty data.
         * 
         * @returns {{term: *, block: *, subject: *, instructor: *}|*}
         */
        getData: function getData() {
            var data = void 0,
                idx = void 0;
            data = _getData();

            for (idx in data) {
                if (!data.hasOwnProperty(idx)) {
                    continue;
                }

                var value = data[idx];

                if (!value || value instanceof Array && !value.length) {
                    delete data[idx];
                }
            }

            return data;
        },

        /**
         * Handles the data returned from a graceful API response.
         * 
         * @param data
         */
        loadCourseClass: function loadCourseClass(data) {
            this.sections = data.sections;
            var events = createEventsFromSections(this, data.sections);

            this.calendar.fullCalendar('addEventSource', {
                'events': filterEvents(this.calendar, events)
            });

            return this;
        },

        /**
         * Clear the applied filters.
         */
        clearFilters: function clearFilters() {
            var selectors = void 0,
                options = void 0;
            selectors = $('.chosen-select');
            options = selectors.find('option[value]:selected');

            // $.removeAttr is broken for the selected property.
            options.prop('selected', false);

            // Hide related fields (term-blocks, course numbers).
            selectors.val('');
            selectors.trigger('change');
            selectors.trigger('chosen:updated');

            if (!this.calendar.fullCalendar('clientEvents').length) {
                updateHeader(false);
            }

            return this;
        },

        /**
         * Clear all calendar data.
         */
        clear: function clear() {
            this.wipe().clearFilters();

            return this;
        },

        /**
         * Wipe the calendar data.
         */
        wipe: function wipe() {
            this.calendar.fullCalendar('removeEventSources');

            return this;
        },

        /**
         * Get the section IDs from all of the displayed events.
         * 
         * @returns {Array}
         */
        getSectionIds: function getSectionIds() {
            var context = void 0,
                ids = void 0,
                idx = void 0,
                events = void 0,
                event = void 0;

            context = this;
            events = context.calendar.fullCalendar('clientEvents');
            ids = [];

            for (idx in events) {
                if (!events.hasOwnProperty(idx)) {
                    continue;
                }

                event = events[idx];
                if (!$.inArray(event.id, ids)) {
                    ids.push(event.id);
                }
            }

            return ids;
        }
    };

    /**
     * Determine if there is space available to render the the Qtip without
     * having to squish text.
     * 
     * @param element
     * @return {int}
     */
    function ttSpaceIndex(element) {
        var left = void 0,
            width = void 0,
            body_width = void 0;
        left = $(element).offset().left;
        width = 280; // QTip CSS library sets a tooltip max-width to 280px.
        body_width = parseInt($('body').css('width'));

        if (1 > (left + width) / body_width) {
            // Tooltip has room in the default position.
            return 0;
        }

        return 1;
    }

    /**
     * Get the tooltip position.
     * 
     * @param element
     * @return {{my: string, at: string, target: *}}
     */
    function getTooltipPosition(element) {
        var position = {
            my: 'bottom left',
            at: 'top left',
            target: element,
            adjust: { resize: false }
        };

        if (1 === ttSpaceIndex(element)) {
            position = {
                my: 'right center',
                at: 'left center'
            };
        }

        return position;
    }

    /**
     * Determine if the element rendered is in the Friday column.
     * 
     * @param element
     * 
     * @return boolean
     */
    function getDayOfWeek(element) {
        var parent = $(element).parents('td').first();

        return parent.parent().children().index(parent);
    }

    /**
     * Update the background colors for an event after it's been rendered.
     * 
     * @param instance
     * @param type
     * @param id
     * @param color
     */
    function updateColors(instance, type, id, color) {
        var events = void 0,
            idx = void 0,
            event = void 0;
        events = instance.calendar.fullCalendar('clientEvents');

        if (!events.length) {
            return;
        }

        for (idx in events) {
            if (!events.hasOwnProperty(idx)) {
                continue;
            }

            event = events[idx];

            if ('term-block' === type && id === event.section.block.display_name) {
                event.backgroundColor = color;
            }

            if ('subject' === type && id === event.section.subject.name) {
                event.backgroundColor = color;
            }

            if ('instructor' === type && id === event.section.instructor.name) {
                event.backgroundColor = color;
            }
        }

        instance.calendar.fullCalendar('rerenderEvents');
    }

    /**
     * Get the color value for an event.
     * 
     * @param scheduler
     * @param section
     * @param defaultColor
     * @returns {*}
     */
    function getColor(scheduler, section, defaultColor) {
        var collection = void 0;

        collection = scheduler.shades['instructor'];
        if (collection.hasOwnProperty(section.instructor.name)) {
            return collection[section.instructor.name];
        }

        collection = scheduler.shades['subject'];
        if (collection.hasOwnProperty(section.subject.name)) {
            return collection[section.subject.name];
        }

        collection = scheduler.shades['term-block'];
        if (collection.hasOwnProperty(section.block.display_name)) {
            return collection[section.block.display_name];
        }

        return defaultColor;
    }

    /**
     * Get an markup class for the class based on it's current capacity.
     * 
     * @param event
     * @returns {*}
     */
    function getCapacityCSS(event) {
        var num_seats = void 0,
            seats_percent = void 0;

        num_seats = event.maximum_enrollment - event.num_enrolled;
        seats_percent = num_seats / event.maximum_enrollment;

        switch (true) {
            case seats_percent < 0.00:
                return 'capacity-over-capacity';
            case seats_percent < 0.10:
                return 'capacity-alert';
            case seats_percent < 0.25:
                return 'capacity-warning';
            default:
                return 'capacity-default';
        }
    }

    /**
     * Generate the text to display in the Tool Tip.
     * 
     * @param event
     * 
     * @returns {string|*}
     */
    function getToolTipText(event) {
        var section = void 0,
            course = void 0,
            output = void 0;

        section = event.section;
        course = event.course;
        output = $('<p>');

        // Title.
        output.append($('<div>').addClass('row ttTitle').append($('<div>').addClass('col-xs-9').text(section.subject.name + ' ' + course.number + ': ' + section.number)).append($('<div>').addClass('col-xs-3 nowrap').text(section.num_enrolled + ' / ' + section.maximum_enrollment)).append($('<div>').addClass('col-xs-12').text(course.name)));

        // Body.
        output.append($('<p>').append('<hr />').append($('<div>').addClass('row').append($('<div>').addClass('ttLabel nowrap col-xs-4').text('Location:')).append($('<div>').addClass('col-xs-8').text(section.campus.display_name)).append($('<div>').addClass('ttDetail col-xs-offset-4 col-xs-8').text(section.building.name + ' - ' + section.room.number)).append($('<div>').addClass('ttLabel nowrap col-xs-4').text('Instructor:')).append($('<div>').addClass('col-xs-8').text(section.instructor.name)).append($('<div>').addClass('ttLabel nowrap col-xs-4').text('Days:')).append($('<div>').addClass('col-xs-8').text(section.days))));

        return output;
    }

    /**
     * Hides the month / day in the column week headers.
     */
    function hideDateColumnHeader() {
        $('.fc-day-header span').each(function () {
            var text = void 0,
                parts = void 0;
            text = $(this).text();
            parts = text.split(' ');

            $(this).text(parts[0]);
        });
    }

    /**
     * Update the header based on the semester.
     *
     * @param {boolean} is_loading
     */
    function updateHeader(is_loading) {
        var header = void 0,
            title = void 0;

        header = $('#calendar').find('.fc-header-toolbar h2');
        title = $('#term').find('option:selected').text();

        if (is_loading) {
            title = $('<div>').addClass('loadersmall');
        }

        header.html(title);
    }

    /**
     * Make sure that there are no duplicate events rendered in the calendar.
     * 
     * @param {fullCalendar} calendar
     * @param {Event[]}       events
     * @returns {Array}
     */
    function filterEvents(calendar, events) {
        var output = void 0,
            idx = void 0;

        if (calendar.fullCalendar('clientEvents').length) {
            return [];
        }

        output = [];
        for (idx in events) {
            var event = events[idx];

            if (calendar.fullCalendar('clientEvents', event.id).length) {
                continue;
            }

            output.push(event);
        }

        return output;
    }

    /**
     * Parse the JSON API data and turn them into event objects.
     * 
     * @param scheduler
     * @param sections
     * @returns {Array}
     */
    function createEventsFromSections(scheduler, sections) {
        var events = void 0,
            color = void 0,
            i = void 0;
        events = [];
        color = '#001505';

        for (i in sections) {
            if (!sections.hasOwnProperty(i)) {
                continue;
            }

            var cls = void 0,
                course = void 0,
                days = void 0,
                subject = void 0;
            cls = sections[i];
            course = cls.course;
            days = cls.days;
            subject = cls.subject;

            if (days && !days.length) {
                continue;
            }

            events.push({
                section: cls,
                course: course,

                id: cls.id,
                crn: cls.crn,
                title: subject.name + ' ' + course.number + ': ' + cls.number,
                start: getTime(cls.start_time),
                end: getTime(cls.end_time),
                dow: getDays(cls.days),
                instructor: cls.instructor.name,
                location: cls.building.name + ' - ' + cls.room.number,
                className: getCapacityCSS(cls),
                backgroundColor: getColor(scheduler, cls, color)
            });
        }

        return events;
    }

    /**
     * Ensures the required fields have appropriate values before submitting
     * an API request.
     * 
     * @returns {boolean}
     */
    function requiredFields() {
        var term = void 0,
            block = void 0,
            multiples = void 0,
            idx = void 0;
        term = $('#term');
        block = $('#term-block');
        multiples = ['#subject', '#instructor'];

        toggleOrangeBorder(false);

        if (!term.val() || !term.val().length) {
            term.trigger('chosen:activate');
            return false;
        }

        if (!block.val() || !block.val().length) {
            block.trigger('chosen:open');
            return false;
        }

        for (idx in multiples) {
            if (!multiples.hasOwnProperty(idx)) {
                continue;
            }

            var selector = multiples[idx];
            if ($(selector + ' option:selected').length) {
                return true;
            }
        }

        toggleOrangeBorder(true);

        return false;
    }

    /**
     * Highlights the required fields, either subject(s) or instructor(s).
     * 
     * @param {Boolean} on
     */
    function toggleOrangeBorder(on) {
        var multiples = void 0,
            color = void 0,
            idx = void 0;
        multiples = ['#subject', '#instructor'];
        color = on ? 'orange' : '';

        for (idx in multiples) {
            if (!multiples.hasOwnProperty(idx)) {
                continue;
            }

            var selector = multiples[idx];
            $(selector + '_chosen').find('ul').css('border-color', color);
        }

        if (on) {
            $('#required-fields-help').removeClass('hidden');
        } else {
            $('#required-fields-help').addClass('hidden');
        }
    }

    /**
     * Turn the stored dates into their numerical representation.
     * 
     * @param {string} strDays
     * @returns {Array}
     */
    function getDays(strDays) {
        if (!strDays.length) {
            return [];
        }

        var dow = void 0,
            days = void 0,
            parts = void 0,
            idx = void 0;
        dow = ['U', 'M', 'T', 'W', 'R', 'F', 'S'];
        days = [];

        if (-1 === strDays.indexOf('/')) {
            parts = strDays;
        } else {
            parts = strDays.split('/');
        }

        for (idx in parts) {
            if (!parts.hasOwnProperty(idx)) {
                continue;
            }

            var initial = parts[idx];
            days.push(dow.indexOf(initial));
        }

        return days;
    }

    /**
     * Format the time.
     * 
     * @param {string} strTime
     * @returns {string}
     */
    function getTime(strTime) {
        var time = 4 === strTime.length ? strTime : '0' + strTime;

        return time.substr(0, 2) + ':' + time.substr(2);
    }

    /**
     * Get the data used in the query to the API Endpoint.
     * 
     * @returns {{term: *, block: *, subject: *, instructor: *}}
     */
    function _getData() {
        return {
            'term': $('#term').val(),
            'block': filterMultiSelects($('#term-block')),
            'subject': filterMultiSelects($('#subject')),
            'number': filterMultiSelects($('#number')),
            'instructor': filterMultiSelects($('#instructor')),
            'last_update': GlobalUtils.getLastUpdate().start
        };
    }

    /**
     * Filters out useless values returned from Chosen/jQuery's val() method.
     * 
     * @param {jQuery} select
     * @returns {Array}
     */
    function filterMultiSelects(select) {
        var output = void 0,
            values = void 0,
            idx = void 0;
        output = [];
        values = select.val();

        for (idx in values) {
            if (!values.hasOwnProperty(idx)) {
                continue;
            }

            var value = values[idx];
            if (!value.length) {
                continue;
            }

            output.push(value);
        }

        return output;
    }

    /**
     * Determine if the client should cache the Section API responses.
     * 
     * @return {boolean}
     */
    function canCache() {
        // Don't cache any results if the server is under maintenance.
        if (0 === GlobalUtils.getLastUpdate().status) {
            return false;
        }

        // If it's production cache the results.
        if (!GlobalUtils.isDev()) {
            return true;
        }

        var param = getQuery('no_cache');

        // If no_cache is present don't cache section API calls.
        return null === param || 1 !== param;
    }

    /**
     * Get a query param.
     * 
     * @param q
     * 
     * @return {*}
     * @url https://stackoverflow.com/questions/5448545/how-to-retrieve-get-parameters-from-javascript
     */
    function getQuery(q) {
        var param = (window.location.search.match(new RegExp('[?&]' + q + '=([^&]+)')) || [, null])[1];
        if (param && '/' === param.substr(-1)) {
            param = param.substr(0, param.length - 1);
        }

        return param;
    }

    return Scheduler;
}(jQuery);