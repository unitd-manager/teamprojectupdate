Util.createCPObject('cpm.directory.street');

cpm.directory.street.init = function(){
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
}
