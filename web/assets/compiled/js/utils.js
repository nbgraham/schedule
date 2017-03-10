/**
 * The utility wrapper that works with FullCalendar.
 *
 * @author Austin Shinpaugh
 */

var Scheduler = (function ($) {
    "use strict";
    
    var Scheduler = function (calendar)
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
            var defaults = {
                resources : '/events.json',
                theme:       true
            };
            
            this.calendar.fullCalendar(Object.assign(defaults, options));
            
            return this;
        },
        
        /**
         * Request the classes based on the applied filters.
         */
        fetch : function () {
            
        }
        
    };
    
    return Scheduler;
}) (jQuery);
