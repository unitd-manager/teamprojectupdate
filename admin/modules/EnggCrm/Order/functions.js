Util.createCPObject('cpm.enggCrm.order');

cpm.enggCrm.order = {
    init: function(){
        $("#portalForm input[name=company_name]").livequery(function(){
            this.focus();
            this.setSelectionRange(this.value.length, this.value.length);
        });

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

        $("#editInvoicePortalForm .invoiceQuantity").livequery('change', function (e){
            var quantity = parseFloat($(this).val());
            var unitPrice = parseFloat($(this).closest('tr').find('.invoiceUnitPrice').val());
            var totalCostObj = $(this).closest('tr').find('.invoiceAmount');
            
            if (!isNaN(quantity) && !isNaN(unitPrice) && quantity >= 0 && unitPrice >= 0) {
                var totalCost = quantity * unitPrice;
                var totalCostFormatted = totalCost.toFixed(2); // Ensure two decimal places
                totalCostObj.val(totalCostFormatted); // Assuming invoiceAmount is an input field
            }
        });
        
        $("#editInvoicePortalForm .invoiceUnitPrice").livequery('change', function (e){
            var unitPrice = parseFloat($(this).val());
            var quantity = parseFloat($(this).closest('tr').find('.invoiceQuantity').val());
            var totalCostObj = $(this).closest('tr').find('.invoiceAmount');
            
            if (!isNaN(quantity) && !isNaN(unitPrice) && quantity >= 0 && unitPrice >= 0) {
                var totalCost = quantity * unitPrice;
                var totalCostFormatted = totalCost.toFixed(2); // Ensure two decimal places
                totalCostObj.val(totalCostFormatted); // Assuming invoiceAmount is an input field
            }
        });

        $("a.clearInvoiceItemEdit").livequery('click', function (e){
            e.preventDefault();
            e.stopImmediatePropagation();

            var row = $(this).closest('tr');
            var invoiceItemId = $(this).attr('data-invoice-item-id');
            $('<input>', {
                type: 'hidden',
                name: 'removed_invoice_item_ids[]',
                value: invoiceItemId
            }).appendTo('#editInvoicePortalForm');

            row.find('textarea[name^="title_"]').val('');
            row.find('textarea[name^="description_"]').val('');
            row.find('.invoiceUnit, .invoiceQuantity, .invoiceUnitPrice, .invoiceAmount').val('');
            row.remove();

            return false;
        });

        $("#editInvoicePortalForm a.addInvoiceItem").livequery('click', function (e){
            e.preventDefault();
            var row = $('<tr><td><textarea name="new_title[]"></textarea></td><td><textarea name="new_description[]"></textarea></td><td><input type="text" class="text invoiceUnit" name="new_unit[]"></td><td><input type="text" class="text invoiceQuantity" name="new_qty[]"></td><td><input type="text" class="text invoiceUnitPrice" name="new_unitprice[]"></td><td><input type="text" class="text invoiceAmount" name="new_amount[]"></td><td><select name="new_addminus[]"><option value="Add">Add</option><option value="Minus">Minus</option></select></td><td class="text"><a href="#" class="removeNewInvoiceItem"><u>Remove</u></a></td></tr>');
            $(this).closest('form').find('table.thinlist tr:last').after(row);
            return false;
        });

        $("#editInvoicePortalForm a.removeNewInvoiceItem").livequery('click', function (e){
            e.preventDefault();
            $(this).closest('tr').remove();
            return false;
        });
        

        $(".m-enggCrm_order a.generateInvoiceorder").livequery('click', function (e){
            var title = "Generate Invoice2";
            var order_id = $(this).attr('order_id');
            var record_type = $(this).attr('record_type');
            var url = 'index.php?module=enggCrm_invoice&_spAction=generateInvoiceForm'
                    + '&showHTML=0&order_id='+order_id+'&record_type='+record_type;
            var exp = {
                url: url,
                callbackOnSuccess: function(){
                    var msg = 'Invoice created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };
        
            var width = Math.min($(window).width() * 0.9, 700); // 90% of the viewport width or 600px max
            var height = Math.min($(window).height() * 0.9, 530); // 90% of the viewport height or 530px max
        
            Util.openFormInDialog.call(this, 'generateInvoiceForm', title, width, height, exp);
        });
        
        $('.m-enggCrm_order .editInvoicess').livequery('click', function (e){
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

       $('#cancelBill').livequery('click', function(e){
            msg = "Please note related receipt,\n\n invoice will also be cancelled,\n\n Do you like to Cancel?";
            var order_id = $(this).attr('order_id');
            if (!confirm(msg)){
                return false;
            }
            else {
                Util.showProgressInd();
                var url = 'index.php?module=enggCrm_order&_spAction=cancelBill&showHTML=0';
                $.get(url,{order_id: order_id}, function(html){
                    Util.hideProgressInd();
                    Util.alert('Order & Related Invoice, Receipt Cancelled Successfully!')
                    window.location.reload(true);
                });
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

            $('.total_Amount_Invoice').html(total_amount.toFixed(3));
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

            $('.total_Amount_Invoice').html(total_amount.toFixed(3));

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

            $('.total_Amount_Invoice').html(total_amount.toFixed(3));

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

            $('.total_Amount_Invoice').html(total_amount.toFixed(3));

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

            $('.total_Amount_Invoice').html(total_amount.toFixed(3));

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

$('.cancelInvoicess').livequery('click', function (e){
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

$('.cancelReceiptes').livequery('click', function (e){
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

$("a.newContactLink").livequery('click', function (e){
    //alert(urlNew);
    var company_id = $('select[name=company_id]').val();
    var url = $(this).attr('link');
    var urlNew = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0&company_id=' + company_id;

    $(this).attr('link', urlNew);
    

});

$("a.editCompanyLink").livequery('click', function (e){
    var company_id = $(this).attr('data-company-id');
    if (!company_id) {
        company_id = $('#fld_company_id').val();
    }

    if (!company_id || company_id == '0') {
        e.stopImmediatePropagation();
        $(this).attr('link', '#');
        $(this).attr('href', '#');
        e.preventDefault();
        alert('Please select a company first');
        return false;
    }

    cpm.enggCrm.order.editingCompanyName = $('input[name=company_name]').val();

    var url = 'index.php?module=enggCrm_order&_spAction=editCompanyName&showHTML=0&company_id=' + company_id;
    cpm.enggCrm.order.editingCompanyId = company_id;
    $(this).attr('href', url);
    $(this).attr('link', url);
});

$('select#fld_company_id, select[name=company_id], input[name=company_id], input#fld_company_id').on('change keyup blur', function() {
    var company_id = cpm.enggCrm.order.getSelectedCompanyId();
    var editCompanyUrl = 'index.php?module=enggCrm_order&_spAction=editCompanyName&showHTML=0';
    if (company_id && company_id != '0') {
        editCompanyUrl += '&company_id=' + company_id;
    } else {
        editCompanyUrl = '#';
    }
    $('a.editCompanyLink').attr('link', editCompanyUrl);

    cpm.enggCrm.order.refreshCompanyAddress(company_id);

    var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
    $.get(url, {company_id: company_id}, function (data) {
        $('#fld_contact_id').cp_loadSelect(data);
    }, 'json');

    var url = 'index.php?module=enggCrm_order&_spAction=projectByCompanyJSON&showHTML=0';
    $.get(url, {company_id: company_id}, function (data) {
        $('#fld_quote_id').cp_loadSelect(data);
    }, 'json');

    var url = $('a.newContactLink').attr('link');
    var urlNew = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0&company_id=' + company_id;
    $('a.newContactLink').attr('link', urlNew);
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
        var total_cost_formatted = (total_cost).toFixed(3);
        totalCostObj.html(total_cost_formatted);
        qtyBalanceObj.html((qtyBalance - quantity).toFixed(3));
    }
}

cpm.enggCrm.order.getSelectedCompanyId = function(){
    var company_id = '';
    var fieldSelectors = [
        '#fld_company_id',
        'select[name=company_id]',
        'input[name=company_id]',
        'input#fld_company_id'
    ];

    $.each(fieldSelectors, function(i, selector){
        if (!company_id && $(selector).length) {
            company_id = $.trim($(selector).val() || '');
        }
    });

    return company_id;
};

cpm.enggCrm.order.refreshCompanyAddress = function(company_id){
    var setAddressValue = function(selector, value){
        var target = $(selector);
        if (!target.length) return;

        if (target.is('input, textarea, select')) {
            target.val(value || '');
            return;
        }

        var valueNode = target.find('.value').first();
        if (valueNode.length) {
            valueNode.text(value || '');
        } else {
            target.text(value || '');
        }
    };

    if (!company_id || company_id == '0') {
        setAddressValue('#fld_cust_address1', '');
        setAddressValue('#fld_cust_address2', '');
        setAddressValue('#fld_cust_address_country', '');
        setAddressValue('#fld_cust_address_po_code', '');
        setAddressValue('input[name=cust_address1]', '');
        setAddressValue('input[name=cust_address2]', '');
        setAddressValue('input[name=cust_address_country]', '');
        setAddressValue('input[name=cust_address_po_code]', '');
        return;
    }

    var url = 'index.php?module=enggCrm_order&_spAction=companyDetailsJSON&showHTML=0';
    $.get(url, {company_id: company_id}, function (data) {
        if (!data) return;
        setAddressValue('#fld_cust_address1', data.cust_address1 || '');
        setAddressValue('#fld_cust_address2', data.cust_address2 || '');
        setAddressValue('#fld_cust_address_country', data.cust_address_country || '');
        setAddressValue('#fld_cust_address_po_code', data.cust_address_po_code || '');
        setAddressValue('input[name=cust_address1]', data.cust_address1 || '');
        setAddressValue('input[name=cust_address2]', data.cust_address2 || '');
        setAddressValue('input[name=cust_address_country]', data.cust_address_country || '');
        setAddressValue('input[name=cust_address_po_code]', data.cust_address_po_code || '');
    }, 'json');
};

cpm.enggCrm.order.loadContactsByCompany = function(){
    var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
    var company_id = cpm.enggCrm.order.getSelectedCompanyId();
    $.get(url, {company_id: company_id}, function (data) {
        $('#fld_contact_id').cp_loadSelect(data);
    }, 'json');
    cpm.enggCrm.order.refreshCompanyAddress(company_id);
}

cpm.enggCrm.order.loadCompany = function(callback){
    var url = 'index.php?module=enggCrm_opportunity&_spAction=newCompanyJSON&showHTML=0';
    $.get(url, function (data) {
        $('#fld_company_id').cp_loadSelect(data);
        if (callback) {
            callback();
        }
    }, 'json');
}

cpm.enggCrm.order.afterNewCompany = function(data, statusText, jqFormObj){
    var company_id = '';
    var company_name = '';
    if (jqFormObj) {
        company_name = $.trim($(jqFormObj).find('[name=company_name]').val() || '');
    }
    if (data && data.returnUrl) {
        var companyIdMatch = data.returnUrl.match(/[?&](?:record_id|currentId)=([^&]+)/);
        if (companyIdMatch) {
            company_id = companyIdMatch[1];
        }
    }
    if (!company_id && data && data.extraParam && data.extraParam.record_id) {
        company_id = data.extraParam.record_id;
    }
    Util.closeAllDialogs();
    cpm.enggCrm.order.loadCompany(function(){
        if (company_id) {
            $('#fld_company_id').val(company_id).trigger('change');
        } else if (company_name) {
            $('#fld_company_id option').filter(function(){
                return $.trim($(this).text()) == company_name;
            }).prop('selected', true).parent().trigger('change');
        }
    });
    var mgsalert = 'New company successfully created!';
    var n = noty({
        text: mgsalert,
        type: 'confirm',
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'defaultTheme',
        timeout: 5000,
    });
}

cpm.enggCrm.order.refreshCompanyNameDisplay = function(companyId, companyName){
    var nameToSet = $.trim(companyName || '');
    if (!nameToSet && companyId) {
        var selected = $('select[name=company_id], #fld_company_id').find('option[value=' + companyId + ']');
        if (selected.length) {
            nameToSet = $.trim(selected.text());
        }
    }

    if (!nameToSet && $('form#portalForm input[name=company_name]').length) {
        nameToSet = $.trim($('form#portalForm input[name=company_name]').val() || '');
    }

    if (nameToSet) {
        $('input[name=company_name], input#fld_company_name').val(nameToSet);
        $('#fld_company_name .value, .row_company_name .value').text(nameToSet);
        $('div#fld_company_name .value').text(nameToSet);
    }
};

cpm.enggCrm.order.afterEditCompany = function(data){
    var company_id = cpm.enggCrm.order.editingCompanyId;
    var updatedCompanyName = cpm.enggCrm.order.editingCompanyName || '';

    if ($('form#portalForm input[name=company_name]').length) {
        updatedCompanyName = $.trim($('form#portalForm input[name=company_name]').val() || updatedCompanyName);
    }

    if (data && data.company_name) {
        updatedCompanyName = data.company_name;
    }

    Util.closeAllDialogs();

    if ($('#fld_company_id').length) {
        cpm.enggCrm.order.loadCompany(function(){
            $('#fld_company_id').val(company_id).trigger('change');
            cpm.enggCrm.order.refreshCompanyNameDisplay(company_id, updatedCompanyName);
        });
    } else {
        cpm.enggCrm.order.refreshCompanyNameDisplay(company_id, updatedCompanyName);
    }

    var mgsalert = 'Company updated successfully!';
    var n = noty({
        text: mgsalert,
        type: 'confirm',
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'defaultTheme',
        timeout: 5000,
    });
}

cpm.enggCrm.order.afterNewContact = function(){
    Util.closeAllDialogs();
    cpm.enggCrm.order.loadContactsByCompany();
    var mgsalert = 'New contact successfully created!';
    var n = noty({
        text: mgsalert,
        type: 'confirm',
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'defaultTheme',
        timeout: 5000,
    });
}

cpm.enggCrm.order.triggerCalcForAmount = function(){
    var amount = $(this).val();
    var quantityObj = $(this).closest('tr').find('.invoiceItemQuantity');
    var quantity = quantityObj.val();
    var totalCostObj = $(this).closest('tr').find('.totalCost');

    if (quantity > 0 && amount > 0) {
        var total_cost = quantity * amount;
        var total_cost_formatted = (total_cost).toFixed(3);
        totalCostObj.html(total_cost_formatted);
    } else if (amount > 0 && quantity == '') {
        var total_cost_formatted = amount;
        totalCostObj.html(total_cost_formatted);
    }
}






$('.editCreditNote').livequery('click', function (e){
    var title = "Edit Credit Note";
    e.preventDefault();
    e.stopImmediatePropagation();

    if ($(this).data('creditNoteEditOpening')) {
        return;
    }
    $(this).data('creditNoteEditOpening', true);

    $('div.popcontents').has('form#editCreditNotePortalForm').each(function(){
        var dialog = $(this);
        if (dialog.hasClass('ui-dialog-content')) {
            dialog.dialog('destroy');
        }
        dialog.remove();
    });

    var expObj = {
        validate: true
       ,onOpenFn: function(){
            var dialogs = $('div.popcontents').has('form#editCreditNotePortalForm');
            dialogs.slice(0, -1).each(function(){
                var dialog = $(this);
                if (dialog.hasClass('ui-dialog-content')) {
                    dialog.dialog('destroy');
                }
                dialog.remove();
            });
        }
       ,callbackOnSuccess: function(){
            var msg = 'Credit Note updated successfully';
            var refreshTimer = setTimeout(function(){
                window.location.assign(window.location.href);
            }, 1500);
            Util.alert(msg, function(){
                clearTimeout(refreshTimer);
                window.location.assign(window.location.href);
            });
        }
    }
    Util.openFormInDialog.call(this, 'editCreditNotePortalForm', title, 1200, 500, expObj);
});
