Util.createCPObject('cpm.tradingsg.order');

cpm.tradingsg.order = {
    init: function(){
        $('.click-all-top .check-all').livequery('click', function(e){
            e.preventDefault();
            cpm.tradingsg.order.checkAllCol.call(this);
        });

        $('.click-all-top .uncheck-all').livequery('click', function(e){
            e.preventDefault();
            cpm.tradingsg.order.uncheckAllCol.call(this);
        });

        $('.m-tradingsg_order .actionBtnsDetail #generateInvoice').livequery('click', function (e){
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

        $('.m-tradingsg_order #editInvoice').livequery('click', function (e){
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

        $('.m-tradingsg_order .actionBtnsDetail #generateReceipt').livequery('click', function (e){
            var title = "Create Receipt";
            e.preventDefault();

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

        $('.room-order-table input.orderItemId, .room-order-table input.invoiceItemId, .room-order-table tbody tr input[id=fld_qty]').livequery('change', function (e){
            Util.showProgressInd();

            var parent = $(this).closest('tr');
            var qtyBalance = $('td.qtyBalance', parent).text();
            var qty = $('input[id=fld_qty]', parent).val();
            var cbObj = $('input.orderItemId', parent);
            var checked = cbObj.is(":checked") ? true : false;
            var qty = (qty != '') ? parseInt(qty) : parseInt(0);

            if(qty == 0 && checked){
                Util.alert('Please enter the qty')
            } else if(qty > qtyBalance && checked){
                Util.alert('The qty should not be more than the balance qty')
            } else {
                cpm.tradingsg.order.updateInvoiceAmount();
            }

            Util.hideProgressInd();
        });

        $('.m-tradingsg_order input.invoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?_topRm=finance&module=tradingsg_order&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
                $('input[id=fld_amount]').val(html);
                Util.hideProgressInd();
            });
        });

        $('.cancelInvoice').livequery('click', function (e){
            var invoice_status = $(this).attr('invoice_status');

            if (invoice_status != 'Paid') {
                msg = "Do you like to cancel the Invoice?";
                if (!confirm(msg)){
                    return false;
                }
                else {
                    var url = 'index.php?_topRm=finance&module=tradingsg_order&_spAction=cancelInvoice&showHTML=0';
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
                var url = 'index.php?_topRm=finance&module=tradingsg_order&_spAction=cancelReceipt&showHTML=0';
                Util.showProgressInd();
                var receipt_code = $(this).attr('receipt_code');
                $.get(url,{receipt_code: receipt_code}, function(html){

                    /* Checking for one or more receipt for the invoice */
                    if (html == 'Cannot cancel') {
                        alert ('Cancel the related receipts and then proceed canceling the invoice');
                        Util.hideProgressInd();
                    } else {
                        alert ('Receipt Cancelled Succesfully');
                        Util.hideProgressInd();
                        window.location.reload(true);
                    }
                });
            }
        });
    },

    checkAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-order-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
            checkbox.attr('checked', 'checked');
        });
        cpm.tradingsg.order.updateInvoiceAmount();
    },

    uncheckAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-order-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
            checkbox.removeAttr('checked');
        });
        $('.invoiceForm input[id=fld_invoice_amount]').val(0);
    },

    updateInvoiceAmount: function(){
        var amount = parseInt(0);
        $('.room-order-table tbody tr input[type=checkbox]:checked').each(function(){
            var parent = $(this).closest('tr');
            var valueObj = $('td.sellingPrice', parent);
            if(valueObj.text() != ''){
                var qtyObj = $(this).parents('tr').find('input[id=fld_qty]');
                var qty = (qtyObj.val() != '') ? parseInt(qtyObj.val()) : parseInt(0);

                amount += parseInt(valueObj.text()) * qty;
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
         /*   var url = 'index.php?_topRm=finance&module=tradingsg_order&_spAction=populateInvoiceAmount&showHTML=0';
            $.get(url,{order_item_id: order_item_id ,checkedVal: checkedVal, qty: qty}, function(html){
                $('.invoiceForm input[id=fld_invoice_amount]').val(html);
                Util.hideProgressInd();
            });
        });*/
    }
}


