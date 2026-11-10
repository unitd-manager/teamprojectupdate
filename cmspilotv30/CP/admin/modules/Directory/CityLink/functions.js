Util.createCPObject('cpm.directory.city');

cpm.directory.city.init = function(){
    $('#frmEdit select#fld_country_id').livequery('change', function(){
        Util.loadDropdownByJSON('country_id', $(this).val(), 'fld_state_id', 'directory_state');
    });
}
