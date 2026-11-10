Util.createCPObject('cpm.hms.order');

cpm.hms.order = {
    init: function(){
        $('.click-all-top .check-all').livequery('click', function(e){
            e.preventDefault();
            cpm.hms.order.checkAllCol.call(this);
        });

        $('.click-all-top .uncheck-all').livequery('click', function(e){
            e.preventDefault();
            cpm.hms.order.uncheckAllCol.call(this);
        });

        $('.m-hms_order input[name=cst]').livequery('click', function (e){
            cst = $(this).val();
            if(cst == 1){
                $('.m-hms_order .cst_value').show();
                $('.m-hms_order .cstValueNew').show();
            } else {
                $('.m-hms_order .cst_value').hide();
                $('.m-hms_order .cstValueNew').hide();
            }
        });

        $('.m-hms_order input[name=vat]').livequery('click', function (e){
            vat = $(this).val();
            if(vat == 1){
                $('.m-hms_order .vat_value').show();
                $('.m-hms_order .vatValueNew').show();
            } else {
                $('.m-hms_order .vat_value').hide();
                $('.m-hms_order .vatValueNew').hide();
            }
        });

        $('.vat_code_generate').livequery('click', function (e){
            var url = 'index.php?_topRm=finance&module=hms_order&_spAction=invoiceCodeVatUpdate&showHTML=0';
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

        $('.m-hms_order .actionBtnsDetail #generateInvoice').livequery('click', function (e){
            var title = "Bill Generation";
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
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 600, expObj);
        });

        $(".order_item_type_value").live("keyup", function() {
            var totalAmount = 0;
            var total_count = $('#total_count').val();
            var inputval = $(this).val();
            if(inputval != ''){
                for ( var i = 1; i<=total_count; i++ ){
                    var inputval = $('.list_fees_'+i).val();
                    if(inputval == undefined){
                       inputval = parseInt(0);
                    }
                    totalAmount += Number(inputval);
                }

                $(".invoice_sub_total_amount").val(totalAmount.toFixed(2));

                var discount = $(".invoice_discount_amount").val();
                if(inputval != ''){
                    totalAmount = totalAmount - discount;
                }

                $(".invoice_total_amount").val(totalAmount.toFixed(2));

            }
        });

        $(".invoice_discount_amount").live("keyup", function() {
            var totalAmount = 0;
            var total_count = $('#total_count').val();
            var discount = $(this).val();
            if(discount != ''){
                for ( var i = 1; i<=total_count; i++ ){
                    var inputval = $('.list_fees_'+i).val();
                    if(inputval == undefined){
                       inputval = parseInt(0);
                    }
                    totalAmount += Number(inputval);
                }

                $(".invoice_sub_total_amount").val(totalAmount.toFixed(2));

                totalAmount = totalAmount - discount;
                $(".invoice_total_amount").val(totalAmount.toFixed(2));

            }
        });

        $('.m-hms_order #generateSalesReturn').livequery('click', function (e){
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

        $('.m-hms_order #editInvoice').livequery('click', function (e){
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

        $('.m-hms_order #editReceipt').livequery('click', function (e){
            var title = "Edit Receipt";
            e.preventDefault();

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

        $('.m-hms_order .actionBtnsDetail #generateReceipt').livequery('click', function (e){
            var title = "Create Receipt";
            e.preventDefault();
            var order_id = $('#record_id').val();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.order.reloadReceiptPortal(order_id);
                        cpm.hms.order.reloadInvoicePortal(order_id);
                        //window.location.reload(true);
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
            var cbObj1 = $('input.invoiceItemId', parent);
            var checked1 = cbObj1.is(":checked") ? true : false;
            var qty = (qty != '') ? parseInt(qty) : parseInt(0);

            if((qty == 0 && checked) || (qty == 0 && checked1)){
                Util.alert('Please enter the qty')
            } else if(qty > qtyBalance && checked){
                Util.alert('The qty should not be more than than the balance qty')
            } else {
                cpm.hms.order.updateInvoiceAmount();
            }

            Util.hideProgressInd();
        });

        $('.m-hms_order input.invoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?_topRm=finance&module=hms_order&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
                $('input[id=fld_amount]').val(html);
                Util.hideProgressInd();
            });
        });

        $('.cancelInvoice').live('click', function (e){
            var invoice_status = $(this).attr('invoice_status');
            var order_id = $('#record_id').val();

            if (invoice_status != 'Paid') {
                msg = "Do you like to cancel the Invoice?";
                if (!confirm(msg)){
                    return false;
                }
                else {
                    var url = 'index.php?_topRm=finance&module=hms_order&_spAction=cancelInvoice&showHTML=0';
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
                            cpm.hms.order.reloadInvoicePortal(order_id);
                            //window.location.reload(true);
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

        $('.cancelReceipt').live('click', function (e){
            msg = "Do you like to cancel the Receipt?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=finance&module=hms_order&_spAction=cancelReceipt&showHTML=0';
                Util.showProgressInd();
                var receipt_code = $(this).attr('receipt_code');
                var order_id     = $(this).attr('order_id');
                $.get(url,{receipt_code: receipt_code, order_id:order_id}, function(html){
                    alert ('Receipt Cancelled Succesfully');
                    Util.hideProgressInd();
                    cpm.hms.order.reloadReceiptPortal(order_id);
                    cpm.hms.order.reloadInvoicePortal(order_id);
                    //window.location.reload(true);
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
            var url = 'index.php?module=hms_order&_spAction=generateFullInvoice&showHTML=0';

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
            cpm.hms.order.updateInvoiceAmount();
        });
    },

    checkAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-order-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
            checkbox.attr('checked', 'checked');
        });
        cpm.hms.order.updateInvoiceAmount();
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
         /*   var url = 'index.php?_topRm=finance&module=hms_order&_spAction=populateInvoiceAmount&showHTML=0';
            $.get(url,{order_item_id: order_item_id ,checkedVal: checkedVal, qty: qty}, function(html){
                $('.invoiceForm input[id=fld_invoice_amount]').val(html);
                Util.hideProgressInd();
            });
        });*/
    },

    reloadInvoicePortal: function(order_id){
        var url = 'index.php?module=hms_order&_spAction=InvoicePortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#orderInvoicePortal').html(html);

            var invoice_count    = $('#fld_invoice_count').val();
            if(invoice_count == 0){
                var url = "index.php?module=hms_order&_spAction=generateInvoiceForm&order_id="+order_id+"&showHTML=0";
                $('#generateReceipt').after("<a href='"+url+"' id='generateInvoice'>CREATE DETAIL INVOICE</a>");
                $('.orderRightpanelButtons').append("<div class='float_right button mb5'>"
                +"<a id='generateFullInvoice' order_id="+order_id+">CREATE INVOICE</a></div>");
                $('#generateReceipt').remove();
            }

            Util.hideProgressInd();
        });
    },

    reloadReceiptPortal: function(order_id){
        var url = 'index.php?module=hms_order&_spAction=ReceiptPortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#orderReceiptPortal').html(html);
            Util.hideProgressInd();
        });
    },
}
