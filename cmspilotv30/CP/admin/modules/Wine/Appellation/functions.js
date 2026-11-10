Util.createCPObject('cpm.wine.appellation');

cpm.wine.appellation.init = function(){
    $('#frmEdit select#fld_country_code').livequery('change', function(){
        Util.loadDropdownByJSON('country_code', $(this).val(), 'fld_region_id', 'common_region');
    });
}
