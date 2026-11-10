Util.createCPObject('cpm.pos.product');

cpm.pos.product = {
    init: function(){
        $('#frmEdit select#fld_category_id').livequery('change', function(){
            Util.loadDropdownByJSON('category_id', $(this).val(), 'fld_sub_category_id', 'pos_subCategory');
        });
    
        $('#frmNew select#fld_category_id').change(function() {
            var url = 'index.php?module=pos_subCategory&_spAction=subCategoryByCategoryJSON&showHTML=0';
            var category_id = $(this).val();
            $.get(url, {category_id: category_id}, function (data) {
                $('#fld_sub_category_id').cp_loadSelect(data);
            }, 'json');
        });
    
        $('.pos_product__pos_shopLink select[name=shop_id]').livequery('change', function(){
            var shop_id = $(this).val();
            var parent = $(this).closest('tr');
            var product_shop_id = $(parent).attr('recid');
            var url = 'index.php?module=pos_product&_spAction=updateCurrency&showHTML=0';
            $.get(url, {shop_id: shop_id, product_shop_id: product_shop_id}, function(html){
                //$('.row-pos_product__pos_shopLink').html(html);
            });
        });
    }
}
