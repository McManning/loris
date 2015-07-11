
$(function() {
    $('.collapsible').collapsible({
        accordion : false
    });

    $('.scrollspy').scrollSpy();

    $(".button-collapse").sideNav();

    $(".actions a").click(function() {
        $('#query-builder').openModal();
        return false;
    });
    
});
