Util.createCPObject('cpm.tradingsg.supplierQuote');

cpm.tradingsg.supplierQuote = {
    init: function(){
        $(".m-tradingsg_supplierQuote input[name='product_title']").livequery('click', function(){
            var titleObj = this;
        	$(titleObj).autocomplete({
                 source : 'index.php?module=tradingsg_supplierQuote&_spAction=searchProductTitle&showHTML=0'
                ,minLength : 2
        		,select: function(event, ui) {
                    var selectedObj = ui.item;
        			var product_id = selectedObj.id
        			$(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");
        			//alert (product_id);

                    Util.showProgressInd();
                    var parent          = $(this).closest('tr');
                    var rec_id          = $(parent).attr('recid');
                    var productTitleObj = $(this ).closest('tr').find('.product-title');
                    var supplierIdObj   = $(this ).closest('tr').find('.supplier-id select[name=supplier_id]');
    
                    var url = 'index.php?module=tradingsg_supplierQuote&_spAction=updateSupplierProductLineItems&showHTML=0';
                    $.get(url, {product_id: product_id, rec_id: rec_id}, function(json){
                        
                    }); 

                    /*
                    var url = $('#scopeRootAlias').val() + 'index.php?module=tradingsg_supplierQuote&_spAction=supplierJsonByProductId&showHTML=0';
    
                    $.getJSON(url, {product_id: product_id}, function(data) {
                        supplierIdObj.cp_loadSelect(data);
                    });
                    */
    
                    Util.hideProgressInd();
                }
        	});
		});

        $('.row-tradingsg_supplierQuote__tradingsg_supplierQuoteHistoryLink .qty, .row-tradingsg_supplierQuote__tradingsg_supplierQuoteHistoryLink .price').livequery('change', function(){
            var parent = $(this).closest('tr');
            //var productObj = $(this).parents('tr').find('input[name=product_title]');
            //productObj.attr('disabled','disabled');
            var qtyObj = $(this).parents('tr').find('input[name=qty]');
            var qty = qtyObj.val();
            var priceObj = $(this).parents('tr').find('input[name=price]');
            var price = priceObj.val();
            var rec_id = $(parent).attr('recid');
            var costPriceObj = $(this ).closest('tr').find('.total-cost-price');
            var url = 'index.php?module=tradingsg_supplierQuote&_spAction=updateTotalCostPrice&showHTML=0';
            
            $.get(url, {rec_id: rec_id, qty: qty, price: price}, function(json){
                costPriceObj.html(json.cost_price);
                $('tr.summary-row .totalCp').html(json.total_cost_price_sum);  
            });
            
        });

        $('.m-tradingsg_supplierQuote #addProductForm').livequery('click', function (e){
                var title = "Add Product";
                e.preventDefault();
                
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);        
        });


        $('#raisePo').livequery('click', function(){
            msg = "Do you like to Raise PO?";
            
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var supplier_quote_id = $(this).attr('supplier_quote_id');
                var url = 'index.php?module=tradingsg_supplierQuote&_spAction=raisePurchaseOrder&showHTML=0&id=' + supplier_quote_id;
                $.get(url, {supplier_quote_id: supplier_quote_id}, function(html){
                    alert('Purchase Order Raised Successfully');
                    window.location.reload(true);
                });
                //Util.hideProgressInd();
            }
        });

        $("a.purchaseOrderDetail").livequery('click', function (e){
            Globals.purchase_order_id = $(this).attr('id');
    
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                    Util.alert('Changes made successfully..');
                }
            }
            Util.openDialogForLink.call(this, 'Purchase Order Detail', 700, 500, expObj);
        });

        $(".supplierViewHistory").livequery('click', function (e){
            var supplier_quote_id = $(this).attr('supplier_quote_id');
            //alert(supplier_quote_id);
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                    Util.alert('Changes made successfully..');
                }
            }
            Util.openDialogForLink.call(this, 'Product Purchase History', 700, 500, expObj);
        });

        $('.m-tradingsg_supplierQuote a.addNotePo').livequery('click', function (e){
                var title = "Add Note";
                e.preventDefault();
                
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Note added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 600, 300, expObj);  
        });

        $('.m-tradingsg_supplierQuote tbody tr input[id=qty_delivered], .m-tradingsg_supplierQuote tbody tr input[id=qty_cancelled]').livequery('change', function (e){
            Util.showProgressInd();
        
            var parent = $(this).closest('tr');
            var qtyDeliveredObj = $(this).parents('tr').find('input[name=qty_delivered]');
            var qtyDelivered = qtyDeliveredObj.val();
            var qtyCancelledObj = $(this).parents('tr').find('input[name=qty_cancelled]');
            var qtyCancelled = qtyCancelledObj.val();
            var qtyOrderedObj = $(this ).closest('tr').find('.qtyOrdered');
            var qtyOrdered = qtyOrderedObj.html();
            var po_product_id = $(this).attr('po_product_id');
            //alert( qtyDelivered);
            if(qtyDelivered > qtyOrdered){
                Util.alert('The qty delivered should not be more than than the qty ordered')
            }
            else if(qtyCancelled > (qtyOrdered-qtyDelivered)){
                Util.alert('The qty cancelled should not be more than than the (qty ordered-qty delivered)')
            } else {
                Util.showProgressInd();
                var url = 'index.php?module=tradingsg_supplierQuote&_spAction=updateQtyDelivered&showHTML=0&id=' + po_product_id;
                $.get(url, {po_product_id: po_product_id, qtyDelivered: qtyDelivered, qtyCancelled: qtyCancelled}, function(html){
                });
           }
    
            Util.hideProgressInd();
        });

	}
}