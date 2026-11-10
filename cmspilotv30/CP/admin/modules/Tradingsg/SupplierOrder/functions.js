Util.createCPObject('cpm.tradingsg.supplierOrder');

cpm.tradingsg.supplierOrder = {
    init: function(){
        $('.m-tradingsg_supplierOrder  #shwoPoProduct').livequery('click', function (e){
            var title = "Related Products";
            e.preventDefault();

            var exp = {
                onCloseFn: function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                    //cpm.tradingsg.supplierOrder.reloadPurchaseOrder();
                }
            }
            Util.openDialogForLink.call(this, title, 600, 500, true, exp);
        });

        $('.checkProduct').livequery('click', function(){
            var purchase_order_id = $(this).val();
            var product_id = $(this).attr('product_id');
            var supplier_order_id = $(this).attr('supplier_order_id');
            var checked = $(this).attr('checked');

            if (checked == 'checked') {
                var url = 'index.php?_topRm=order&module=tradingsg_supplierOrder&_spAction=createSOHistoryRecord&showHTML=0';
                $.get(url,{purchase_order_id: purchase_order_id, product_id: product_id, supplier_order_id: supplier_order_id}, function(){
                    Util.hideProgressInd();
                });
            } else {
                var url = 'index.php?_topRm=order&module=tradingsg_supplierOrder&_spAction=deleteSupplierHistoryRecord&showHTML=0';
                $.get(url,{purchase_order_id: purchase_order_id, product_id: product_id}, function(){
                    Util.hideProgressInd();
                });
            }
        });
    },

    reloadPurchaseOrder: function(){
        var supplier_order_id = $(this).attr('supplier_order_id');
        //alert(supplier_order_id);
        //return;
        var url = 'index.php?module=tradingsg_supplierOrder&_spAction=productPortalDisplay&showHTML=0';
        $.get(url,  function(html){
            $('#poItems').html(html);
            Util.hideProgressInd();
        });
    }

}

