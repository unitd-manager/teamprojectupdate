Util.createCPObject('cpm.tradingsg.product');

cpm.tradingsg.product = {
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

        $("a.quickAdd").livequery('click', function (e){
            var title = "Quick Add product";
            var url = 'index.php?module=tradingsg_product&_spAction=quickAdd'
                    + '&showHTML=0';
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                    });
                }
            };
            Util.openFormInDialog.call(this, 'quickAddForm', title, 1200, 500, exp);
        });

        /* Adding row in quick product*/
        $("a.addRow").livequery('click', function (e){
            var url = 'index.php?module=tradingsg_product&_spAction=addProductRecord'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#productTable tr:last').after(html);
            });

        });

        /*$(".ui-corner-all").livequery('click', function (e){
            Links.reloadPortalRecords('tradingsg_product#tradingsg_companyLink', 'tradingsg_product');
        });*/
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
                            cpm.tradingsg.product.reloadProductPriceLink(product_id);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'AddProductPriceForm', title, 550, 482, expObj);
        });

        $("select[name=discount_type]").livequery('change', function (e){
            var discount_type       = $(this).val();
            var discount_percentage = $('input[name=discount_percentage]').val();
            var discount_amount     = $('input[name=discount_amount]').val();
            
            if (discount_type == ""){
                $('.hideDiscountPercent').removeClass('showDiscountPercent');
                $('.hideDiscountAmount').removeClass('showDiscountAmount');
            }

            else if(discount_type == "%"){
                $('.hideDiscountPercent').addClass('showDiscountPercent');
                $('.hideDiscountAmount').removeClass('showDiscountAmount');

                if(discount_amount > 0){
                    $('input[name=discount_percentage]').val(discount_amount);
                }
            }

            else {
                $('.hideDiscountAmount').addClass('showDiscountAmount');
                $('.hideDiscountPercent').removeClass('showDiscountPercent');

                if(discount_percentage > 0){
                    $('input[name=discount_amount]').val(discount_percentage);
                }
            }
        });
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
        var url = 'index.php?module=tradingsg_product&_spAction=ProductPriceDetail&showHTML=0';
        $.get(url, {product_id: product_id}, function(html){
            $('#productPriceLinkPortal').html(html);
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


