
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
    
});
