Util.createCPObject('cpm.tradingsg.batchImport');

cpm.tradingsg.batchImport = {
    init: function(){
        $(".m-tradingsg_batchImport select[name='product_id']").change(function(){
    			var product_id = $("select[name='product_id']").val();
    			//alert (product_id);
                Util.showProgressInd();

                var parent          = $(this).closest('tr');
                var supplierIdObj     = $(this ).parents('tr').find('.company-id select[name=company_id]');


                var url = $('#scopeRootAlias').val() + 'index.php?module=tradingsg_batchImport&_spAction=supplierJsonByProductId&showHTML=0';

                $.getJSON(url, {product_id: product_id}, function(data) {
                    supplierIdObj.cp_loadSelect(data);
                });

                Util.hideProgressInd();
		});

        $('.row-tradingsg_batchImport__tradingsg_batchHistoryLink .qty, .row-tradingsg_batchImport__tradingsg_batchHistoryLink .price').livequery('change', function(){
            var parent = $(this).closest('tr');
            var qtyObj = $(this).parents('tr').find('input[name=qty]');
            var qty = qtyObj.val();
            var priceObj = $(this).parents('tr').find('input[name=price]');
            var price = priceObj.val();
            var rec_id = $(parent).attr('recid');
            var costPriceObj = $(this ).closest('tr').find('.total-cost-price');
            var url = 'index.php?module=tradingsg_batchImport&_spAction=updateTotalCostPrice&showHTML=0';
            
            $.get(url, {rec_id: rec_id, qty: qty, price: price}, function(json){
                costPriceObj.html(json.cost_price);
                $('tr.summary-row .totalCp').html(json.total_cost_price_sum);  
            });
            
        });

	}
}