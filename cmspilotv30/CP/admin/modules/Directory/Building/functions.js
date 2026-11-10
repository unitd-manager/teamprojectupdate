Util.createCPObject('cpm.directory.building');

cpm.directory.building = {
    init: function(){
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

        $('#calculateNearestTLink').click(cpm.directory.building.calculateNearestTLink);
    },

    calculateNearestTLink: function(){
        var url = $(this).attr('link');
        $.get(url, function (data) {
            document.location = document.location;
        });
    }

}

