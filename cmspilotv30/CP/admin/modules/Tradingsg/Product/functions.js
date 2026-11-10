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


        $('a.addRawMaterial').livequery('click', function (e){
         var title = "material";
         
            var url = 'index.php?module=tradingsg_product&_spAction=addRawMaterial'
                    + '&showHTML=0';
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Material created successfully';
                   Util.alert(msg, function(){
                        Util.closeAllDialogs();
                       
                  });
                    
                }
            };
            Util.openFormInDialog.call(this, 'rawMaterialPortalForm', title, 700, 500, exp);
        });


        /*$('.m-tradingsg_product #actBtn_apply, .m-tradingsg_product #actBtn_save').livequery('click', function(){
            var product_id = $('#record_id').val();
            var url = 'index.php?module=tradingsg_product&_spAction=supplierValidationAlert&showHTML=0' +
                  '&product_id=' + product_id;
            var msg = 'No Supplier Linked';
            $.post(url, function (json) {
                if (json.status == 'No Supplier Linked') {
                    Util.alert(msg);
                    return;
                }
            }, 'json');
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


