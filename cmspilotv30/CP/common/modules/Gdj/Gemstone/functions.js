Util.createCPObject('cpm.gdj.gemstone');

cpm.gdj.gemstone.init = function(){
    $('#frmEdit select#fld_category_id').livequery('change', function(){
       Util.loadSubCategoryDropdown.call(this);
    });
}