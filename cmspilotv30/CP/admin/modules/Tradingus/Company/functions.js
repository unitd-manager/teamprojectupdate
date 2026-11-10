Util.createCPObject('cpm.tradingus.company');

cpm.tradingus.company = {
    init: function(){

        $('#portalForm select#fld_product_group_id').livequery('change', function(){
           cpm.tradingus.company.loadCategoryDropdown.call(this);
        });

        $(".formulaPad input[name='cost_price']").livequery('change', function(){
            var cost_price = $(this).val();
            service_cost = $(".formulaPad input[name='service_cost']").val();
            discount = $(".formulaPad input[name='discount']").val();
            var url = "index.php?module=tradingus_company&_spAction=formulaPadCalc&showHTML=0";
            $.get(url, {service_cost: service_cost, discount: discount, cost_price: cost_price}, function(json){
                $('.formulaPad .serviceCostValue').html(json.serviceCostValue);
                $('.formulaPad .sellingPrice').html(json.sellingPrice);
                $('.formulaPad .discountSellingPrice').html(json.discountSellingPrice);
                $('.formulaPad .serviceCostOutput').html(json.serviceCostOutput);
                $('.formulaPad .serviceCostTotal').html(json.serviceCostTotal);
            });
        });

        $(".formulaPad input[name='service_cost']").livequery('change', function(){
            var service_cost = $(this).val();
            cost_price = $(".formulaPad input[name='cost_price']").val();
            discount = $(".formulaPad input[name='discount']").val();
            var url = "index.php?module=tradingus_company&_spAction=formulaPadCalc&showHTML=0";
            $.get(url, {service_cost: service_cost, discount: discount, cost_price: cost_price}, function(json){
                $('.formulaPad .serviceCostValue').html(json.serviceCostValue);
                $('.formulaPad .sellingPrice').html(json.sellingPrice);
                $('.formulaPad .discountSellingPrice').html(json.discountSellingPrice);
                $('.formulaPad .serviceCostOutput').html(json.serviceCostOutput);
                $('.formulaPad .serviceCostTotal').html(json.serviceCostTotal);
            });
        });

        $(".formulaPad input[name='discount']").livequery('change', function(){
            var discount = $(this).val();
            cost_price = $(".formulaPad input[name='cost_price']").val();
            service_cost = $(".formulaPad input[name='service_cost']").val();
            var url = "index.php?module=tradingus_company&_spAction=formulaPadCalc&showHTML=0";
            $.get(url, {service_cost: service_cost, discount: discount, cost_price: cost_price}, function(json){
                $('.formulaPad .discountValue').html(json.discountValue);
                $('.formulaPad .sellingPrice').html(json.sellingPrice);
                $('.formulaPad .discountSellingPrice').html(json.discountSellingPrice);
                $('.formulaPad .serviceCostOutput').html(json.serviceCostOutput);
                $('.formulaPad .serviceCostTotal').html(json.serviceCostTotal);
            });
        });
    },

    loadCategoryDropdown: function(){
        $(this).each(function(){
            ProductGroupId = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=tradingus_product&_spAction=categoryJsonByProductGroupId&showHTML=0'

            $.getJSON(url, {product_group_id: ProductGroupId}, function(data) {
                $('#portalForm select#fld_category_id').cp_loadSelect(data);
            });
        });
    }
}