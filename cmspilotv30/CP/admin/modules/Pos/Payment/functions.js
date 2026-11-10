Util.createCPObject('cpm.pos.payment');

cpm.pos.payment.init = function(){

        $('#fld_payment_type').livequery('change', function(){
            var paymentType = $(this).val();
            if (paymentType == 'Invoice'){
                $('.row_days').hide();
            } else {
                $('.row_days').show();
            }
        });

}