Util.createCPObject('cpm.directory.business');

cpm.directory.business.init = function(){
    $('#frmEdit select#fld_category_id').livequery('change', function(){
       Util.loadSubCategoryDropdown.call(this);
    });
}
