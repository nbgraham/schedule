/**
 * Home page javascript file. Loads the calendar and requests data to
 * populate it with.
 *
 * @author Austin Shinpaugh
 */

"use strict";

(function ($) {
    var scheduler;
    
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
    });
    
}) (jQuery);