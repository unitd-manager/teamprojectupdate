Util.createCPObject('cpm.gdj.jewellery');

cpm.gdj.jewellery.init = function(){
    $('#frmEdit select#fld_category_id').livequery('change', function(){
       Util.loadSubCategoryDropdown.call(this);
    });
}