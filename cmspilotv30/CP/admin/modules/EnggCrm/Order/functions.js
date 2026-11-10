Util.createCPObject('cpm.enggCrm.order');

cpm.enggCrm.order = {
    init: function(){
        $('.m-enggCrm_order input.invoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;
            var order_id   = $(this).attr('order_id');

            var url = 'index.php?_topRm=finance&module=enggCrm_receipt&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal, order_id: order_id}, function(html){
                $('input[id=fld_amount]').val(html);
                Util.hideProgressInd();
            });
        });

        /* Adding multiple rows in invoice */
        $("a.generateInvoice").livequery('click', function (e){
            var title = "Generate Invoice";
            var order_id    = $(this).attr('order_id');
            var record_type = $(this).attr('record_type');
            var url = 'index.php?module=enggCrm_invoice&_spAction=generateInvoiceForm'
                    + '&showHTML=0&order_id='+order_id+'&record_type='+record_type;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Invoice created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };
            Util.openFormInDialog.call(this, 'generateInvoiceForm', title, 1100, 530, exp);
        });

         $("select[name='invoice_type']").livequery('change', function (e){
                var invoice_type = $(this).val();
                if(invoice_type == 'LOT'){
                    $('.invoice_creation_text').removeClass("invoice_creation_text_disable");
                    $('.invoice_creation_description').removeClass("invoice_creation_description_disable");
                }
                else{
                    $('.invoice_creation_text input').val('');
                    $('.invoice_creation_text').addClass("invoice_creation_text_disable");
                    $('.invoice_creation_description').removeClass("invoice_creation_description_disable");
                }
         });

        $("input#fld_invoice_start_date").livequery('change', function (e){
            var start_date = $(this).val();
            var end_date  = $("input#fld_invoice_end_date").val();
            var order_id  = $("input#fld_order_id").val();

            cpm.enggCrm.order.reloadInvoiceItems(order_id, start_date, end_date);
        });

        $("input#fld_invoice_end_date").livequery('change', function (e){
            var end_date = $(this).val();
            var start_date  = $("input#fld_invoice_start_date").val();
            var order_id  = $("input#fld_order_id").val();

            cpm.enggCrm.order.reloadInvoiceItems(order_id, start_date, end_date);
        });

        $("a.generateReceipt").livequery('click', function (e){
            var title = "Generate Receipt";
            e.preventDefault();

            $("select[name='mode_of_payment']").livequery('change', function (e){
                cpm.enggCrm.order.populatePaymentMode.call(this);
            });

            var order_id = $(this).attr('order_id');
            var url = 'index.php?module=enggCrm_receipt&_spAction=generateReceiptForm'
                    + '&showHTML=0&order_id=' + order_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };
            Util.openFormInDialog.call(this, 'portalForm', title, 700, 500, exp);
        });

        $("a.generateCreditNote").livequery('click', function (e){
            var title = "Generate Credit Note";
            e.preventDefault();

            var order_id = $(this).attr('order_id');
            var url = 'index.php?module=enggCrm_invoice&_spAction=generateCreditNoteForm'
                    + '&showHTML=0&order_id=' + order_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Credit Note created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };
            Util.openFormInDialog.call(this, 'portalForm', title, 1200, 500, exp);
        });

        /* Adding row in invoice */
        $("#generateInvoiceForm a.addRow").livequery('click', function (e){
            var url = 'index.php?module=enggCrm_invoice&_spAction=addInvoiceItemRecord'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#generateInvoiceForm .maintable tr:last').after(html);
            });
        });

        $("#generateInvoiceForm .invoiceItemQuantity").livequery('change', function (e){
            cpm.enggCrm.order.triggerCalcForQuantity.call(this);

            var totalqty = 0;
            var totalprice = 0;

            var total_Qty = document.getElementsByClassName('invoiceItemQuantity');
            var total_Price = document.getElementsByClassName('invoiceItemAmount');

            var total_amount = 0;
            for (var i = 0; i < total_Qty.length; ++i) {
                if (!isNaN(parseInt(total_Qty[i].value)) ){
                    totalqty = parseInt(total_Qty[i].value);
                }

                if (!isNaN(parseInt(total_Price[i].value)) ){
                    totalprice = parseInt(total_Price[i].value);
                }

                if (!isNaN(parseInt(total_Qty[i].value)) && !isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += totalqty * totalprice;
                }

            }

            var discount = $('.discount_input').val();
            total_amount = total_amount - discount;

            $('.total_Amount_Invoice').html(total_amount.toFixed(2));
        });

        $("#generateInvoiceForm .invoiceItemAmount").livequery('change', function (e){
            cpm.enggCrm.order.triggerCalcForAmount.call(this);

            var totalqty = 0;
            var totalprice = 0;

            var total_Qty = document.getElementsByClassName('invoiceItemQuantity');
            var total_Price = document.getElementsByClassName('invoiceItemAmount');

            var total_amount = 0;
            for (var i = 0; i < total_Qty.length; ++i) {
                if (!isNaN(parseInt(total_Qty[i].value)) ){
                    totalqty = parseInt(total_Qty[i].value);
                }

                if (!isNaN(parseInt(total_Price[i].value)) ){
                    totalprice = parseInt(total_Price[i].value);
                }

                if (!isNaN(parseInt(total_Qty[i].value)) && !isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += totalqty * totalprice;
                }

            }

            var discount = $('.discount_input').val();
            total_amount = total_amount - discount;

            $('.total_Amount_Invoice').html(total_amount.toFixed(2));

        });

        $("a.clearInvoiceItem").livequery('click', function (e){
            var titleObj = $(this).closest('tr').find('.invoiceItemTitle');
            var quantityObj = $(this).closest('tr').find('.invoiceItemQuantity');
            var unitObj = $(this).closest('tr').find('.invoiceItemUnit');
            var amountObj = $(this).closest('tr').find('.invoiceItemAmount');
            var totalCostObj = $(this).closest('tr').find('.totalCost');
            var descriptionObj = $(this).closest('tr').find('.invoiceItemDescription');
            var remarksObj = $(this).closest('tr').find('.invoiceItemRemarks');

            titleObj.val('');
            quantityObj.val('');
            unitObj.val('');
            amountObj.val('');
            totalCostObj.html('');
            descriptionObj.val('');
            remarksObj.val('');
        });

        /* Adding multiple rows in detail invoice */
        $("a.generateDetailInvoice").livequery('click', function (e){
            var title = "Generate Invoice";
            var order_id = $(this).attr('order_id');
            var record_type = $(this).attr('record_type');
            var url = 'index.php?module=enggCrm_invoice&_spAction=generateDetailInvoiceForm'
                    + '&showHTML=0&order_id='+order_id+'&record_type='+record_type;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Invoice created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };
            Util.openFormInDialog.call(this, 'generateDetailInvoiceForm', title, 1180, 500, exp);
        });

        /* Adding row in detail invoice */
        $("#generateDetailInvoiceForm a.addRow").livequery('click', function (e){
            var url = 'index.php?module=enggCrm_invoice&_spAction=addInvoiceItemRecord'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#generateDetailInvoiceForm .maintable tr:last').after(html);
            });
        });

        /* Adding row in detail invoice */
        $(".generateDetailInvoiceForm a.addRow").livequery('click', function (e){
            var record_type = $(this).attr('record_type');

            if(record_type == 'Manpower Supply'){
                var url = 'index.php?module=enggCrm_invoice&_spAction=addInvoiceItemRecordManpower'
                    +'&showHTML=0';
            }else{
                var url = 'index.php?module=enggCrm_invoice&_spAction=addInvoiceItemRecordDetail'
                        +'&showHTML=0';

            }

            $.get(url, '' ,function(html){
                $('.generateDetailInvoiceForm .room-invoice-table tr:last').after(html);
            });
        });

        $("#generateDetailInvoiceForm .invoiceItemQuantity").livequery('keyup', function (e){
            cpm.enggCrm.order.triggerCalcForQuantity.call(this);

            var totalqty = 0;
            var totalprice = 0;

            var total_Qty = document.getElementsByClassName('invoiceItemQuantity');
            var total_Price = document.getElementsByClassName('invoiceItemAmount');

            var total_amount = 0;
            for (var i = 0; i < total_Qty.length; ++i) {
                if (!isNaN(parseInt(total_Qty[i].value)) ){
                    totalqty = parseInt(total_Qty[i].value);
                }

                if (!isNaN(parseInt(total_Price[i].value)) ){
                    totalprice = parseInt(total_Price[i].value);
                }

                if (!isNaN(parseInt(total_Qty[i].value)) && !isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += totalqty * totalprice;
                }

            }

            var discount = $('.discount_input').val();
            total_amount = total_amount - discount;

            $('.total_Amount_Invoice').html(total_amount.toFixed(2));

        });

        $('.room-invoice-table input.orderItem_record_id, .room-invoice-table tbody tr input[id=quantity]').livequery('keyup', function (e){
            Util.showProgressInd();
            var parent = $(this).closest('tr');
            var qtyBalance = $('input[name=qty_balance]', parent).val();
            var qtyBalance = (qtyBalance != '') ? parseInt(qtyBalance) : parseInt(0);
            var qty = $('input[id=quantity]', parent).val();
            var qty = (qty != '') ? parseInt(qty) : parseInt(0);

            if(qty == 0){
                Util.alert('Please enter the qty')
            } else if(qty > qtyBalance){
                Util.alert('The qty should not be more than the balance qty')
            }

            Util.hideProgressInd();
        });

        $("#generateDetailInvoiceForm .invoiceItemAmount").livequery('change', function (e){
            cpm.enggCrm.order.triggerCalcForAmount.call(this);

            var totalqty = 0;
            var totalprice = 0;

            var total_Qty = document.getElementsByClassName('invoiceItemQuantity');
            var total_Price = document.getElementsByClassName('invoiceItemAmount');

            var total_amount = 0;
            for (var i = 0; i < total_Qty.length; ++i) {
                if (!isNaN(parseInt(total_Qty[i].value)) ){
                    totalqty = parseInt(total_Qty[i].value);
                }

                if (!isNaN(parseInt(total_Price[i].value)) ){
                    totalprice = parseInt(total_Price[i].value);
                }

                if (!isNaN(parseInt(total_Qty[i].value)) && !isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += totalqty * totalprice;
                }

            }

            var discount = $('.discount_input').val();
            total_amount = total_amount - discount;

            $('.total_Amount_Invoice').html(total_amount.toFixed(2));

        });

        $('.click-all-top .check-all').livequery('click', function(e){
            e.preventDefault();
            cpm.enggCrm.order.checkAllCol.call(this);
        });

        $('.click-all-top .uncheck-all').livequery('click', function(e){
            e.preventDefault();
            cpm.enggCrm.order.uncheckAllCol.call(this);
        });

        $('.m-enggCrm_order input[name=cst]').livequery('click', function (e){
            cst = $(this).val();
            if(cst == 1){
                $('.m-enggCrm_order .cst_value').show();
                $('.m-enggCrm_order .cstValueNew').show();
            } else {
                $('.m-enggCrm_order .cst_value').hide();
                $('.m-enggCrm_order .cstValueNew').hide();
            }
        });

        $('.m-enggCrm_order input[name=vat]').livequery('click', function (e){
            vat = $(this).val();
            if(vat == 1){
                $('.m-enggCrm_order .vat_value').show();
                $('.m-enggCrm_order .vatValueNew').show();
            } else {
                $('.m-enggCrm_order .vat_value').hide();
                $('.m-enggCrm_order .vatValueNew').hide();
            }
        });

        $('.vat_code_generate').livequery('click', function (e){
            var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=invoiceCodeVatUpdate&showHTML=0';
            Util.showProgressInd();
            var order_id     = $(this).attr('order_id');
            var invoice_id   = $(this).attr('invoice_id');
            var invoice_date = $(this).attr('invoice_date');
            $.get(url,{invoice_date: invoice_date, invoice_id:invoice_id, order_id:order_id}, function(html){
                alert ('Invoice Code Generated Succesfully');
                Util.hideProgressInd();
                window.location.reload(true);
            });
        });

        $('.discount_input').live('keyup', function (e){
            var totalqty = 0;
            var totalprice = 0;

            var total_Qty = document.getElementsByClassName('invoiceItemQuantity');
            var total_Price = document.getElementsByClassName('invoiceItemAmount');

            var total_amount = 0;
            for (var i = 0; i < total_Qty.length; ++i) {
                if (!isNaN(parseInt(total_Qty[i].value)) ){
                    totalqty = parseInt(total_Qty[i].value);
                }

                if (!isNaN(parseInt(total_Price[i].value)) ){
                    totalprice = parseInt(total_Price[i].value);
                }

                if (!isNaN(parseInt(total_Qty[i].value)) && !isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += totalqty * totalprice;
                }

            }

            var discount = $(this).val();
            total_amount = total_amount - discount;

            $('.total_Amount_Invoice').html(total_amount.toFixed(2));

        });

        $('.showHideCancelledInvoice').livequery('click', function (e){
            var link_text = $(this).html();

            if(link_text == '(+) Click to View Cancelled Invoice(s)'){
                $('.showHideCancelledInvoice').text('(-) Click to Hide Cancelled Invoice(s)');
            }
            else{
                $('.showHideCancelledInvoice').text('(+) Click to View Cancelled Invoice(s)');
            }

            $('.cancelledInvoiceTableOrder').slideToggle();
        });

    },

    reloadInvoiceItems: function(order_id, start_date, end_date){
        var url = "index.php?module=enggCrm_order&_spAction=generateDetailInvoiceOrderItem&order_id="+order_id
                  +"&start_date="+start_date+"&end_date="+end_date+"&showHTML=0";
        $.get(url,  function(html){
            $('#changingResultRows').html(html);
            Util.hideProgressInd();
        });
    },

    checkAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-order-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
            checkbox.attr('checked', 'checked');
        });
        cpm.enggCrm.order.updateInvoiceAmount();
    },

    uncheckAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-order-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
            checkbox.removeAttr('checked');
        });
        $('.invoiceForm #fld_invoice_amount').html(0);
    },

    updateInvoiceAmount: function(){
        var amount = parseInt(0);
        $('.room-order-table tbody tr input[type=checkbox]:checked').each(function(){
            var parent = $(this).closest('tr');
            var valueObj = $('td.sellingPrice', parent);
            if(valueObj.text() != ''){
                var qtyObj = $(this).parents('tr').find('input[id=fld_qty]');
                var qty = (qtyObj.val() != '') ? parseInt(qtyObj.val()) : parseInt(0);

                amount += Math.round(parseFloat(valueObj.text()) * qty);
            }
        });
        $('.invoiceForm #fld_invoice_amount').html(amount);

        /* $('.room-order-table tbody tr input[name=qty]').livequery('change', function(){
            Util.showProgressInd();
            var parent = $(this).closest('tr');
            order_item_id = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var qtyObj = $(this).parents('tr').find('input[name=qty]');
            var qty = qtyObj.val();*/
            /*var priceObj = $('td.sellingPrice', parent);
            var price = priceObj.text();
            var valueObj = qty * price;
            if(valueObj != ''){
                amount += valueObj;
            }*/
         /*   var url = 'index.php?_topRm=finance&module=tradingin_order&_spAction=populateInvoiceAmount&showHTML=0';
            $.get(url,{order_item_id: order_item_id ,checkedVal: checkedVal, qty: qty}, function(html){
                $('.invoiceForm input[id=fld_invoice_amount]').val(html);
                Util.hideProgressInd();
            });
        });*/
    }
}

