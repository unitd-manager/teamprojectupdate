Util.createCPObject('cpt.default');

cpt.default.init = function(){
};

$(function() {
    var body = $('body');
    if (body.hasClass('v-edit') || body.hasClass('v-detail')) {
        //previous
        $(document).bind('keyup', 'left', function() {
            $('#nav2 .preBtn a span').trigger('click');
        });
    
        //next
        $(document).bind('keydown', 'right', function() {
            $('#nav2 .nxtBtn a span').trigger('click');
        });
        
        //apply
        //$(document).bind('keydown', {combi: 'Ctrl+Shift+y', disableInInput: false}, function(e) {
        $('html, input, select, textarea').bind('keydown', 'ctrl+shift+y', function(e) {
            Actions.apply();
            e.stopPropagation();
            e.preventDefault();
            return false;
        });
    
        /*
        //delete
        $(document).bind('keydown', {combi: 'Ctrl+Shift+d', disableInInput: false}, function(e) {
            e.stopPropagation();
            e.preventDefault();
            Actions.deleteRecord();
            return false;
        });
    
        //edit
        $(document).bind('keydown', {combi: 'Ctrl+Shift+e', disableInInput: false}, function(e) {
            e.stopPropagation();
            e.preventDefault();
            $('.actionBtns #actBtn_edit').trigger('click');
            return false;
        });
        */
    }
});