/*!
 * The utility wrapper that works with FullCalendar.
 * @author Austin Shinpaugh
 */

const Scheduler = (function ($) {
    'use strict';
    
    let Scheduler = function (calendar)
    {
        if (!calendar) {
            throw new Error('Missing calendar parameter.');
        }
        
        this.calendar = $(calendar);
        this.shades   = {'subject': {}, 'instructor': {}, 'term-block': {}};
        this.sections = [];
        this.selected = [];
    };
    
    Scheduler.prototype = {
        /**
         * Set a background color for a grouping of events.
         * 
         * @param type
         * @param id
         * @param value
         */
        setColor: function (type, id, value) {
            this.shades[type][id] = value;
            
            updateColors(this, type, id, value);
        },
        
        /**
         * Initialize the calendar.
         * 
         * @var object options The options to override the default settings.
         */
        init : function (options)
        {
            let outer = this;

            let defaults = {
                startParam:   null,
                endParam:     null,
                lazyFetching: true,
                allDaySlot:   false,
                defaultView:  'agendaWeek',
                weekends:     false,
                defaultDate:  moment(),
                minTime:      '08:00:00',
                maxTime:      '22:00:00',
                header: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'agendaWeek,agendaDay'
                },
                viewRender: function () {
                    updateHeader(false);
                    hideDateColumnHeader();
                    
                    $('.qtip').qtip('destroy');
                },
                loading: function (isLoading) {
                    updateHeader(isLoading);
                },
                eventRender: function (event, element) {
                    if (event.hasOwnProperty('instructor')) {
                        element.find('.fc-title')
                            .append('<br/>' + event.instructor)
                        ;
                    }
                    
                    if (event.hasOwnProperty('location')) {
                        element.find('.fc-title')
                            .append('<br />' + event.location)
                        ;
                    }
                },
                eventAfterRender: function (event, element) {
                    $(element).qtip({
                        overwrite: true,
                        style: {
                            classes: 'qtip-rounded qtip-shadow qtip-bootstrap'
                        },
                        position: getTooltipPosition(event, element),
                        content: {
                            text: getToolTipText(event)
                        },
                        events: {
                            show: function (event, api) {
                                if (!GlobalUtils.isMobile()) {
                                    return;
                                }
                                
                                event.preventDefault();
                                
                                $('.mobile-tooltip').html(
                                    $(event.currentTarget)
                                        .find('.qtip-content')
                                        .html()
                                );
                            }
                        }
                    });
                },
                windowResize: function () {
                    // Update the tooltips position.
                    $('.qtip').qtip('reposition');
                },
                selectable: true,
                selectHelper: true,
                select: function(start, end) {
                    var title = 'Potential Time Slot';
                    var eventData = {
                        title: title,
                        start: start,
                        end: end
                    };        

                    addHalfHourSections(eventData, outer.selected);
                    console.log(outer.selected);
                    
                    $('#calendar').fullCalendar('renderEvent', eventData, true); // stick? = true
                    
                    $('#calendar').fullCalendar('unselect');
                },
            };
            
            this.calendar.fullCalendar($.extend(defaults, options));
            
            return this;
        },
        
        /**
         * Request the sections based on the applied filters.
         */
        fetch : function (uri) {
            if (!requiredFields()) {
                return false;
            }
            
            let context = this;
            
            if (!uri) {
                uri = this.buildUri();
            }
            
            this.wipe();
            this.calendar.fullCalendar('addEventSource', {
                url:   GlobalUtils.getAPIUrl(uri),
                type:  'GET',
                data:  context.getData(),
                cache: canCache(),
                
                success: function (data) {
                    context.loadCourseClass(data);
                    
                    if (!data.sections || !data.sections.length) {
                        GlobalUtils.showMessage('No results matched your filter criteria.');
                    }
                    
                    GlobalUtils.toggleExportBtn(context);
                },
                error: function (xhr) {
                    if (409 !== xhr.status) {
                        console.log(xhr);
                        GlobalUtils.toggleExportBtn(context);
                        GlobalUtils.showMessage('An error occurred while fetching your request. Please try again.', 'Error');
                        return;
                    }
                    
                    // An update has occurred and the page data needs to be refreshed.
                    GlobalUtils.showMessage('The system recently updated, and you must reload the page in order to receive accurate results.<br /><br /><p>Automatically reloading the page in 10 seconds...</p>');
                    
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
        buildUri : function () {
            return 'section.json';
        },

        /**
         * Gets the raw data from the input controls and filters out
         * any unnecessary data so that the query string isn't filled
         * with empty data.
         * 
         * @returns {{term: *, block: *, subject: *, instructor: *}|*}
         */
        getData : function () {
            let data, idx;
            data = getData();
            
            for (idx in data) {
                if (!data.hasOwnProperty(idx)) {
                    continue;
                }
                
                let value = data[idx];
                
                if (!value || (value instanceof Array && !value.length)) {
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
        loadCourseClass : function (data) {
            this.sections = data.sections;
            let events    = createEventsFromSections(this, data.sections);
            
            this.calendar.fullCalendar('addEventSource', {
                'events': filterEvents(this.calendar, events)
            });
            
            return this;
        },

        /**
         * Clear the applied filters.
         */
        clearFilters : function () {
            let selectors, options;
            selectors = $('.chosen-select');
            options   = selectors.find('option:selected');
            
            options.removeAttr('selected');
            
            // Hide related fields (term-blocks, course numbers).
            selectors
                .val('')
                .trigger('chosen:updated')
            ;
            
            toggleOrangeBorder(false);
            
            if (this.sections.length) {
                updateHeader(false);
            }
            
            GlobalUtils.hideSecondaryFilters();
            GlobalUtils.toggleExportBtn();
            
            return this;
        },

        /**
         * Clear all calendar data.
         */
        clear : function () {
            this
                .clearFilters()
                .wipe()
            ;
            
            return this;
        },

        /**
         * Wipe the calendar data.
         */
        wipe : function () {
            this.sections = [];
            this.calendar.fullCalendar('removeEventSources');
            
            // Remove any existing qTip items.
            $('.qtip').remove();
            
            return this;
        }
    };

    /**
     * Determine if there is space available to render the the Qtip without
     * having to squish text.
     * 
     * @param event
     * @param element
     * 
     * @private
     * @return {int}
     */
    function _ttSpaceIndex(event, element)
    {
        let left, width, body_width, room_right, is_early;
        left       = $(element).offset().left;
        width      = 280; // QTip CSS library sets a tooltip max-width to 280px.
        body_width = parseInt($('body').css('width'));
        room_right = 1 > ((left + width) / body_width);
        is_early   = event.start.hour() < 12;
        
        if (room_right && !is_early) {
            // Tooltip has room in the default position.
            return 0;
        } else if (!room_right) {
            return 2;
        }
        
        return 1;
    }
    
    /**
     * Get the tooltip position.
     * 
     * @param event
     * @param element
     * 
     * @return {{my: string, at: string, target: *}}
     */
    function getTooltipPosition(event, element)
    {
        let index, position;
        
        index    = _ttSpaceIndex(event, element);
        position = {
            my:     'bottom left',
            at:     'top left',
            target: element,
            adjust: {resize: false}
        };
        
        if (2 === index) {
            position = {
                my: 'right center',
                at: 'left center'
            };
        } else if (1 === index) {
            position = {
                my: 'top left',
                at: 'left bottom'
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
    function getDayOfWeek(element)
    {
        let parent = $(element).parents('td').first();
        
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
    function updateColors (instance, type, id, color)
    {
        let events, idx, event;
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
    function getColor(scheduler, section, defaultColor)
    {
        let collection;
        
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
    function getCapacityCSS(event)
    {
        let num_seats, seats_percent;
        
        num_seats     = event.maximum_enrollment - event.num_enrolled;
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
    function getToolTipText(event)
    {
        if (event.title.includes("Potential")) { 
            return "Potential"; 
        } 
        
        let section, course, output;
        
        section = event.section;
        course  = event.course;
        output  = $('<p>');
        
        // Title.
        output.append(
            $('<div>')
                .addClass('row ttTitle')
                .append(
                    $('<div>').addClass('col-xs-9')
                        .text(section.subject.name + ' ' + course.number + ': ' + section.number)
                ).append(
                    $('<div>').addClass('col-xs-3 nowrap')
                        .text(section.num_enrolled + ' / ' + section.maximum_enrollment)
                ).append(
                    $('<div>').addClass('col-xs-12')
                        .text(course.name)
                )
        );
        
        // Body.
        output.append(
            $('<p>').append('<hr />').append(
                $('<div>').addClass('row')
                    .append(
                        $('<div>').addClass('ttLabel nowrap col-xs-4').text('Location:')
                    ).append(
                        $('<div>').addClass('col-xs-8').text(section.campus.display_name)
                    ).append(
                        $('<div>').addClass('ttDetail col-xs-offset-4 col-xs-8').text(
                            section.building.name + ' - ' + section.room.number
                        )
                    ).append(
                        $('<div>').addClass('ttLabel nowrap col-xs-4').text('Instructor:')
                    ).append(
                        $('<div>').addClass('col-xs-8').text(section.instructor.name)
                    ).append(
                        $('<div>').addClass('ttLabel nowrap col-xs-4').text('Days:')
                    ).append(
                        $('<div>').addClass('col-xs-8').text(section.days)
                    )
            )
        );
        
        return output;
    }

    /**
     * Hides the month / day in the column week headers.
     */
    function hideDateColumnHeader ()
    {
        $('.fc-day-header span').each(function () {
            let text, parts;
            text  = $(this).text();
            parts = text.split(' ');
            
            $(this).text(parts[0]);
        });
    }

    /**
     * Update the header based on the semester.
     *
     * @param {boolean} is_loading
     */
    function updateHeader(is_loading)
    {
        let header, title;
        
        header = $('#calendar').find('.fc-header-toolbar h2');
        title  = $('#term').find('option:selected').text();
        
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
    function filterEvents(calendar, events)
    {
        let output, idx;
        
        if (calendar.fullCalendar('clientEvents').length) {
            return [];
        }
        
        output = [];
        for (idx in events) {
            let event = events[idx];
            
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
    function createEventsFromSections(scheduler, sections)
    {
        let events, color, i;
        events = [];
        color  = '#001505';
        
        for (i in sections) {
            if (!sections.hasOwnProperty(i)) {
                continue;
            }
            
            let cls, course, days, subject;
            cls     = sections[i];
            course  = cls.course;
            days    = cls.days;
            subject = cls.subject;
            
            if (days && !days.length) {
                continue;
            }
            
            events.push({
                section: cls,
                course:  course,
                
                id:    cls.id,
                crn:   cls.crn,
                title: subject.name + ' ' + course.number + ': ' + cls.number,
                start: getTime(cls.start_time),
                end:   getTime(cls.end_time),
                dow:   getDays(cls.days),
                instructor:      cls.instructor.name,
                location:        cls.building.name + ' - ' + cls.room.number,
                className:       getCapacityCSS(cls),
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
    function requiredFields()
    {
        let term, block, multiples, idx;
        term      = $('#term');
        block     = $('#term-block');
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
            
            let selector = multiples[idx];
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
    function toggleOrangeBorder(on)
    {
        let multiples, color, idx;
        multiples = ['#subject', '#instructor'];
        color     = on ? 'orange' : '';
        
        for (idx in multiples) {
            if (!multiples.hasOwnProperty(idx)) {
                continue;
            }
            
            let selector = multiples[idx];
            $(selector + '_chosen')
                .find('ul')
                .css('border-color', color)
            ;
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
    function getDays (strDays)
    {
        if (!strDays.length) {
            return [];
        }
        
        let dow, days, parts, idx;
        dow   = ['U', 'M', 'T', 'W', 'R', 'F', 'S'];
        days  = [];
        
        if (-1 === strDays.indexOf('/')) {
            parts = strDays;
        } else {
            parts = strDays.split('/');
        }
        
        for (idx in parts) {
            if (!parts.hasOwnProperty(idx)) {
                continue;
            }
            
            let initial = parts[idx];
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
    function getTime (strTime)
    {
        let time = 4 === strTime.length ? strTime : '0' + strTime;
        
        return time.substr(0, 2) + ':' + time.substr(2);
    }

    /**
     * Get the data used in the query to the API Endpoint.
     * 
     * @returns {{term: *, block: *, subject: *, instructor: *}}
     */
    function getData()
    {
        return {
            't' : $('#term').val(),
            'b' : filterMultiSelects($('#term-block')),
            's' : filterMultiSelects($('#subject')),
            'n' : filterMultiSelects($('#number')),
            'i' : filterMultiSelects($('#instructor')),
            'u' : GlobalUtils.getLastUpdate().start
        };
    }

    /**
     * Filters out useless values returned from Chosen/jQuery's val() method.
     * 
     * @param {jQuery} select
     * @returns {Array}
     */
    function filterMultiSelects(select)
    {
        let output, values, idx;
        output = [];
        values = select.val();
        
        for (idx in values) {
            if (!values.hasOwnProperty(idx)) {
                continue;
            }
            
            let value = values[idx];
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
    function canCache()
    {
        // Don't cache any results if the server is under maintenance.
        if (0 === GlobalUtils.getLastUpdate().status) {
            return false;
        }
        
        // If it's production cache the results.
        if (!GlobalUtils.isDev()) {
            return true;
        }
        
        let param = getQuery('no_cache');
        
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
    function getQuery(q)
    {
        let param = (window.location.search.match(new RegExp('[?&]' + q + '=([^&]+)')) || [, null])[1];
        if (param && '/' === param.substr(-1)) {
            param = param.substr(0, param.length - 1);
        }
        
        return param;
    }
    
    return Scheduler;
}) (jQuery);
