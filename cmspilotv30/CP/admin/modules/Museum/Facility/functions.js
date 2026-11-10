Util.createCPObject('cpm.museum.facility');

cpm.museum.facility.init = function(){
    $('#frmEdit select#fld_section_id').livequery('change', function(){
       Util.loadCategoryDropdown.call(this);
    });

    $('#frmEdit select#fld_category_id').livequery('change', function(){
       Util.loadSubCategoryDropdown.call(this);
    });
}