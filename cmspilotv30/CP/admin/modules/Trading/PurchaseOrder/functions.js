Util.createCPObject('cpm.trading.purchaseOrder');

cpm.trading.purchaseOrder = {
    init: function(){
        $('.m-trading_purchaseOrder #actBtn_apply, .m-trading_purchaseOrder #actBtn_save')
        .click(cpm.trading.purchaseOrder.validateCreateInventory);

        //for edit portal product
        $('#fld_quantity, #buy_unit_price')
        .live('change', cpm.trading.purchaseOrder.calculateValues);

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
                $('#btnUpdateInventory').click(cpm.trading.purchaseOrder.saveInventoryForm);
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
    }

}