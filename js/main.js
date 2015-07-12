
/** 
 * Utility to reset expansions of $ref resources of a defined resource.
 */
function resetExpansions($resource) {

    // Do some cleanup on the definition clone. If anything has been expanded,
    // just delete the expansions. Basically, this is clearing all children 
    // of all .ref-resource elements.
    $resource.find('.ref-resource').html('');

    // Any previously hidden .ref-expander elements need to also be redisplayed
    // so they can be expanded upon again, if requested
    $resource.find('.ref-expander').show();

    // Clear the list of expanded references
    $resource.find('.ref-expansions').html('');
}

$(function() {
    $('.collapsible').collapsible({
        accordion : false
    });

    $('.scrollspy').scrollSpy();

    $(".button-collapse").sideNav({
        menuWidth: 300,
        edge: 'left'
    });

    $(".actions a").click(function() {
        $('#query-builder').openModal();
        return false;
    });
    
    $('a.toggle-collapsible').on('click', function(e) {
        var $target = $(e.target);

        if ($target.html() == '-') {
            $target.html('+');
            $target.parent().children('.collapsible').addClass('collapsed');
        } else {
            $target.html('-');
            $target.parent().children('.collapsible').removeClass('collapsed');
        }
        return false;
    });

    $('a.toggle-all-collapsible').on('click', function(e) {
        var $target = $(e.target);

        // Use $.find over $.children to target everything in the tree
        if ($target.html() == '-') {
            $target.parent().find('.collapsible').addClass('collapsed');
            $target.parent().find('.toggle-collapsible, .toggle-all-collapsible').html('+');
        } else {
            $target.parent().find('.collapsible').removeClass('collapsed');
            $target.parent().find('.toggle-collapsible, .toggle-all-collapsible').html('-');
        }

        return false;
    });

    $('#filter-paths').on('keyup', function(e) {

        var filter = $(this).val();

        // show everything
        if (filter.length < 1) {
            $('.path').show();
        } else {
            // Only show paths that start with the search filter
            $('.path[id^="' + filter + '"]').show();
            $('.path:not([id^="' + filter + '"])').hide();
        }
    });

    $('a.ref-expander').on('click', function(e) { 

        var $target = $(e.target);
        
        if ($target.attr('href').indexOf('#/definitions/') == 0) {
            var $definitionAnchor = $('a[id="' + $target.attr('href').substr(1) + '"]')
            var $definition = $definitionAnchor.parent().children('.attr');
            var $clone = $definition.clone(true);

            resetExpansions($clone);

            // Hide .ref-expander we clicked and render the new cloned resource
            $target.hide();

            // Append our expansion to our parent resource's ref-expansions

            // Update data-expansion-prefix for all expandable resources in the clone
            // to ourselves, so we can track what was actually expanded and report
            // on it
            var prefix = $target.data('expansion-key') + '.';

            $clone.find('.ref-expander').each(function() {
                $(this).data('expansion-key', prefix + $(this).data('expansion-key'));
            });

            // Append the resource definition
            $target.parent().find('.ref-resource').html($clone);
            
            /*var $refExpansions = $target.closest('.resource').find('.ref-expansions');

            if ($refExpansions.html().length < 1) {
                $refExpansions.html('?expand=' + $target.data('expansion-key'));
            } else {
                $refExpansions.append(',' + $target.data('expansion-key'));
            }*/
        }

        e.preventDefault();
        return false;
    });

    $('#toggle-json').change(function() {
        if ($(this).is(":checked")) {
            $('.resource').addClass('json');
        } else {
            $('.resource').removeClass('json');
        }
    });
});
