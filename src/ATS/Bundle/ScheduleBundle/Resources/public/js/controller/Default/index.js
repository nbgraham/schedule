/**
 * Home page javascript file. Loads the calendar and requests data to
 * populate it with.
 *
 * @author Austin Shinpaugh
 */

(function ($) {
    "use strict";
    
    let scheduler;
    
    if (!$.fullCalendar) {
        console.log('FullCalendar is not loaded.');
        return;
    }
    
    $(document).ready(function () {
        if (!Scheduler instanceof Object) {
            console.log('Scheduler util is not loaded.');
            return;
        }
        
        scheduler = new Scheduler('#calendar');
        scheduler.init();
        
        populateFilters();
        bindModalActions();
    });

    /**
     * Fill the filter boxes with their respective data.
     */
    function populateFilters()
    {
        fillSelect('#subject', GlobalUtils.getSubjects());
        fillSelect('#instructor', GlobalUtils.getInstructors());
        fillSelect('#term', GlobalUtils.getSemesters());
        
        bindSemesterChange();
        bindSubjectChange();
        
        // TODO: Fix this. Hitting escape when an input is selected will close the modal... which is super annoying.
        $('.chosen-search-input').on('keydown', function (e) {
            if (e.keyCode !== 27) {
                return;
            }
            
            $(this)
                .blur()
                .focus()
            ;
            
            e.stopImmediatePropagation();
        });
    }

    /**
     * Fill a select field and set it up with Chosen.
     * 
     * @param {string} id
     * @param {object} data
     */
    function fillSelect(id, data)
    {
        let select, idx;
        select = $(id);
        
        for (idx in data) {
            if (!data.hasOwnProperty(idx)) {
                return;
            }
            
            let item = data[idx];
            
            $('<option>')
                .attr('value', item.id)
                .text(determineChosenLabel(item))
                .appendTo(select)
            ;
        }
        
        // Chosen will initialize at 0px because it's in a modal.
        select.chosen({ 
            width: '100%',
            allow_single_deselect: 1/*,
            From a DevOps perspective, soft-limiting this just makes sense. From
            someone who wants to graduate and impress - what are you gonna do?
            max_selected_options:  3,*/
        });
    }

    /**
     * Determines an appropriate option display text based on the information
     * provided from the entity that the user is selecting from.
     * 
     * @param {object} entity
     * 
     * @returns string
     */
    function determineChosenLabel(entity)
    {
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
    function bindSemesterChange()
    {
        $('#term').on('change', function (event, params) {
            // params is undefined when you deselect a semester.
            if (!params) {
                $('#term-block').chosen('destroy');
                return;
            }
            
            let semesters, semester, select, idx;
            semesters = GlobalUtils.getSemesters();
            for (idx in semesters) {
                if (!semesters.hasOwnProperty(idx)) {
                    continue;
                }
                
                semester = semesters[idx];
                if (semester.id != params.selected) {
                    continue;
                }
                
                select = $('#term-block');
                // If there are other options in the term-block selector, remove them.
                select.find('option[value]').remove();
                select.show();
                
                // Fill the term-block selector.
                fillSelect('#term-block', semester.blocks);
                
                // Notify Chosen that the content of the select box changed.
                select.trigger("chosen:updated");
            }
            
        });
    }
    
    /**
     * Whenever a change in subject selection happens, update the
     * course number selector.
     */
    function bindSubjectChange()
    {
        $('#subject').on('change', function (event, params) {
            // params is undefined when you deselect a subject.
            if (!params) {
                $('#number').chosen('destroy');
                return;
            }
            
            let subjects, select, subject, idx;
            subjects = GlobalUtils.getSubjects();
            select   = $('#number');
            
            if (params.hasOwnProperty('deselected')) {
                select
                    .find('option[data-subject="' + params.deselected + '"]')
                    .remove()
                ;
                
                if (!$(this).val().length) {
                    select.chosen('destroy');
                } else {
                    select.trigger('chosen:updated');
                }
                
                return;
            }
            
            for (idx in subjects) {
                if (!subjects.hasOwnProperty(idx)) {
                    continue;
                }
                
                subject = subjects[idx];
                if (subject.id != params.selected) {
                    continue;
                }
                
                fillSelect('#number', subject.courses);
                
                select.find('option:not([data-subject])')
                    .attr('data-subject', subject.id)
                ;
                
                select.trigger('chosen:updated');
            }
        });
    }
    
    function bindModalActions()
    {
        let modal = $('#filtersModal');
        modal.find('#apply-filters').on('click', function () {
            if (scheduler.fetch()) {
                modal.modal('hide');
            }
        });
        
        modal.find('#clear-filters').on('click', function () {
            scheduler.clearFilters();
        });
        
        $('#clear-calendar').click(function () {
            scheduler.clear();
        });
    }
    
}) (jQuery);