$('.m-enggCrm .actionBtnsDetail #generateInvoice').livequery('click', function (e){
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
    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
});

$('.m-enggCrm_order #generateSalesReturn').livequery('click', function (e){
    var title = "Create Sales Return";
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Sales Return created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
});

$('.room-order-table input.orderItemId, .room-order-table input.invoiceItemId, .room-order-table tbody tr input[id=fld_qty]').livequery('change', function (e){
    Util.showProgressInd();

    var parent = $(this).closest('tr');
    var qtyBalance = $('td.qtyBalance', parent).text();
    var qty = $('input[id=fld_qty]', parent).val();
    var cbObj = $('input.orderItemId', parent);
    var checked = cbObj.is(":checked") ? true : false;
    var cbObj1 = $('input.invoiceItemId', parent);
    var checked1 = cbObj1.is(":checked") ? true : false;
    var qty = (qty != '') ? parseInt(qty) : parseInt(0);

    if((qty == 0 && checked) || (qty == 0 && checked1)){
        Util.alert('Please enter the qty')
    } else if(qty > qtyBalance && checked){
        Util.alert('The qty should not be more than than the balance qty')
    } else {
        cpm.enggCrm.order.updateInvoiceAmount();
    }

    Util.hideProgressInd();
});

$('.cancelInvoice').livequery('click', function (e){
    var invoice_status = $(this).attr('invoice_status');

    if (invoice_status != 'Paid') {
        msg = "Do you want to cancel the Invoice?";
        if (!confirm(msg)){
            return false;
        }
        else {
            var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelInvoice&showHTML=0';
            Util.showProgressInd();
            var invoice_code = $(this).attr('invoice_code');
            var invoice_id = $(this).attr('invoice_id');
            $.get(url,{invoice_code: invoice_code, invoice_id:invoice_id}, function(html){

                /* Checking for one or more receipt for the invoice */
                if (html == 'Cannot cancel') {
                    Util.alert ('Cancel the related receipts and then proceed canceling the invoice');
                    Util.hideProgressInd();
                } else {
                    alert ('Invoice Cancelled Succesfully');
                    Util.hideProgressInd();
                    window.location.reload(true);
                }
            });
        }
    } else {
        msg = "Please cancel the receipt and then try canceling the Invoice";
        if (!confirm(msg)){
            return false;
        } else {
            return false;
        }
    }
});

