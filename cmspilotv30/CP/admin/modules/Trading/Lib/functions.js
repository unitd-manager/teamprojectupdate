Util.createCPObject('cpm.trading.lib');

cpm.trading.lib = {
    showTermsDialog: function() {

    }
}

$(function() {
    $('.hasNotesRight.terms input[type=button]').click(function(e) {
        e.preventDefault();
        
        var url = $(this).attr('link');
        var exp = {
            url: url
           ,afterOpen: function() {
                $('.popcontents .term-selection input[type=button]').click(function() {
                    var field_name = $('#field_name').val();
                    //get text from TD
                    var valueText = $(this).parent().siblings('.value').html();
                    $('#' + field_name).val(valueText);
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                });
            }
        };
        Util.openDialogForLink('Choose Value',  400, 300, 0, exp);
    });
});