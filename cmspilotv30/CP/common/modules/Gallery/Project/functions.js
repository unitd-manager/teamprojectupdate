Util.createCPObject('cpm.gallery.project');

cpm.gallery.project.init = function(){
    $('#frmEdit select#fld_category_id').livequery('change', function(){
       Util.loadSubCategoryDropdown.call(this);
    });
}
