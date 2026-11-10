Util.createCPObject('cpm.hms.stockTransfer');

cpm.hms.stockTransfer = {
    init: function(){
        $(".addProduct input[name='product_title']")
        .livequery(cpm.hms.stockTransfer.posProductTitle);
        
        $("#orderItems input[name='qty']").live('change', function(){
            var qty = $(this).val();
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var stock_transfer_history_id = $(this).attr('stock_transfer_history_id');
            var request_qty = $(this).parents('tr').find("input[name='request_qty']");
            var request_qty = parseInt(request_qty.val(), 10);
            var stock = parseInt($(this).attr('stock'), 10);
            
            if(stock < qty){
                Util.alert('The qty should be less than the stock qty');
                cpm.hms.stockTransfer.reloadOrderItems(stock_transfer_id);
            } else {
                if(request_qty < qty){
                    Util.alert('The qty should be less than the request qty');
                    cpm.hms.stockTransfer.reloadOrderItems(stock_transfer_id);
                }
                else{
                    var url = 'index.php?module=hms_stockTransfer&_spAction=updateQtyOrderItem&showHTML=0';
                    $.get(url, {qty: qty, stock_transfer_history_id: stock_transfer_history_id, stock_transfer_id: stock_transfer_id}, function(html){
                      cpm.hms.stockTransfer.reloadOrderItems(stock_transfer_id); 
                    });
                }
            }
        });

        $("#orderItems input[name='request_qty']").live('change', function(){
            var request_qty = $(this).val();
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var stock_transfer_history_id = $(this).attr('stock_transfer_history_id');
            var stock =parseInt($(this).attr('stock'), 10);
            if(stock < request_qty){
                Util.alert('The qty should be less than the stock qty');
                cpm.hms.stockTransfer.reloadOrderItems(stock_transfer_id);
            } else {
                var url = 'index.php?module=hms_stockTransfer&_spAction=updateRequestQtyOrderItem&showHTML=0';
                $.get(url, {request_qty: request_qty, stock_transfer_history_id: stock_transfer_history_id, stock_transfer_id: stock_transfer_id}, function(html){
                  cpm.hms.stockTransfer.reloadOrderItems(stock_transfer_id); 
                });
            }
        });

        $('.deleteItem').livequery('click', function (){
            var url = 'index.php?module=hms_stockTransfer&_spAction=deleteItem&showHTML=0';
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var stock_transfer_history_id = $(this).attr('stock_transfer_history_id');

            var msg = "Are you sure to delete this item?";
            if (confirm(msg)){
                Util.showProgressInd();
                $.get(url,  {stock_transfer_history_id:stock_transfer_history_id, stock_transfer_id: stock_transfer_id}, function(html){
                    cpm.hms.stockTransfer.reloadOrderItems(stock_transfer_id);
                });
            }
        });

        $('.completeTransaction').livequery('click', function (){
            var stock_transfer_id     = $(this).attr('stock_transfer_id');
            var site_id               = $(this).attr('site_id');
            var stockTransfer_product = $('.stockTransfer_product_count').val();

            if(stockTransfer_product == 0 || stockTransfer_product == undefined){
                alert('Please add some products!');
                $('#fld_product_title').focus();

            }else{

                var msg = "Do you like to complete the transaction?";
                if (confirm(msg)){
                    Util.showProgressInd();
                    var url = 'index.php?module=hms_stockTransfer&_spAction=updateCompleteTransactionProduct&showHTML=0';
                    $.get(url, {stock_transfer_id: stock_transfer_id}, function(html){
                      cpm.hms.stockTransfer.reloadEditDisplay(stock_transfer_id, site_id); 
                    });
                }
            }
        });

        $('.rollbackChanges').livequery('click', function (){
            var stock_transfer_id      = $(this).attr('stock_transfer_id');
            var site_id                = $(this).attr('site_id');

            var msg = "Do you like to rollback the transaction?";
            if (confirm(msg)){
                Util.showProgressInd();
                var url = 'index.php?module=hms_stockTransfer&_spAction=rollbackCompleteTransactionProduct&showHTML=0';
                $.get(url, {stock_transfer_id: stock_transfer_id}, function(html){
                  cpm.hms.stockTransfer.reloadEditDisplay(stock_transfer_id, site_id); 
                });
            }
        });

        $('.deductFromStock').livequery('click', function (){
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var site_id = $(this).attr('site_id');

            var msg = "This action will deduct item(s) from the stock \n\n Would you like to continue?";
            if (confirm(msg)){
                Util.showProgressInd();
                var url = 'index.php?module=hms_stockTransfer&_spAction=updateDeductStockProduct&showHTML=0';
                $.get(url, {stock_transfer_id: stock_transfer_id}, function(html){
                  cpm.hms.stockTransfer.reloadEditDisplay(stock_transfer_id, site_id);
                });
            }
        });

        $("select[name='status']").live('change', function(){
            var product_status    = $(this).val();
            var stock_transfer_id = $('#record_id').val();
            var site_id           = $("input[name='site_id']").val();
            
            Util.showProgressInd();
            var url = 'index.php?module=hms_stockTransfer&_spAction=updateStatusStockTransfer&showHTML=0';
            $.get(url, {product_status: product_status, stock_transfer_id: stock_transfer_id, site_id: site_id}, function(html){
                cpm.hms.stockTransfer.reloadEditDisplay(stock_transfer_id, site_id); 
            });
            
        });
    },

    posProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=hms_stockTransfer&_spAction=searchProductTitle&showHTML=0'
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
    	           	var url = 'index.php?module=hms_stockTransfer&_spAction=updateOrderLineItems&showHTML=0';
                    $.get(url, {product_id: product_id, stock_transfer_id: stock_transfer_id}, function(json){
    	                cpm.hms.stockTransfer.reloadOrderItems(stock_transfer_id);
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
                var url = 'index.php?module=hms_stockTransfer&_spAction=updateOrderLineItems&showHTML=0';
                $.get(url, {product_id: product_id,stock_transfer_id: stock_transfer_id}, function(json){
                    cpm.hms.stockTransfer.reloadOrderItems(stock_transfer_id);
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
        var url = 'index.php?module=hms_stockTransfer&_spAction=orderItems&showHTML=0';
        $.get(url, {stock_transfer_id: stock_transfer_id}, function(html){
            $('#orderItems').html(html);
            Util.hideProgressInd();
        });
    },

    reloadEditDisplay: function(stock_transfer_id, site_id){
        var url = 'index.php?module=hms_stockTransfer&_spAction=editDisplay&showHTML=0';
        $.get(url, {stock_transfer_id: stock_transfer_id, site_id:site_id}, function(html){
            $('#editDisplayLoad').html(html);
            Util.hideProgressInd();
        });
    }

}
