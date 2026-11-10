Util.createCPObject('cpm.pos.purchaseOrder');

cpm.pos.purchaseOrder = {
    init: function(){
        $('a#addPurchaseOrderItems').livequery('click', function(){
            Util.showProgressInd();
    
            var url = 'index.php?module=pos_purchaseOrder&_spAction=insertPurchaseOrderItems&showHTML=0';
            var purchase_order_id = $(this).attr('purchase_order_id');
    
            $.get(url, {purchase_order_id: purchase_order_id}, function(html){
                cpm.pos.purchaseOrder.reloadPurchaseOrderItems();
            });
        });

        $('.deletePurchaseOrderItem').livequery('click', function(){
            var purchase_order_items_id = $(this).attr('purchase_order_items_id');
            Util.showProgressInd();
            var url = "index.php?module=pos_purchaseOrder&_spAction=deletePurchaseOrderItem&showHTML=0";
            $.get(url, {purchase_order_items_id: purchase_order_items_id}, function(){
                cpm.pos.purchaseOrder.reloadPurchaseOrderItems();
                cpm.pos.purchaseOrder.updateTotalValues();
            });
        });

        $('input[name=sku_no]').livequery('change', function(){
            var sku_no = $(this).val();
            var purchase_order_items_id = $(this).closest('tr').attr('id');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=updateSkuNoPurchaseOrderItem&showHTML=0';
            $.get(url, {sku_no: sku_no, purchase_order_items_id: purchase_order_items_id}, function(html){
                cpm.pos.purchaseOrder.reloadPurchaseOrderItems();
            });
        });

        $('input[name=vendor_sku_no]').livequery('change', function(){
            var vendor_sku_no = $(this).val();
            var purchase_order_items_id = $(this).attr('title');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=updateVendorSkuNoPurchaseOrderItem&showHTML=0';
            $.get(url, {vendor_sku_no: vendor_sku_no, purchase_order_items_id: purchase_order_items_id}, function(html){
            });
        });

        $('input[name=qty]').livequery('change', function(){
            var qty = $(this).val();
            var purchase_order_items_id = $(this).attr('title');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=updateQtyPurchaseOrderItem&showHTML=0';
            classTotal = '.purchaseOrderItemtotal_' + purchase_order_items_id;
            $.get(url, {qty: qty, purchase_order_items_id: purchase_order_items_id}, function(html){
                $(classTotal).html(html);
                cpm.pos.purchaseOrder.updateTotalValues();
            });
        });

        $('input[name=unit_price]').livequery('change', function(){
            var unit_price = $(this).val();
            var purchase_order_items_id = $(this).attr('title');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=updateUnitPricePurchaseOrderItem&showHTML=0';
            classTotal = '.purchaseOrderItemtotal_' + purchase_order_items_id;
            $.get(url, {unit_price: unit_price, purchase_order_items_id: purchase_order_items_id}, function(html){
                $(classTotal).html(html);
                cpm.pos.purchaseOrder.updateTotalValues();
            });
        });

        $('input[name=discount]').livequery('change', function(){
            var discount = $(this).val();
            var purchase_order_items_id = $(this).attr('title');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=updateDiscountPurchaseOrderItem&showHTML=0';
            classTotal = '.purchaseOrderItemtotal_' + purchase_order_items_id;
            $.get(url, {discount: discount, purchase_order_items_id: purchase_order_items_id}, function(html){
                $(classTotal).html(html);
                cpm.pos.purchaseOrder.updateTotalValues();
            });
        });

        $('#overallDiscount').livequery('change', function(){
            var overall_discount = $(this).val();
            var purchase_order_id = $(this).attr('purchase_order_id');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=updateOverallDiscountPurchaseOrder&showHTML=0';
            $.get(url, {overall_discount: overall_discount, purchase_order_id: purchase_order_id}, function(html){
                cpm.pos.purchaseOrder.updateTotalValues();
            });
        });

        $('input.pos_vendorLink').livequery('click', function(){
            var vendor_code = $(this).attr('recid');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=populateVendorName&showHTML=0';
            $.get(url, {vendor_code: vendor_code}, function(html){
                $('#fld_vendor_name').html(html);
            });
        });

        $('input.pos_staffLink').livequery('click', function(){
            var staff_id = $(this).attr('recid');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=populateStaffName&showHTML=0';
            $.get(url, {staff_id: staff_id}, function(html){
                $('#fld_staff_name').html(html);
            });
        });

        $('input.pos_shopLink').livequery('click', function(){
            var shop_id = $(this).attr('recid');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=populateShopName&showHTML=0';
            $.get(url, {shop_id: shop_id}, function(json){
                $('#fld_shop_name').html(json.title);
                $('#fld_address').val(json.address);
                $('#fld_phone').val(json.phone);
            })

        });

        $('input.pos_warehouseLink').livequery('click', function(){
            var warehouse_code = $(this).attr('recid');
            var url = 'index.php?module=pos_purchaseOrder&_spAction=populateWarehouseName&showHTML=0';
            $.get(url, {warehouse_code: warehouse_code}, function(html){
                $('#fld_warehouse_name').html(html);
            });
        });
    },
    reloadPurchaseOrderItems: function(){
        var url = 'index.php?module=pos_purchaseOrder&_spAction=purchaseOrderItems&showHTML=0';
        $.get(url,  function(html){
            $('#purchaseOrderItems').html(html);
            Util.hideProgressInd();
        });
    },

    updateTotalValues: function(){
        var url = 'index.php?module=pos_purchaseOrder&_spAction=totalValues&showHTML=0';
        $.get(url, function(json){
            $('#purchaseOrderSubTotal').html(json.subTotal);
            $('#purchaseOrderDiscAmount').html(json.discTotal);
            $('#purchaseOrderLessAmount').html(json.lessAmount);
            $('#purchaseOrderNetTotal').html(json.netTotal);
            $('#overallDiscountAmount').html(json.overallDiscount);
        });
    }
    
}
 