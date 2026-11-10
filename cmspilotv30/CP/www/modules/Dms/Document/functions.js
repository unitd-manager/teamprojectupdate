Util.createCPObject('cpm.dms.document');

cpm.dms.document.init = function(){
    if($("#quickSearch input[name='keyword']").length){
        Util.prepopulatedTextbox();
        $('#quickSearch').submit(function(e){
            e.preventDefault();
            Util.clearPrepopulatedTextbox($(this));
            $(this).unbind('submit');
            $(this).trigger('submit');
        });
    }

    $("#quickSearch select[name='category_id']").live('change', function (e) {
        e.preventDefault();
        $('#quickSearch').submit();
    });
}
