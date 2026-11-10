Util.createCPObject('cpw.ecommerce.shippingDetails');

cpw.ecommerce.shippingDetails.init = function(){
    $(function () {
        $('.btnProceedToConfirm a').click(function(e){
            e.preventDefault();
            $('form#frmShippingDetails').submit();
        });

        $('#fld_organization_id').change(function(){
            var url = '/index.php?widget=ecommerce_shippingDetails&_spAction=orgDetails';
            $.get(url, {organization_id: $(this).val()}, function(html){
                $('#orgDetails').html(html);
            });
        });
        
        if($('#fld_organization_id').length > 0 && $('#fld_organization_id').val()){
            $('#fld_organization_id').trigger('change');
        }
    });
}
