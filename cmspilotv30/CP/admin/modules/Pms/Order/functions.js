$("select[name='subsidy_paid']").change(function(){
    var order_item_id = $(this).attr('id');
    var subsidy_paid_status = $(this).val();
    var url = 'index.php?_topRm=reports&module=pms_order&_spAction=updateSubsidyStatus&showHTML=0&order_item_id=' + order_item_id + '&subsidy_paid_status' + subsidy_paid_status;
    Util.showProgressInd();
    $.get(url, function(html){
    alert ('Status Updated Succesfully');
    Util.hideProgressInd();
    });
});

Util.createCPObject('cpm.pms.order');

$('.check-all').livequery('click', function (e){
    cpm.pms.order.checkAllRow.call(this)
});

$('.uncheck-all').livequery('click', function (e){
    cpm.pms.order.uncheckAllRow.call(this)
});

$('.cancelInvoice').livequery('click', function (e){
    msg = "Do you like to cancel the Invoice?";
    if (!confirm(msg)){
        return false;
    }
    else {
        var url = 'index.php?_topRm=finance&module=pms_order&_spAction=cancelInvoice&showHTML=0';
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

$('.cancelReceipt').livequery('click', function (e){
    msg = "Do you like to cancel the Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else {
        var url = 'index.php?_topRm=finance&module=pms_order&_spAction=cancelReceipt&showHTML=0';
        Util.showProgressInd();
        var receipt_code = $(this).attr('receipt_code');
        $.get(url,{receipt_code: receipt_code}, function(){
            alert ('Receipt Cancelled Succesfully');
            Util.hideProgressInd();
            window.location.reload(true);
        });
    }
});

cpm.pms.order = {
    init: function(){
        $('.check-all').click(cpm.pms.order.checkAllRow);
        $('.click-all-side .uncheck-all').click(cpm.pms.order.uncheckAllRow);
    },

    checkAllRow: function(e){
        $(this).parents('tr').find('input[type="checkbox"]').attr('checked', true);
    },

    uncheckAllRow: function(e){
        $(this).parents('tr').find('input[type="checkbox"]').attr('checked', false);
    },

    printOrder:function(){
        var recordId = $('#record_id').val();
        var room = $('#cpRoom').val();
        var topRoom = $('#cpTopRoom').val();
        var url = 'index.php?module=pms_order&_spAction=printOrder&order_id=' + recordId;
        $.get(url, function(html){
        //alert ('Status Updated Succesfully');
        //Util.hideProgressInd();
        });
    }
}

$('#generateInvoice').livequery('click', function (e){
    msg = "Do you like to Generate Invoice?";
    if (!confirm(msg)){
        return false;
    }
});

$('#generateInvoicePvt').livequery('click', function (e){
    msg = "Do you like to Generate Invoice?";
    if (!confirm(msg)){
        return false;
    }
    else{
        var title = "Create Invoice";
        e.preventDefault();
        
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Invoice created successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);        
    }
});

$('#editInvoicePvt').livequery('click', function (e){
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
    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);        
});

$('#deleteInvoicePvt').livequery('click', function (e){
    msg = "Do you like to delete the Invoice?";
    if (!confirm(msg)){
        return false;
    }
    else {
        var url = 'index.php?_topRm=finance&module=pms_order&_spAction=deleteInvoiceFormPvt&showHTML=0';
        Util.showProgressInd();
        var invoice_id = $(this).attr('invoice_id');
        $.get(url,{invoice_id: invoice_id}, function(html){
        alert ('Status Updated Succesfully');
        Util.hideProgressInd();
        });
    }
});

