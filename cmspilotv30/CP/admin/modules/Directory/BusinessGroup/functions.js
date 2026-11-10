Util.createCPObject('cpm.directory.businessGroup');

cpm.directory.businessGroup = {
    init: function(){
        $('#actBtn_updateBusinesses').click(cpm.directory.businessGroup.updateBusinesses);

        $('#frmEdit select#fld_category_id').livequery('change', function(){
            Util.loadDropdownByJSON('category_id', $(this).val(),
                                    'fld_sub_category_id',
                                    'webBasic_subCategory');
        });
    },

    updateBusinesses: function(e) {
        e.preventDefault();
        var msg = 'Are you sure to update Businesses with this Multiple\'s data?';
        Util.confirm(msg, function() {
            Util.closeAllDialogs();
            Util.showProgressInd('Updating Business records...');

            var business_group_id = $('#record_id').val();
            var url = 'index.php?module=directory_businessGroup&_spAction=updateBusinesses'
                    + '&business_group_id=' + business_group_id
                    + '&showHTML=0';
            $.get(url, function () {
                Util.alert('Business records updated.');
                Util.hideProgressInd();
            });
        });
    }
}