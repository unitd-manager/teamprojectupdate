Util.createCPObject('cpm.subscription.paymentSummary');

cpm.subscription.giroPayment = {
    init: function(){
        $('.click-all-top .check-all').click(cpm.subscription.paymentSummary.checkAllCol);
        $('.click-all-top .uncheck-all').click(cpm.subscription.paymentSummary.uncheckAllCol);
    },

    checkAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-paymentSummary-table tr').each(function(rowIndex, trObj) {
            if (rowIndex > 1) {
                var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
                checkbox.attr('checked', 'checked'); 
            }
        });
    },
    
    uncheckAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-paymentSummary-table tr').each(function(rowIndex, trObj) {
            if (rowIndex > 1) {
                var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
                checkbox.removeAttr('checked'); 
            }
        });
    },
}

/* Setting the invoice code in session */
$('.m-subscription_paymentSummary form.giroForm .invoiceCode').livequery('click', function(e){
    var invoice_code  = $(this).val();
    var is_checked  = $(this).is(':checked');

    var url = 'index.php?module=subscription_paymentSummary&_spAction=setInvoiceCodeForSession&showHTML=0';
 
    Util.showProgressInd();
    $.get(url, {invoice_code: invoice_code, is_checked: is_checked}, function(html){
        Util.hideProgressInd();
    });    
});

$('#generateReceiptForParent').livequery('click', function (e){
    var title = "Create Receipt";
    e.preventDefault();
    
    $("select[name='mode_of_payment']").livequery('change', function (e){
        cpm.subscription.giroPayment.populatePaymentMode.call(this);
    });
     
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            window.close();
            var msg = 'Updated successfully';

            var url = "index.php?_topRm=finance&module=subscription_order&_spAction=printGroupReceiptInFpdf&showHTML=0";
		    window.open(url,'_blank');

            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 700, 500, expObj);        
});

cpm.subscription.giroPayment.populatePaymentMode = function(){
    var paymentMode = $(this).val();
    if (paymentMode == 'Cheque') {
        Util.showProgressInd();
        $('form.receiptFormForParent .row_cheque_date').removeClass('hideme');
        $('form.receiptFormForParent .row_bank_name').removeClass('hideme');
        $('form.receiptFormForParent .row_cheque_no').removeClass('hideme');
        Util.hideProgressInd();
    } else {
        Util.showProgressInd();
        $('form.receiptFormForParent .row_cheque_date').addClass('hideme');
        $('form.receiptFormForParent .row_bank_name').addClass('hideme');
        $('form.receiptFormForParent .row_cheque_no').addClass('hideme');
        Util.hideProgressInd();
    }
}

$('.receiptFormForParent .populateAmountPayable').livequery('click', function (e){
    var discount_amount = $('.receiptFormForParent #fld_discount_amount').val();
    var formName = $(this).closest('form').attr('name');

    var url = 'index.php?_topRm=finance&module=subscription_order&_spAction=calculateAmountPayable&showHTML=0';
    Util.showProgressInd();
    $.get(url,{discount_amount: discount_amount, formName: formName}, function(html){
        $('#totalAmountPayable').html(html);
    });
    Util.hideProgressInd();
});

$(".receiptFormForParent select[name='mode_of_payment']").livequery('change', function (e){
    var paymentMode = $(this).val();
    if (paymentMode != '') {
        alert("Your preferred mode of payment is " + paymentMode);
    }
});