$('.cancelReceipt').livequery('click', function (e){
    msg = "Do you like to cancel the Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else {
        var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelReceipt&showHTML=0';
        Util.showProgressInd();
        var receipt_id = $(this).attr('receipt_id');
        $.get(url,{receipt_id: receipt_id}, function(html){
            alert ('Receipt Cancelled Succesfully');
            Util.hideProgressInd();
            window.location.reload(true);
        });
    }
});

$('#displayText').livequery('click', function (e){

    var ele = document.getElementById('toggleText');
    var text = document.getElementById('displayText');

    if(ele.style.display == 'block') {
        ele.style.display = 'none';
        text.innerHTML = 'Show More Fields (+)';
    }
    else {
        ele.style.display = 'block';
        text.innerHTML = 'Hide More Fields (-)';
    }
});

$('#generateFullInvoice').livequery('click', function (e){
    var order_id = $(this).attr('order_id');
    var url = 'index.php?module=enggCrm_order&_spAction=generateFullInvoice&showHTML=0';

    msg = "Do you like to create full invoice?";

    if (!confirm(msg)){
        return false;
    }
    else{
       Util.showProgressInd();
       $.get(url, {order_id: order_id}, function(){
            Util.hideProgressInd();
            Util.alert('Invoice Created Succesfully!');
            window.location.reload(true);
       });
    }

});

