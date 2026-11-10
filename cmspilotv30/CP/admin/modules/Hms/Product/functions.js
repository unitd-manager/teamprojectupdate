Util.createCPObject('cpm.hms.product');

cpm.hms.product = {
    init: function(){
        $('#frmEdit select#fld_category_id').livequery('change', function(){
           Util.loadSubCategoryDropdown.call(this);
        });

        $('#frmEdit select#fld_product_group_id').livequery('change', function(){
           cpm.tradingsg.product.loadCategoryDropdown.call(this);
        });

        $("#bulkAddVouchers").livequery('click', function (e){
            var title = "Bulk Generate Voucher Codes";

            e.preventDefault();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Generated successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        Links.reloadPortalRecords('tradingsg_product#ecommerce_productVoucherLink', 'tradingsg_product');
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 300, 150, expObj);
        });

        /* Add Product Price*/
        $('#AddProductPrice').live('click', function (e){
                var title = "Add Product Price";
                var product_id = $(this).attr('product_id');
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Price Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.hms.product.reloadProductPriceLink(product_id);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'AddProductPriceForm', title, 462, 302, expObj);
        });

        /* Edit Product Price*/
        $('.EditProductPrice').live('click', function (e){
            var title = "Edit Product Price";
            e.preventDefault();
            var product_id = $(this).attr('product_id');
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Price Updated Successfully';
                    Util.alert(msg, function(){ 
                        Util.closeAllDialogs();
                        cpm.hms.product.reloadProductPriceLink(product_id);
                        cpm.hms.product.reloadProductPriceHistoryLink(product_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'EditProductPriceForm', title, 462, 302, expObj);
        });

        /*$(".ui-corner-all").livequery('click', function (e){
            Links.reloadPortalRecords('tradingsg_product#tradingsg_companyLink', 'tradingsg_product');
        });*/
    },

    loadCategoryDropdown: function(){
        $(this).each(function(){
            ProductGroupId = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=tradingsg_product&_spAction=categoryJsonByProductGroupId&showHTML=0'

            $.getJSON(url, {product_group_id: ProductGroupId}, function(data) {
                $('#frmEdit select#fld_category_id').cp_loadSelect(data);
            });
        });
    },

    reloadProductPriceLink: function(product_id){
        var url = 'index.php?module=hms_product&_spAction=ProductPriceDetail&showHTML=0';
        $.get(url, {product_id: product_id}, function(html){
            $('#productPriceLinkPortal').html(html);
        });
    },

    reloadProductPriceHistoryLink: function(product_id){
        var url = 'index.php?module=hms_product&_spAction=ProductPriceHistory&showHTML=0';
        $.get(url, {product_id: product_id}, function(html){
            $('#productPriceHistoryLinkPortal').html(html);
        });
    },

    publishQuoteRecordFromList: function(room, rowID, currentValue, reUploadRecord){

        if(reUploadRecord){
            reUpload = 1;
        } else {
            reUpload = 0;
        }

        var url = $('#scopeRootAlias').val() + "index.php?_spAction=publishQuoteRecordByID&showHTML=0";

        var cell = "#txt__general_quote__" + rowID

        $(cell).html('processing');
        var data = {
             record_id: rowID
            ,room: room
            ,currentValue: currentValue
            ,reUpload: reUpload
        };
        $.post(url, data, function (data) {
            $(cell).html(data);
        });

    }
}


