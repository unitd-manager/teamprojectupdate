Util.createCPObject('cpw.ecommerce.addToCart');

cpw.ecommerce.addToCart = {
    init: function(){
        $('.w-ecommerce-addToCart .ic-cart').livequery('click', function(e){
            e.preventDefault();
            var url = $(this).attr('url');

            if ($('#addToCartQty').length > 0){
                var qty = $('#addToCartQty').val();

                if (qty > 0){
                    url += '&qty=' + qty;
                }
            }

            $.getJSON(url, function(json){
                document.location = json.basketUrl;
            });
        });
    }
}