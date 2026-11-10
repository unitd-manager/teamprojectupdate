Util.createCPObject('cpm.trading.company');

cpm.trading.company = {
    init: function(){
        $("select#fld_category").change(function() {
            var value = $(this).val().toLowerCase();
            if (value == 'supplier'){
                $('#fld_industry').closest('div').hide();
                $('#fld_supplier_type').closest('div').show();
            } else {
                $('#fld_supplier_type').closest('div').hide();
                $('#fld_industry').closest('div').show();
            }
        });

        $('select#fld_category')
        .livequery(function(e) {
            var defValue = $(this).val().toLowerCase();
            if (defValue == 'supplier'){
                $('#fld_industry').closest('div').hide();
            } else {
                $('#fld_supplier_type').closest('div').hide();
            }
        });

        //category / sub category
        $('select#fld_category_id').change(function() {
            var url = 'index.php?module=webBasic_subCategory&_spAction=subCategoryByCategoryJSON&showHTML=0';
            var category_id = $(this).val();
            $.get(url, {category_id: category_id}, function (data) {
                $('#fld_sub_category_id').cp_loadSelect(data);
            }, 'json');
        });

    }
}