$(function() {
    $('#siteSearch form').submit(function(e){
        e.preventDefault();
        Util.clearPrepopulatedTextbox($(this));
        $(this).unbind('submit');
        $(this).trigger('submit');
    });
});