$('#total_button').livequery('click', function(e){
    cpm.enggCrm.order.updateInvoiceAmount();
});

cpm.enggCrm.order.populatePaymentMode = function(){
    var paymentMode = $(this).val();
    if (paymentMode == 'Cheque') {
        Util.showProgressInd();
        $('form.receiptForm .row_cheque_no').removeClass('hideme');
        $('form.receiptForm .row_cheque_date').removeClass('hideme');
        $('form.receiptForm .row_bank_name').removeClass('hideme');
        Util.hideProgressInd();
    } else {
        Util.showProgressInd();
        $('form.receiptForm .row_cheque_no').addClass('hideme');
        $('form.receiptForm .row_cheque_date').addClass('hideme');
        $('form.receiptForm .row_bank_name').addClass('hideme');
        Util.hideProgressInd();
    }
}

cpm.enggCrm.order.triggerCalcForQuantity = function(){
    var quantity = $(this).val();
    var amountObj = $(this).closest('tr').find('.invoiceItemAmount');
    var amount = amountObj.val();
    var totalCostObj = $(this).closest('tr').find('.totalCost');
    var qtyBalanceVal = $(this).closest('tr').find('input[name=qty_balance]');
    var qtyBalanceObj = $(this).closest('tr').find('.invoiceItemBalanceQuantity');
    var qtyBalance = qtyBalanceVal.val();

    if (quantity > 0 && amount > 0) {
        var total_cost = quantity * amount;
        var total_cost_formatted = (total_cost).toFixed(2);
        totalCostObj.html(total_cost_formatted);
        qtyBalanceObj.html((qtyBalance - quantity).toFixed(2));
    }
}

cpm.enggCrm.order.triggerCalcForAmount = function(){
    var amount = $(this).val();
    var quantityObj = $(this).closest('tr').find('.invoiceItemQuantity');
    var quantity = quantityObj.val();
    var totalCostObj = $(this).closest('tr').find('.totalCost');

    if (quantity > 0 && amount > 0) {
        var total_cost = quantity * amount;
        var total_cost_formatted = (total_cost).toFixed(2);
        totalCostObj.html(total_cost_formatted);
    } else if (amount > 0 && quantity == '') {
        var total_cost_formatted = amount;
        totalCostObj.html(total_cost_formatted);
    }
}

$('.editInvoice').livequery('click', function (e){
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
    Util.openFormInDialog.call(this, 'editInvoicePortalForm', title, 600, 500, expObj);
});

$('.editCreditNote').livequery('click', function (e){
    var title = "Edit Credit Note";
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Credit Note updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'editCreditNotePortalForm', title, 600, 500, expObj);
});
