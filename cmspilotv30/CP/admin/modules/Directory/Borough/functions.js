Util.createCPObject('cpm.directory.borough');

cpm.directory.borough.init = function(){
    $('#frmEdit select#fld_country_id').livequery('change', function(){
        Util.loadDropdownByJSON('country_id', $(this).val(), 'fld_state_id', 'directory_state');
    });

    $('#frmEdit select#fld_state_id').livequery('change', function(){
        Util.loadDropdownByJSON('state_id', $(this).val(), 'fld_city_id', 'directory_city');
    });
}
