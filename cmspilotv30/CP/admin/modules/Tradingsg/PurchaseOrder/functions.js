Util.createCPObject('cpm.tradingsg.purchaseOrder');

cpm.tradingsg.purchaseOrder = {
    init: function(){
        $('.m-trading_purchaseOrder #actBtn_apply, .m-trading_purchaseOrder #actBtn_save')
        .click(cpm.tradingsg.purchaseOrder.validateCreateInventory);

        //for edit portal product
        $('#fld_quantity, #buy_unit_price')
        .live('change', cpm.tradingsg.purchaseOrder.calculateValues);

    },

    calculateValues: function(){
        var url = "index.php?_topRm=main&module=trading_product"
                + "&_spAction=calculatedValuesPoItems"
                + "&showHTML=0";
        var values = $("#portalForm").serialize();

        $.post(url, values, function(json) {
            $('#buy_unit_price_base').val(json.buy_unit_price_base);

            $('#t_buy_price').html(json.buy_price);
            $('#t_buy_unit_price_base').html(json.buy_unit_price_base);
            $('#t_buy_price_base').html(json.buy_price_base);

        }, 'json');
    },

    validateCreateInventory: function(){
        var fld_status = $('#fld_status').val();
        var fld_status_prev = $('#fld_status_prev').val();
        if (fld_status != fld_status_prev && fld_status == 'confirmed') {
            var msg = "Changing status to confirmed will create Inventory records.\n" +
                      "Are you sure to continue?";
            if (!confirm(msg)){
                return false;
            }
        }

    },

    editInventoryForm: function(purchase_order_items_id) {
        var url = 'index.php?module=trading_purchaseOrder&_spAction=editInventoryForm' +
                  '&purchase_order_items_id=' + purchase_order_items_id +
                  '&showHTML=0';
        var exp = {
            url: url
           ,afterOpen: function() {
                $('#btnUpdateInventoryCancel').click(function() {
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                });
                $('#btnUpdateInventory').click(cpm.tradingsg.purchaseOrder.saveInventoryForm);
                $('#location_common').change(function() {
                    $('#updateInventory .location').val($(this).val());
                });
                $('#status_common').change(function() {
                    $('#updateInventory .status').val($(this).val());
                });
            }
        };
        Util.openDialogForLink('Edit Inventory Status',  900, 500, 0, exp);
    },

    saveInventoryForm: function() {
        var url = "index.php?module=trading_purchaseOrder"
                + "&_spAction=saveInventory"
                + "&showHTML=0";
        var values = $('#updateInventory input, #updateInventory select').serialize();

        $.post(url, values, function(json) {
            Util.alert(json.html, function() {
                $('#dialog').dialog('destroy');
                $('#dialog').remove();
            });
        }, 'json');
    },

    printPO: function() {
        var purchase_order_id = $('#record_id').val();
        var url = 'index.php?_spAction=printReport&record_id=' +
                   purchase_order_id + '&showHTML=0&roomName=trading_purchaseOrder&report=purchaseOrder';
        document.location = url;
    },

    validateEditProductItemLink_xxx: function(exp) {
        var retValue = true;
        var purchase_order_items_id = exp.recId;
        var url = 'index.php?module=trading_purchaseOrder'
                + '&_spAction=validateEditProductItemLink&showHTML=0';
        $.ajax({
            url: url,
            async: false,
            data: {purchase_order_items_id: purchase_order_items_id},
            dataType: 'json',
            success: function (json) {
                if (json.status == 'error') {
                    Util.alert(json.errorMsg);
                    retValue = false;
                }
            }
        });
        return retValue;
    },

    updateInvoiceAmount: function(){
        var amount = parseInt(0);
        $('.room-po-table tbody tr input[type=checkbox]:checked').each(function(){
            var parent = $(this).closest('tr');
            var valueObj = $('td.sellingPrice', parent);
            if(valueObj.text() != ''){
                var qtyObj = $(this).parents('tr').find('input[id=fld_qty]');
                var qty = (qtyObj.val() != '') ? parseInt(qtyObj.val()) : parseInt(0);

                amount += parseInt(valueObj.text()) * qty;
            }
        });
        $('.invoiceForm #fld_invoice_amount').html(amount);
    }
}

$('.room-po-table input.poProductId, .room-po-table input.invoiceItemId, .room-po-table tbody tr input[id=fld_qty]').livequery('change', function (e){
    Util.showProgressInd();

    var parent = $(this).closest('tr');
    var qtyBalance = $('td.qtyBalance', parent).text();
    var qty = $('input[id=fld_qty]', parent).val();
    var cbObj = $('input.poProductId', parent);
    var checked = cbObj.is(":checked") ? true : false;
    var qty = (qty != '') ? parseInt(qty) : parseInt(0);

    if(qty == 0 && checked){
        Util.alert('Please enter the qty')
    } else if(qty > qtyBalance && checked){
        Util.alert('The qty should not be more than than the balance qty')
    } else {
        cpm.tradingsg.purchaseOrder.updateInvoiceAmount();
    }

    Util.hideProgressInd();
});

/*$('.m-tradingsg_purchaseOrder input.poProductId').livequery('click', function (e){
    Util.showProgressInd();
    po_product_id = $(this).val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;

    var url = 'index.php?_topRm=finance&module=tradingsg_purchaseOrder&_spAction=populateReceiptAmount&showHTML=0';
    $.get(url,{po_product_id: po_product_id ,checkedVal: checkedVal}, function(html){
        $('.invoiceForm #fld_invoice_amount').val(html);
        Util.hideProgressInd();
    });
});*/

$('.m-tradingsg_purchaseOrder #raiseInvoice').livequery('click', function (e){
    msg = "Do you like to Raise Invoice?";
    if (!confirm(msg)){
        return false;
    }
    else{
        var title = "Raise Invoice";
        e.preventDefault();

        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Invoice raised successfully';
                Util.closeAllDialogs();
                window.location.reload(true);
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
    }
});

$('.m-tradingsg_purchaseOrder #editInvoice').livequery('click', function (e){
    msg = "Do you like to Edit Invoice?";
    if (!confirm(msg)){
        return false;
    }
    else{
        var title = "Edit Invoice";
        e.preventDefault();

        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Invoice updated successfully';
                Util.closeAllDialogs();
                window.location.reload(true);
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
    }
});
