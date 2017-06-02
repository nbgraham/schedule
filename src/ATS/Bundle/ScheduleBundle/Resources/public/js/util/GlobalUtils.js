/**
 * Global utility used in other scripts.
 * 
 * @author Austin Shinpaugh
 */

let GlobalUtils;
{
    let semesters   = [];
    let instructors = [];
    let subjects    = [];
    
    GlobalUtils = function GlobalUtils()
    {
        
    };

    /**
     * Get a URI that can be used in either dev or prod.
     * 
     * @param {string} path
     * 
     * @returns {string}
     */
    GlobalUtils.getAPIUrl = function (path)
    {
        return (GlobalUtils.isDev() ? '/app_dev.php' : '') + path;
    };

    /**
     * Toggle the Export button.
     * This doesn't belong here, maybe I'll move it later.
     * 
     * @param scheduler
     */
    GlobalUtils.toggleExportBtn = function (scheduler)
    {
        let button = $('#btn-export');
        
        if (scheduler.getSectionIds().length) {
            button.removeAttr('disabled');
        } else {
            button.attr('disabled', 'disabled');
        }
    };

    /**
     * Determine if we're in the dev environment.
     * 
     * @return {boolean}
     */
    GlobalUtils.isDev = function ()
    {
        return location.pathname.indexOf('app_dev.php') > -1;
    };

    /**
     * Return the available semesters
     * 
     * @returns {object}
     */
    GlobalUtils.getSemesters = function ()
    {
        return semesters;
    };

    /**
     * Sets the available semesters.
     * 
     * @param data
     */
    GlobalUtils.setSemesters = function (data)
    {
        semesters = data.terms;
    };

    /**
     * Sets the JSON feed of instructors.
     * 
     * @param data
     */
    GlobalUtils.setInstructors = function (data)
    {
        if (data.hasOwnProperty('instructors')) {
            instructors = data.instructors;
        } else {
            instructors = data;
        }
    };

    /**
     * Get the Instructors.
     * 
     * @return Object
     */
    GlobalUtils.getInstructors = function ()
    {
        return instructors;
    };
    
    /**
     * Sets the JSON feed of subjects.
     * 
     * @param data
     */
    GlobalUtils.setSubjects = function (data)
    {
        if (data.hasOwnProperty('subjects')) {
            subjects = data.subjects;
        } else {
            subjects = data;
        }
    };

    /**
     * Get the subjects.
     * 
     * @return Object
     */
    GlobalUtils.getSubjects = function ()
    {
        return subjects;
    };
    
    /**
     * Returns the subject from the URL.
     * @returns {string}
     */
    GlobalUtils.getUriSubject = function ()
    {
        let path, parts, subject;
        path  = window.location.pathname;
        parts = path.split('/');
        
        subject = parts[parts.length - 1];
        
        return -1 === subject.indexOf('.') ? subject : subject.substr(0, subject.indexOf('.'));
    };
}