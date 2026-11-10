Util.createCPObject('cpm.trading.inventory');

cpm.trading.inventory = {
    init: function() {
        $('a.status').click(cpm.trading.inventory.changeStatus);
        $('#fld_on_sale').change(cpm.trading.inventory.calculateSalePrice);
        $('input[name=on_sale]').click(cpm.trading.inventory.updateSalePriceFromList);
    },
    
    changeStatus: function(e) {
        e.preventDefault();
        var title = $(this).attr('dialogTitle');

        var linkObj = $(this);

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(json){
                linkObj.html(json.returnText);
                Util.closeAllDialogs();
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 450, 325, expObj);
    },
    
    calculateSalePrice: function() {
        var url = 'index.php?module=trading_inventory&_spAction=calculateSalePrice&showHTML=0';
        var inventory_id = $('#record_id').val();
        var on_sale = $(this).val();
        if (on_sale) {
            $.getJSON(url, {inventory_id: inventory_id}, function (json) {
                $('#fld_retail_unit_price_discount').val(json.sale_price);
            });
        } else {
            $('#fld_retail_unit_price_discount').val('');
        }
    },
    
    updateSalePriceFromList: function() {
        var url = 'index.php?module=trading_inventory&_spAction=updateSalePriceFromList&showHTML=0';
        var inventory_id = $(this).attr('inventory_id');
        var on_sale = $(this).is(':checked') ? 1 : 0;
        
        var onSaleObj = $(this);

        $.getJSON(url, {inventory_id: inventory_id, on_sale: on_sale}, 
        function (json) {
            onSaleObj.parents('td').siblings('td.price_discount').html(json.sale_price);
        });
    }


}

