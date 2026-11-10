Util.createCPObject('cpw.ecommerce.basket');

cpw.ecommerce.basket.init = function(){
    $(function () {
        $('.w-ecommerce-basket .btnEmptyCart').click(function (e) {
            e.preventDefault();
            modName = $(this).closest('.shopBtns').attr('modName');

            Util.confirm('Are you sure to proceed?', function(){
                var url = '/index.php?widget=ecommerce_basket&_spAction=emptyBasket&showHTML=0';
                $.get(url, {modName: modName}, function(){
                    document.location = document.location;
                })
            });
        });

        $('.w-ecommerce-basket select[name=qty]').change(function (e) {
            e.preventDefault();
            var basket_id = $(this).attr('basket_id');
            var qty = $(this).val();
            var url = '/index.php?widget=ecommerce_addToCart&_spAction=add&showHTML=0';

            $.getJSON(url, {basket_id: basket_id, qty: qty}, function(json){
                document.location = json.basketUrl;
            });
        });

        $('.w-ecommerce-basket input[name=qty]').change(function() {
            var basket_id = $(this).attr('basket_id');
            var qty = $(this).val();
            var url = '/index.php?widget=ecommerce_addToCart&_spAction=add&showHTML=0';
            Util.showProgressInd();
            $.getJSON(url, {basket_id: basket_id, qty: qty}, function(json){
                document.location = json.basketUrl;
            });
        });

        $(".w-ecommerce-basket input[name=qty]").keyup(function() {
            /** BSAS: commented because it caused some errors in IE **/
            //$(this).val(this.value.match(/[0-9]*/));
        });

        $('.w-ecommerce-basket .removeItem').click(function (e) {
            e.preventDefault();
            var basket_id = $(this).attr('basket_id');
            var url = '/index.php?widget=ecommerce_addToCart&_spAction=add&showHTML=0';

            $.getJSON(url, {basket_id: basket_id, qty: 0}, function(json){
                document.location = json.basketUrl;
            })
        });

        $('.w-ecommerce-basket select#fld_shippingCountryCode').change(function (e) {
            e.preventDefault();
            var country_code = $(this).val();
            var url = '/index.php?widget=ecommerce_basket&_spAction=changeShippingCountry&showHTML=0';

            $.getJSON(url, {country_code: country_code}, function(json){
                var shippingCharge = json.shippingCharge;
                var netTotal = json.netTotal;

                $('.w-ecommerce-basket .shippingChargeValue span.priceDisplay').html(shippingCharge);
                $('.w-ecommerce-basket .netTotalValue span.priceDisplay').html(netTotal);
            })
        });

        $('.w-ecommerce-basket a#enterPromoCode').click(function (e) {
            e.preventDefault();
            var url = '/index.php?widget=ecommerce_basket&_spAction=enterPromoCode&showHTML=0';

            var exp = {
                 url: url
                ,submitBtnText: 'Apply'
                ,callbackOnSuccess: function(json){
                    var discount = json.extraParam.discount;
                    var netTotal = json.extraParam.netTotal;
                    var promoCode = json.extraParam.promoCode;

                    $('.w-ecommerce-basket .discountValue span.priceDisplay').html(discount);
                    $('.w-ecommerce-basket .netTotalValue span.priceDisplay').html(netTotal);
                    $('.w-ecommerce-basket #enterPromoCode').hide();
                    $('.w-ecommerce-basket #promoCodeAppliedWrapper code').html(promoCode);
                    $('.w-ecommerce-basket #promoCodeAppliedWrapper').removeClass('hideme').show();

                    Util.closeTopMostDialog();
                }
            }

            Util.openFormInDialog('frmPromoCode', 'Enter Promo Code', 300, 200, exp);
        });

        $('.w-ecommerce-basket #promoCodeAppliedWrapper a.remove').click(function (e) {
            e.preventDefault();
            var url = '/index.php?widget=ecommerce_basket&_spAction=removePromoCode&showHTML=0';

            $.getJSON(url, function(json){
                var discount = json.discount;
                var netTotal = json.netTotal;

                $('.w-ecommerce-basket .discountValue span.priceDisplay').html(discount);
                $('.w-ecommerce-basket .netTotalValue span.priceDisplay').html(netTotal);
                $('.w-ecommerce-basket #enterPromoCode').removeClass('hideme').show();
                $('.w-ecommerce-basket #promoCodeAppliedWrapper').hide();
            })
        });
    });

}

