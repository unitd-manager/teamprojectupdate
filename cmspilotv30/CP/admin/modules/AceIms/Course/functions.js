Util.createCPObject('cpm.aceIms.course');
cpm.aceIms.course.init = function(){
    $('#frmEdit select[name=program_group_id]').livequery('change', function(){

        Util.showProgressInd('Populating Subsidy Discount records... Please Wait');
        
        program_group_id = $(this).val();
        course_id = $('input[name=course_id]').val();
        var url = 'index.php?module=aceIms_course&_spAction=addSubsidyDiscountPortal&program_group_id=' + program_group_id + '&showHTML=0';
        
        $.get(url, {program_group_id: program_group_id, course_id: course_id}, function(html){
            Util.closeAllDialogs();
            Links.reloadPortalRecords('aceIms_course#aceIms_courseSubsidyLink', 'aceIms_course');
            Util.hideProgressInd();
        });
    });
}
