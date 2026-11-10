Util.createCPObject('cpm.ecommerce.promoCode');

cpm.ecommerce.promoCode.init = function(){
    $('#frmEdit select#fld_country_id').livequery('change', function(){
        Util.loadDropdownByJSON('country_id', $(this).val(), 'fld_city_id', 'directory_city');
    });
}