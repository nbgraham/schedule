/*!
 * Global utility used in other scripts.
 * @author Austin Shinpaugh
 */

let GlobalUtils;
{
    let semesters   = [];
    let instructors = [];
    let subjects    = [];
    let last_update = null;
    
    GlobalUtils = function GlobalUtils()
    {
        
    };

    /**
     * Show the message modal.
     * 
     * @param message
     * @param title
     */
    GlobalUtils.showMessage = function (message, title) 
    {
        let modal = $('#messageModal');
        
        modal.find('.modal-body').html(message);
        modal.find('.modal-title').text(title ? title : 'Attention');
        
        modal.modal('show')
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
        let base = window.location.toString();
        if ('/' !== base.slice(-1)) {
            base = base + '/';
        }
        
        return base + path;
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
     * Set the last updated variable.
     * 
     * @param update
     */
    GlobalUtils.setLastUpdate = function (update)
    {
        last_update = update;
    };

    /**
     * Get the last updated variable.
     * 
     * @return {object}
     */
    GlobalUtils.getLastUpdate = function ()
    {
        return last_update;
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
        
        let idx;
        for (idx in subjects) {
            if (!subjects.hasOwnProperty(idx)) {
                continue;
            }
            
            subjects[idx].courses.sort(function (a, b) {
                if (a.number === b.number) {
                    return 0;
                }
                
                if (a.number > b.number) {
                    return 1;
                }
                
                return -1;
            });
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
     * Determine if the current browsing experience is in a mobile device.
     * 
     * @return boolean
     */
    GlobalUtils.isMobile = function ()
    {
        return $('.mobile-tooltip').is(':not(:hidden)');
    };
}