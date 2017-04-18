/**
 * Wrapper for the filters.
 *
 * @author Austin Shinpaugh
 */

let Filters;
{
    Filters = function Filters ()
    {
        
    };
    
    Filters.clearAllChosen = function ()
    {
        let selects = $('#filtersModal select');
        
        selects.find('option:selected')
            .removeAttr('selected')
        ;
        
        selects.trigger('chosen:updated');
    };
    
    Filters.clearChosen = function (chosen)
    {
        $(chosen)
            .find('option:selected')
            .removeAttr('selected')
        ;
        
        $(chosen).trigger('chosen:updated');
    };
}