$('.editReceiptPvt').livequery('click', function (e){
    var title = "Edit Receipt";
    e.preventDefault();
    
    $("select[name='mode_of_payment']").livequery('change', function (e){
        cpm.pms.order.populatePaymentMode.call(this);
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

$(".clearInvoiceCode").livequery('click', function (e){
    msg = "Do you like to Clear the Existing Invoice?";
    if (!confirm(msg)){
        return false;
    }
    var order_item_id = $(this).attr('order_item_id');
    var url = 'index.php?module=pms_order&_spAction=clearInvoice&showHTML=0';
    $.get(url, {order_item_id: order_item_id}, function(html){
        document.location = document.location;
    });
});


/*$("select[name='status']").change(function(){
    var invoice_id = $(this).attr('id');
    var invoice_status = $(this).val();
    var url = 'index.php?_topRm=finance&module=pms_order&_spAction=updateInvoiceStatus&showHTML=0';
    Util.showProgressInd();
    $.get(url,{invoice_id: invoice_id,invoice_status:invoice_status}, function(html){
    alert ('Status Updated Succesfully');
    Util.hideProgressInd();
    });
});*/


$('#generateReceipt').livequery('click', function (e){
    msg = "Do you like to Generate Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else{
        var title = "Create Receipt";
        e.preventDefault();
        
        $("select[name='mode_of_payment']").livequery('change', function (e){
            cpm.pms.order.populatePaymentMode.call(this);
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
    }
});

$('#generateReceiptEnt').livequery('click', function (e){
    var title = "Create Receipt";
    e.preventDefault();
    
    $("select[name='mode_of_payment']").livequery('change', function (e){
        cpm.pms.order.populatePaymentMode.call(this);
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
    Util.openFormInDialog.call(this, 'portalForm', title, 700, 500, expObj);        
});

$('#generateReceiptPvt').livequery('click', function (e){
    msg = "Do you like to Generate Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else{
        var title = "Create Receipt";
        e.preventDefault();
        
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
    }
});

$('#generateMiscReceiptPvt').livequery('click', function (e){
    msg = "Do you like to Generate Misc Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else{
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
    }
});

$('#generateMonthlyInvoice').livequery('click', function (e){
    var title = "Create Invoice";
    e.preventDefault();
    
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
    Util.openFormInDialog.call(this, 'portalForm', title, 900, 400, expObj);        
});

$('#applySpecialDiscount').livequery('click', function (e){
    var title = "Apply Special Discount";
    e.preventDefault();
    
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Discount updated successfully for all due invoices';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 550, 275, expObj);        
});

$('#generateCreditNote').livequery('click', function (e){
    msg = "Do you like to Generate Credit Note?";
    if (!confirm(msg)){
        return false;
    }
    else{
        var title = "Create Credit Note";
        e.preventDefault();
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
    }
});

$('#generateRefund').livequery('click', function (e){
    msg = "Do you like to Generate Refund?";
    if (!confirm(msg)){
        return false;
    }
    else{
        var title = "Create Refund";
        e.preventDefault();
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
    }
});

$('input.invoiceCode').livequery('click', function (e){
    Util.showProgressInd();
    invoice_code = $(this).val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;

    var url = 'index.php?_topRm=finance&module=pms_order&_spAction=populateReceiptAmount&showHTML=0';
    $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
        $('#totalAmountPayable').html('');
        $('input[id=fld_amount]').val(html);
    });
    
    var url = 'index.php?module=pms_order&_spAction=populateDiscountAmount&showHTML=0';
    $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
        $('input[id=fld_discount_amount]').val(html);
        Util.hideProgressInd();
    });
});

$('input.invoiceCodePvt').livequery('click', function (e){
    Util.showProgressInd();
    invoice_hist_id = $(this).val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;

    var url = 'index.php?_topRm=finance&module=pms_order&_spAction=populateReceiptAmountPvt&showHTML=0';
    $.get(url,{invoice_hist_id: invoice_hist_id,checkedVal:checkedVal}, function(html){
        $('input[id=fld_amount]').val(html);
        Util.hideProgressInd();
    });
});

/* Amount population when invoice is clicked for misc receipt */
$('input.invoiceCodeMiscPvt').livequery('click', function (e){
    Util.showProgressInd();
    invoice_hist_id = $(this).val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;

    var url = 'index.php?_topRm=finance&module=pms_order&_spAction=populateReceiptAmountMiscPvt&showHTML=0';
    $.get(url,{invoice_hist_id: invoice_hist_id,checkedVal:checkedVal}, function(html){
        $('input[id=fld_amount]').val(html);
        Util.hideProgressInd();
    });
});

$('input.receiptCode').livequery('click', function (e){
    Util.showProgressInd();
    receipt_code = $(this).val();

    var url = 'index.php?_topRm=finance&module=pms_order&_spAction=populateRefundAmount&showHTML=0';
    $.get(url,{receipt_code: receipt_code}, function(html){
        $('input[id=fld_amount]').val(html);
        Util.hideProgressInd();
    });
});

$("#fld_invoice_id").livequery('change', function(){
    Util.showProgressInd();
    invoice_id = $(this).val();

    var url = 'index.php?_topRm=finance&module=pms_order&_spAction=populateCreditNoteAmount&showHTML=0';
    $.get(url,{invoice_id: invoice_id}, function(html){
        $('input[id=fld_amount]').val(html);
        Util.hideProgressInd();
    });
});

cpm.pms.order.populatePaymentMode = function(){
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

cpm.pms.order.populateReceiptModeOfPayment = function(){
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

cpm.pms.order.populateMiscTotalAmount = function(){
    var late_fees       = $(".miscReceiptFormForPvt input[name='late_fees']").val();
    var change_fees     = $(".miscReceiptFormForPvt input[name='module_subject_change_fee']").val();
    var review_fees     = $(".miscReceiptFormForPvt input[name='exam_result_review_fee']").val();
    var deferment_fees  = $(".miscReceiptFormForPvt input[name='ns_deferment_fees']").val();
    var credit_card_fees = $(".miscReceiptFormForPvt input[name='credit_card_service_fees']").val();
    var other_fees      = $(".miscReceiptFormForPvt input[name='other_charges']").val();
    
    var url = 'index.php?_topRm=finance&module=pms_order&_spAction=populateMiscTotalAmount&showHTML=0';
    $.get(url,{late_fees: late_fees
              ,change_fees: change_fees
              ,review_fees: review_fees
              ,deferment_fees: deferment_fees
              ,credit_card_fees: credit_card_fees
              ,other_fees: other_fees
              }, function(html){
        $('.miscReceiptFormForPvt input[id=fld_amount]').val(html);
        Util.hideProgressInd();
    });

}

$(".invoiceDisplay select[name='status']").livequery('change', function (e){
    var status = $(this).val();
    //var order_id = $(this).attr('order_id');
    var order_id = $('#record_id').val();
    
    var url = 'index.php?module=pms_order&_spAction=invoiceRecords&showHTML=0';
    $.get(url,{status: status, order_id: order_id}, function(html){
        $('#invoicePortalOuter').html(html);
    });
});

$('#generateBookReceipt').livequery('click', function (e){
    msg = "Do you like to Generate Book Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else{
        var title = "Create Book Receipt";
        e.preventDefault();
        
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
    }
});

$('.receiptForm .populateAmountPayable').livequery('click', function (e){
    var discount_amount = $('.receiptForm #fld_discount_amount').val();
    var formName = $(this).closest('form').attr('name');

    var url = 'index.php?module=pms_order&_spAction=calculateAmountPayable&showHTML=0';
    Util.showProgressInd();
    $.get(url,{discount_amount: discount_amount,formName: formName}, function(html){
        $('#totalAmountPayable').html(html);
    });
    Util.hideProgressInd();
});

$(".receiptForm select[name='mode_of_payment']").livequery('change', function (e){
    var paymentMode = $(this).val();
    if (paymentMode != '') {
        alert("Your preferred mode of payment is " + paymentMode);
    }
});
