Util.createCPObject('cpm.tradingsg.stockTransfer');

cpm.tradingsg.stockTransfer = {
    init: function(){
        $(".addProduct input[name='product_title']")
        .livequery(cpm.tradingsg.stockTransfer.posProductTitle);
        
        $("#orderItems input[name='qty']").livequery('change', function(){
            var qty = $(this).val();
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var stock_transfer_history_id = $(this).attr('stock_transfer_history_id');
            var stock =parseInt($(this).attr('stock'), 10);
            //alert(stock);
            if(stock < qty){
                Util.alert('The qty should be less than the stock qty');
                cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
            } else {
                var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateQtyOrderItem&showHTML=0';
                $.get(url, {qty: qty, stock_transfer_history_id: stock_transfer_history_id, stock_transfer_id: stock_transfer_id}, function(html){
                  cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id); 
                });
            }
        });

        $('.deleteItem').livequery('click', function (){
            var url = 'index.php?module=tradingsg_stockTransfer&_spAction=deleteItem&showHTML=0';
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var stock_transfer_history_id = $(this).attr('stock_transfer_history_id');
            $.get(url,  {stock_transfer_history_id:stock_transfer_history_id, stock_transfer_id: stock_transfer_id}, function(html){
                cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
            });
        });
    },

    posProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=tradingsg_stockTransfer&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 2
            ,selectFirst: true
            ,autoFocus: true
            ,focus: function(event, ui) {
                var len = $('.ui-autocomplete > li').length;
                if(len === 1){
                    var selectedObj = ui.item;
        			var product_id = selectedObj.id
                    var stock_transfer_id = $(this).attr('stock_transfer_id');
        			$(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");

                    //--------------------------------------------
                    Util.showProgressInd();
    	           	var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateOrderLineItems&showHTML=0';
                    $.get(url, {product_id: product_id, stock_transfer_id: stock_transfer_id}, function(json){
    	                cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
    	                $(".addProduct input[name='product_title']").val('');
                        Util.hideProgressInd();
                    });
                    $(titleObj).autocomplete( "close" );
                }
            }
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var product_id = selectedObj.id
                var stock_transfer_id = $(this).attr('stock_transfer_id');
                //alert (product_id);
                $(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");

                //--------------------------------------------
                Util.showProgressInd();
                var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateOrderLineItems&showHTML=0';
                $.get(url, {product_id: product_id,stock_transfer_id: stock_transfer_id}, function(json){
                    cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
                    if (json.msg =='Please note the product is already added') {
                        Util.hideProgressInd();
                        Util.alert(json.msg);
                        $('input[name=product_title]').val('');
                        return;
                    }
                    $(".addProduct input[name='product_title']").val('');
                    Util.hideProgressInd();
                });
    		}
    	});
    },

    reloadOrderItems: function(stock_transfer_id){
        var url = 'index.php?module=tradingsg_stockTransfer&_spAction=orderItems&showHTML=0';
        $.get(url, {stock_transfer_id: stock_transfer_id}, function(html){
            $('#orderItems').html(html);
            Util.hideProgressInd();
        });
    }

}
