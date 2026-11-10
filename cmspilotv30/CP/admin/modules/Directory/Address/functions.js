Util.createCPObject('cpm.directory.address');

cpm.directory.address.init = function(){
    $('#frmNew select#fld_country_id').livequery('change', function(){
        Util.loadDropdownByJSON('country_id', $(this).val(), 'fld_state_id', 'directory_state', 'frmNew');
    });
    $('#frmEdit select#fld_country_id').livequery('change', function(){
        Util.loadDropdownByJSON('country_id', $(this).val(), 'fld_state_id', 'directory_state');
    });
    $('#frmEdit select#fld_state_id').livequery('change', function(){
        Util.loadDropdownByJSON('state_id', $(this).val(), 'fld_city_id', 'directory_city');
    });
    $('#frmEdit select#fld_city_id').livequery('change', function(){
        Util.loadDropdownByJSON('city_id', $(this).val(), 'fld_borough_id', 'directory_borough');
    });
    $('#frmEdit select#fld_borough_id').livequery('change', function(){
        Util.loadDropdownByJSON('borough_id', $(this).val(), 'fld_area_id', 'directory_area');
    });
    $('#frmEdit select#fld_area_id').livequery('change', function(){
        Util.loadDropdownByJSON('area_id', $(this).val(), 'fld_street_id', 'directory_street');
    });
    $('#frmEdit select#fld_street_id').livequery('change', function(){
        //Util.loadDropdownByJSON('street_id', $(this).val(), 'fld_shop_center_id', 'directory_shopCenter');
        Util.loadDropdownByJSON('street_id', $(this).val(), 'fld_building_id', 'directory_building');
    });
}
