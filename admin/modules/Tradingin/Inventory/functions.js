Util.createCPObject('cpm.tradingin.inventory');

cpm.tradingin.inventory = {
    init: function(){

        $('.viewAllUpdatedAdjustStockHistory').livequery('click', function (){
            Util.showProgressInd();
            var inventory_id = $(this).attr('inventory_id');

            if(inventory_id != "") {
                var url = "index.php?_topRm=inventory&module=tradingin_inventory&_spAction=updatedAdjustStockHistory&inventory_id="+inventory_id+"&showHTML=0";
                var exp = {
                    url: url
                };

                Util.openDialogForLink('Adjust Stock History', 550, 300, 0, exp);
            } else {
                Util.hideProgressInd();
                Util.alert("There is no history records found!");
            }
        });

        $('.batchStockList').livequery('click', function (e){
            var parent = $(this).closest('tr');
            $('.batchStockList', parent).hide();
            $('.currentStockSaveDisplay', parent).show();
            $('.currentStockEdit', parent).show();
        });

        $('.overallStockForList .saveCurrentStock').livequery('click', function(){
            var parent = $(this).closest('tr');
            $('.batchStockList', parent).show();
            $('.currentStockSaveDisplay', parent).hide();
            $('.currentStockEdit', parent).hide();
            var mgsalert = 'Stock Updated Successfully!';
            var n = noty({
                text: mgsalert,
                type: 'confirm',
                dismissQueue: true,
                layout: 'topCenter',
                theme: 'defaultTheme',
                timeout: 5000,
            });
        });

        $('.overallStockForList input[name=current_stock]').livequery('change', function(){
            var current_stock = $(this).val();
            var parent        = $(this).closest('tr');
            var product_id    = $('.overallStockForList .saveCurrentStock', parent).attr('product_id');
            var inventory_id  = $('.overallStockForList .saveCurrentStock', parent).attr('inventory_id');

            if(current_stock == "") {
                current_stock = 0;
            }
            Util.showProgressInd();
            var url = 'index.php?module=tradingin_inventory&_spAction=updateCurrentStockInventoryBatchRecordList&showHTML=0';
            $.get(url, {product_id: product_id, inventory_id:inventory_id, current_stock:current_stock}, function(html){
                $("span.stockUpdateList_"+inventory_id).html(parseInt(html));
                Util.hideProgressInd();
            });
        });

        /*$('.m-tradingin_inventory.v-list .ui-dialog .ui-icon-closethick').livequery('click', function (e){
            window.location.reload(true);
        });*/
    },

    reloadManualStock: function(product_id, site_id){
        var url = 'index.php?module=tradingin_inventory&_spAction=manualStockDisplayDetail&showHTML=0';
        $.get(url, {product_id: product_id, site_id:site_id}, function(html){
            $('#manualStockDetail').html(html);
            $("input[name='manual_stock']").val('');
            Util.hideProgressInd();
        });
    },
}