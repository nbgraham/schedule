/**
 * The utility wrapper that works with FullCalendar.
 *
 * @author Austin Shinpaugh
 */

const Scheduler = (function ($) {
    "use strict";
    
    let Scheduler = function (calendar)
    {
        if (!calendar) {
            throw new Error('Missing calendar parameter.');
        }
        
        this.calendar = $(calendar);
    };
    
    Scheduler.prototype = {
        /**
         * Initialize the calendar.
         * 
         * @var object options The options to override the default settings.
         * 
         * @return Scheduler
         */
        init : function (options)
        {
            let defaults = {
                defaultView: 'agendaWeek',
                weekends:    false,
                defaultDate: moment(),
                minTime:     "08:00:00",
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaWeek,agendaDay'
                },
                eventRender: function (event, element) {
                    if (!event.hasOwnProperty('description')) {
                        return;
                    }
                    
                    element.find('.fc-title')
                        .append("<br/>" + event.description)
                    ; 
                },
                dayClick: function(date, jsEvent, view) {
                    console.log(date);
                    console.log(jsEvent);
                    console.log(view);
                }
                
                /*events: [{
                    title:"My repeating event",
                    start: '10:00', // a start time (10am in this example)
                    end: '14:00', // an end time (6pm in this example)
                    
                    dow: [ 1, 4 ] // Repeat monday and thursday
                }],*/
            };
            
            this.calendar.fullCalendar(Object.assign(defaults, options));
            
            return this;
        },
        
        /**
         * Request the classes based on the applied filters.
         */
        fetch : function (uri) {
            let context = this;
            
            if (!uri) {
                uri = this.buildUri();
            }
            
            this.calendar.fullCalendar('addEventSource', {
                url:   GlobalUtils.getAPIUrl(uri),
                type:  'GET',
                data:  getData(),
                cache: true,
                
                eventDataTransform: function (data) {
                    console.log('data transform');
                    console.log(data);
                },
                complete : function (data) {
                    context.loadCourseClass(data.responseJSON);
                },
                error: function (xhr) {
                    console.log('error:');
                    console.log(xhr);
                }
            });
        },
        
        buildUri : function () {
            let uri, data;
            uri  = '/class';
            data = getData();
            
            uri = uri
                //+ (data.block   ? '/' + data.block   : '')
                //+ (data.subject ? '/' + data.subject : '')
                + '.json'
            ;
            
            console.log(uri);
            
            return uri;
        },
        
        getData : function () {
            return {
                'instructor': $('#instructor').val()
            };
        },
        
        loadCourseClass : function (data) {
            let events;
            
            if (data.hasOwnProperty('classes')) {
                events = loadEventAsClass(data.classes);
            } else {
                events = getClasses(data.courses);
            }
            
            this.calendar.fullCalendar('addEventSource', {
                events: events
            });
            
        },

        /**
         * Clear the calendar.
         */
        clear : function () {
            this.calendar.fullCalendar('removeEventSources');
        }
    };
    
    function loadEventAsClass(classes)
    {
        let events, i;
        events = [];
        
        for (i in classes) {
            let cls, course, days, subject;
            cls     = classes[i];
            course  = cls.course;
            days    = cls.days;
            subject = cls.subject;
            
            if (days && !days.length) {
                continue;
            }
            
            events.push({
                title: subject.name + ' ' + course.number,
                start: getTime(cls.start_time),
                end:   getTime(cls.end_time),
                dow:   getDays(cls.days),
                description: cls.instructor.name
                
            });
        }
        
        return events;
    }
    
    function getClasses (courses)
    {
        let events, i;
        
        events = [];
        
        for (i in courses) {
            let course, classes, x;
            course  = courses[i];
            classes = course.classes;
            
            for (x in classes) {
                let cls, days;
                cls  = classes[x];
                days = getDays(cls.days);
                
                if (!days.length) {
                    continue;
                }
                
                events.push({
                    title: course.subject + ' ' + course.number,
                    start: getTime(cls.start_time),
                    end:   getTime(cls.end_time),
                    dow:   getDays(cls.days)
                });
            }
        }
        
        return events;
    }
    
    function getDays (strDays)
    {
        if (!strDays.length) {
            return [];
        }
        
        let dow, days, parts, idx;
        dow   = ['Sun', 'M', 'T', 'W', 'R', 'F', 'Sat'];
        days  = [];
        parts = strDays.split('/');
        
        for (idx in parts) {
            let initial = parts[idx];
            days.push(dow.indexOf(initial));
        }
        
        return days;
    }
    
    function getTime (strTime)
    {
        let time = 4 === strTime.length ? strTime : '0' + strTime;
        
        return time.substr(0, 2) + ':' + time.substr(2);
    }
    
    function getData()
    {
        return {
            'term'      : $('#term').val(),
            'block'     : $('#term-block').val(),
            'subject'   : $('#subject').val(),
            'instructor': $('#instructor').val()
        };
    }
    
    function generateEventSourceKey()
    {
        let data = getData();
        
        return [data.block, data.subject, data.instructor].join('-');
    }
    
    function getEventSourceKey()
    {
        
    }
    
    return Scheduler;
}) (jQuery);
