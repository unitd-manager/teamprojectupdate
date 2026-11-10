Util.createCPObject('cpm.enggCrm.invoice');

cpm.enggCrm.invoice.init = function(){
    $("input#showInvoiceTerms").click(function() {
        Util.openDialogForLink.call($(this), 'Set Invoice Terms', 300, 300)
    });

    $("input#showInvoiceNotes").click(function() {
        Util.openDialogForLink.call($(this), 'Set Invoice Notes', 300, 300)
    });

    $('input.set_invoiceTerms').livequery('click', function(){
        var value = $('.value', $(this).closest('tr')).html();
        $('#fld_invoice_terms').val(value);
        $('#dialog').dialog('close');
        $('#dialog').dialog('destroy');
    });

    $('input.set_invoiceNotes').livequery('click', function(){
        var value = $('.value', $(this).closest('tr')).html();
        $('#fld_notes').val(value);
        $('#dialog').dialog('close');
        $('#dialog').dialog('destroy');
    });

    $("a.reminderMail").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Util.alert('Email Sent successfully..');
            }
        }
        Util.openFormInDialog.call(this, 'reminderEmail', title, 400, 200, expObj);
    });

    /* Used in Studio USS - Arif 10/05/14 */
    $('a.reminderMailSend').livequery('click', function(e){
        msg = "Do you like to send reminder Invoice?";
        
        if (!confirm(msg)){
            return false;
        } else {
            Util.showProgressInd('Please Wait...');
            var invoice_id = $(this).attr('invoice_id');
    
            var url = 'index.php?module=enggCrm_invoice&_spAction=sendReminderEmailSubmit&showHTML=0';
            $.get(url, {invoice_id: invoice_id}, function(html){
                    Util.closeAllDialogs();
                    Util.alert(html);
            });
            Util.hideProgressInd();
        }
    });
};

/*var Actions = {
    printListPDFInvoice: function (queryString){
        var room = $('#cpRoom').val();

        var roomNameArr = room.split("_");
        var reportName  = roomNameArr[1];

        var reportName = reportName + 'List';
        var url = $('#scopeRootAlias').val() + 'index.php?_topRm=project&module=project_invoice&_spAction=printInvoiceList&showHTML=0&roomName=' + room + '&report=' + reportName +
        '&' + queryString  + "target='_blank'";
        //document.location = url;
		window.open(url,'_blank');
    }
}*/

