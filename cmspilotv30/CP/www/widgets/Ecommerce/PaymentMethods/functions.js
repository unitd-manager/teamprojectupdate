Util.createCPObject('cpw.ecommerce.paymentMethods');

cpw.ecommerce.paymentMethods.init = function(){
    var defOption = $('.w-ecommerce-paymentMethods input[name=payment_method]:checked').val();
    $('form#frmShippingDetails input[name=payment_method]').val(defOption);

    $('.w-ecommerce-paymentMethods input[name=payment_method]').click(function(){
        var chosenOption = $(this).val();
        $('form#frmShippingDetails input[name=payment_method]').val(chosenOption);
    });
}
