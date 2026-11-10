Util.createCPObject('cpm.party.card');

cpm.party.card = {
    init: function(){
        $('#frmEdit select#fld_category_id').livequery('change', function(){
            Util.loadSubCategoryDropdown.call(this);
        });
    }
}