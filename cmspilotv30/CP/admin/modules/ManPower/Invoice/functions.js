Util.createCPObject('cpm.manPower.invoice');

cpm.manPower.invoice.init = function(){
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
};

$('.m-manPower_invoice .rightPanel #generateReceipt').livequery('click', function (e){
    msg = "Do you like to Generate Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else{
        var title = "Create Receipt";
        e.preventDefault();
        
        cpm.manPower.invoice.populateReceiptModeOfPayment.call(this);
         
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Receipt created successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);
    }
});

$('.m-manPower_invoice .rightPanel .editReceipt').livequery('click', function (e){
    var title = "Edit Receipt";
    e.preventDefault();
    
    $("select[name='mode_of_payment']").livequery('change', function (e){
        cpm.manPower.invoice.populateReceiptModeOfPayment.call(this);
    });
     
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Receipt updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);        
});

$('.m-manPower_invoice .rightPanel .cancelReceipt').livequery('click', function (e){
    msg = "Do you like to cancel the Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else {
        Util.showProgressInd();
        var receipt_code = $(this).attr('receipt_code');
        var url = 'index.php?_topRm=finance&module=manPower_receipt&_spAction=cancelReceipt&showHTML=0';
        $.get(url,{receipt_code: receipt_code}, function(){
            alert ('Receipt Cancelled Succesfully');
            Util.hideProgressInd();
            window.location.reload(true);
        });
    }
});

cpm.manPower.invoice.populateReceiptModeOfPayment = function(){
    $("select[name='mode_of_payment']").livequery('change', function (e){
        var paymentMode = $(this).val();
        if (paymentMode == 'Cheque') {
            Util.showProgressInd();
            $('.row_cheque_date').removeClass('hideme');
            $('.row_bank_name').removeClass('hideme');
            $('.row_cheque_no').removeClass('hideme');
            Util.hideProgressInd();
        } else {
            Util.showProgressInd();
            $('.row_cheque_date').addClass('hideme');
            $('.row_bank_name').addClass('hideme');
            $('.row_cheque_no').addClass('hideme');
            Util.hideProgressInd();
        }
    });
}
