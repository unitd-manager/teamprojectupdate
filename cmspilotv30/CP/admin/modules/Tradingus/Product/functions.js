Util.createCPObject('cpm.tradingus.product');

cpm.tradingus.product = {
    init: function(){
        $('#frmEdit select#fld_category_id').livequery('change', function(){
           Util.loadSubCategoryDropdown.call(this);
        });

        $('#frmEdit select#fld_product_group_id').livequery('change', function(){
           cpm.tradingus.product.loadCategoryDropdown.call(this);
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
                        Links.reloadPortalRecords('tradingus_product#ecommerce_productVoucherLink', 'tradingus_product');
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 300, 150, expObj);
        });

        $('#addPriceNew').livequery('click', function (e){
            var title = "New PriceLink";
            var product_id = $(this).attr('product_id');
            e.preventDefault();

            var expObj = {
                validate: true
                ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                reloadPriceList.reloadPriceListobj(product_id);
                }
            }
            Util.openFormInDialog.call(this, 'portalFormPriceLink', title, 589, 253, expObj);
        });

        $('.editPrice').livequery('click', function (e){
            var title = "Edit PriceLink";
            var product_id       = $(this).attr('product_id');
            var product_price_id = $(this).attr('product_price_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                reloadPriceList.reloadPriceListobj(product_id);
                }
            }
            Util.openFormInDialog.call(this, 'portalFormPriceEditLink', title, 589, 253, expObj);
        });

        $('.deletePriceRecord').livequery('click', function (){
            var product_id       = $(this).attr('product_id');
            var product_price_id = $(this).attr('product_price_id');
            var url ='index.php?module=tradingus_product&_spAction=deletePriceRecord&showHTML=0'
              $.get(url, {product_id:product_id, product_price_id:product_price_id}, function(html){
                reloadPriceList.reloadPriceListobj(product_id);
              });
         });

        /*$(".ui-corner-all").livequery('click', function (e){
            Links.reloadPortalRecords('tradingus_product#tradingus_companyLink', 'tradingus_product');
        });*/
    },

    loadCategoryDropdown: function(){
        $(this).each(function(){
            ProductGroupId = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=tradingus_product&_spAction=categoryJsonByProductGroupId&showHTML=0'

            $.getJSON(url, {product_group_id: ProductGroupId}, function(data) {
                $('#frmEdit select#fld_category_id').cp_loadSelect(data);
            });
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

var reloadPriceList ={
    reloadPriceListobj: function(product_id){
             var url = 'index.php?module=tradingus_product&_spAction=priceDisplay&showHTML=0';
            $.get(url, {product_id: product_id}, function(html){
                $('#priceLinkPortal').html(html);
                Util.hideProgressInd();
             });
    }
}


