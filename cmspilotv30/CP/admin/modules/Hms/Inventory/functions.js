Util.createCPObject('cpm.hms.inventory');

cpm.hms.inventory = {
	init: function(){
		/* Update Product and Stock */
        $('.UpdateInventoryRecords').live('click', function (e){
            var url = 'index.php?module=hms_inventory&_spAction=UpdateStockProductsRecords'
                    + '&showHTML=0';
            Util.showProgressInd();
            $.get(url, function(html){
            	Util.hideProgressInd();
            	window.location.reload(true);
            });
        });
    }
}