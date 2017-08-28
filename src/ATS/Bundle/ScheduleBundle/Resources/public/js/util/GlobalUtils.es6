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
        let base;
        
        if (window.location.hasOwnProperty('origin')) {
            base = window.location.origin;
            base = base + window.location.pathname;
        } else {
            // Legacy support.
            let location = window.location;
            base = location.protocol + '//' + location.hostname;
            base = base + location.pathname;
        }
        
        if ('/' !== base.slice(-1)) {
            base = base + '/';
        }
        
        return base + path;
    };

    /**
     * Toggle the Export button.
     * This doesn't belong here, maybe I'll move it later.
     * 
     * @param {Scheduler} scheduler
     */
    GlobalUtils.toggleExportBtn = function (scheduler)
    {
        let button = $('#btn-export');
        
        if (scheduler && scheduler.sections.length > 0) {
            button.removeAttr('disabled');
        } else {
            button.attr('disabled', 'disabled');
        }
    };

    /**
     * Clear a chosen field.
     * 
     * @param select
     */
    GlobalUtils.clearSelect = function (select)
    {
        select
            .find('option:selected')
                .removeAttr('selected')
            .end()
                .chosen('destroy')
        ;
        
        this.buildChosen(select);
    };

    /**
     * Hide the secondary filters.
     */
    GlobalUtils.hideSecondaryFilters = function ()
    {
        $('#number, #term-block')
            .chosen('destroy')
        ;
        
        $('label[for="number"], label[for="term-block"]')
            .addClass('hidden')
        ;
    };
    
    /**
     * Build the chosen dialogue boxes with the default app settings.
     * 
     * @param select
     */
    GlobalUtils.buildChosen = function (select)
    {
        // Chosen will initialize at 0px because it's in a modal.
        select.chosen({ 
            width: '100%',
            allow_single_deselect:  1/*,
            inherit_select_classes: true
            ,
            From a DevOps perspective, soft-limiting this just makes sense. From
            someone who wants to graduate and impress - what are you gonna do?
            max_selected_options:  3,*/
        });
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