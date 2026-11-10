Util.createCPObject('cpm.agileIms.order');

cpm.agileIms.order = {
    init: function(){
        $('.check-all').click(cpm.agileIms.order.checkAllRow);
        $('.click-all-side .uncheck-all').click(cpm.agileIms.order.uncheckAllRow);
    },

    checkAllRow: function(e){
        $(this).parents('tr').find('input[type="checkbox"]').attr('checked', true);
    },

    cbAfterGenerateInvoice: function(e){
        var msg = 'Updated successfully';
        Util.alert(msg, function(){
            Util.closeAllDialogs();
            window.location.reload(true);
        });
    }
}

/* Generate Invoice form */
$('#generateInvoice').livequery('click', function (e){
    e.preventDefault();

    if($('input[name=traineeId\[\]]:checked').length == 0){
        Util.alert("Please check atleast one trainee");
        return false;
    }

    msg = "Do you like to Generate Invoice?";
    if (!confirm(msg)){
        return false;
    } else {
        Util.showProgressInd('Generating Invoice.... Please wait');
        $('#orderItemList').submit();
        Util.hideProgressInd();
    }
});



/* Generate Receipt form */
$('#generateReceipt').livequery('click', function (e){
    var title = "Create Receipt";
    e.preventDefault();
    
    $("select[name='mode_of_payment']").livequery('change', function (e){
        cpm.agileIms.order.populatePaymentMode.call(this);
    });
     
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);        
});

/* Edit Receipt in Receipt portal */
$('#editReceipt').livequery('click', function (e){
    var title = "Edit Receipt";
    e.preventDefault();
    
    $("select[name='mode_of_payment']").livequery('change', function (e){
        cpm.agileIms.order.populatePaymentMode.call(this);
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
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 350, expObj);        
});

/* Population of Cheque date, Bank name and Cheque no when payment mode is chosen as Cheque in Generate Receipt form */
cpm.agileIms.order.populatePaymentMode = function(){
    var paymentMode = $(this).val();
    if (paymentMode == 'Cheque') {
        Util.showProgressInd();
        $('form.receiptForm .row_cheque_date').removeClass('hideme');
        $('form.receiptForm .row_bank_name').removeClass('hideme');
        $('form.receiptForm .row_cheque_no').removeClass('hideme');
        Util.hideProgressInd();
    } else {
        Util.showProgressInd();
        $('form.receiptForm .row_cheque_date').addClass('hideme');
        $('form.receiptForm .row_bank_name').addClass('hideme');
        $('form.receiptForm .row_cheque_no').addClass('hideme');
        Util.hideProgressInd();
    }
}

/* Population of receipt amount when Invoice is checked in Generate Receipt form */
$('input.invoiceCode').livequery('click', function (e){
    Util.showProgressInd();
    invoice_code = $(this).val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;

    var url = 'index.php?_topRm=finance&module=agileIms_order&_spAction=populateReceiptAmount&showHTML=0';
    $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
        $('#totalAmountPayable').html('');
        $('input[id=fld_amount]').val(html);
        Util.hideProgressInd();
    });
});

/* Status filter for the Invoices */
$(".invoiceDisplay select[name='status']").livequery('change', function (e){
    Util.showProgressInd();
    var status = $(this).val();
    var order_id = $('#record_id').val();
    
    var url = 'index.php?module=agileIms_order&_spAction=invoiceRecords&showHTML=0';
    $.get(url,{status: status, order_id: order_id}, function(html){
        $('#invoicePortalOuter').html(html);
    });
    Util.hideProgressInd();
});

/* Cancel of Receipt in Receipt portal */
$('.cancelReceipt').livequery('click', function (e) {
    msg = "Do you like to cancel the Receipt?";
    if (!confirm(msg)) {
        return false;
    }else {
        var url = 'index.php?_topRm=finance&module=agileIms_receipt&_spAction=cancelReceipt&showHTML=0';
        Util.showProgressInd();
        var receipt_code = $(this).attr('receipt_code');
        $.get(url,{receipt_code: receipt_code}, function(){
            alert ('Receipt Cancelled Succesfully');
            Util.hideProgressInd();
            window.location.reload(true);
        });
    }
});

/* Cancel of Invoice in Invoice portal */
$('.cancelInvoice').livequery('click', function (e) {
    msg = "Do you like to cancel the Invoice?";
    if (!confirm(msg)) {
        return false;
    } else {
        var url = 'index.php?_topRm=finance&module=agileIms_invoice&_spAction=cancelInvoice&showHTML=0';
        Util.showProgressInd();
        var invoice_code = $(this).attr('invoice_code');
        $.get(url,{invoice_code: invoice_code}, function(html){
            /* Checking for one or more receipt for the invoice */
            if (html == 'Cannot cancel') {
                alert ('Cancel the related receipts and then proceed canceling the invoice');
                Util.hideProgressInd();
            } else {
                alert ('Invoice Cancelled Succesfully');
                Util.hideProgressInd();
                window.location.reload(true); 
            }
        });
    }
});

/* Edit Invoice in Invoice portal */
$('#editInvoice').livequery('click', function (e){
    var title = "Edit Invoice";
    e.preventDefault();
    
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Invoice updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 350, expObj);        
});

/* Generate Misc Receipt in Right portal */
$('#generateMiscReceipt').livequery('click', function (e){
    var title = "Create Misc Receipt";
    e.preventDefault();
    
    $(".miscReceiptFormForPvt input[name='late_fees']").livequery('change', function(e){
        cpm.pms.order.populateMiscTotalAmount.call(this);
    });
    
    $(".miscReceiptFormForPvt input[name='module_subject_change_fee']").livequery('change', function(e){
        cpm.pms.order.populateMiscTotalAmount.call(this);
    });
    
    $(".miscReceiptFormForPvt input[name='exam_result_review_fee']").livequery('change', function(e){
        cpm.pms.order.populateMiscTotalAmount.call(this);
    });
    
    $(".miscReceiptFormForPvt input[name='ns_deferment_fees']").livequery('change', function(e){
        cpm.pms.order.populateMiscTotalAmount.call(this);
    });
    
    $(".miscReceiptFormForPvt input[name='credit_card_service_fees']").livequery('change', function(e){
        cpm.pms.order.populateMiscTotalAmount.call(this);
    });
    
    $(".miscReceiptFormForPvt input[name='other_charges']").livequery('change', function(e){
        cpm.pms.order.populateMiscTotalAmount.call(this);
    });
    
    cpm.pms.order.populateReceiptModeOfPayment.call(this);
     
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
});

