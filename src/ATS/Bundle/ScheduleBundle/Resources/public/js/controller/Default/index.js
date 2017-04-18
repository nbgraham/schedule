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
     * Fill filter select fields with their associated data.
     */
    function populateFilters()
    {
        fillSelect('#subject', GlobalUtils.getSubjects());
        fillSelect('#instructor', GlobalUtils.getInstructors());
        fillSelect('#term', GlobalUtils.getSemesters());
        
        bindSemesterChange();
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
            
            let item, name;
            item = data[idx];
            name = item.hasOwnProperty('display_name')
                ? item.display_name
                : item.name
            ;
            
            $('<option>')
                .attr('value', item.id)
                .text(name)
                .appendTo(select)
            ;
        }
        
        // Chosen will initialize at 0px because it's in a modal.
        select.chosen({ 
            width: '100%',
            allow_single_deselect: 1
        });
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
    
    function bindModalActions()
    {
        let modal = $('#filtersModal');
        modal.find('#apply-filters').on('click', function () {
            scheduler.fetch();
        });
        
        modal.find('#clear-filters').on('click', function () {
            scheduler.clear();
        });
    }
    
}) (jQuery);