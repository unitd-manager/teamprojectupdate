Util.createCPObject('cpm.ecommerce.product');

cpm.ecommerce.product = {
    init: function(){
        $('#frmEdit select#fld_section_id').livequery('change', function(){
           Util.loadCategoryDropdown.call(this);
        });

        $('#frmEdit select#fld_category_id').livequery('change', function(){
           Util.loadSubCategoryDropdown.call(this);
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
                        Links.reloadPortalRecords('ecommerce_product#ecommerce_productVoucherLink', 'ecommerce_product');
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 300, 150, expObj);
        });
    },

    showInWebProductRecordFromList: function(room, rowID, currentValue, reUploadRecord){

        if(reUploadRecord){
            reUpload = 1;
        } else {
            reUpload = 0;
        }

        var url = $('#scopeRootAlias').val() + "index.php?_spAction=showInWebProductRecordByID&showHTML=0";

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
