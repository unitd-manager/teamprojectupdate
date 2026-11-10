Util.createCPObject('cpm.edukloud.course');
cpm.edukloud.course.init = function(){
    $('#frmEdit select[name=program_group_id]').livequery('change', function(){

        Util.showProgressInd('Populating Subsidy Discount records... Please Wait');
        
        program_group_id = $(this).val();
        course_id = $('input[name=course_id]').val();
        var url = 'index.php?module=edukloud_course&_spAction=addSubsidyDiscountPortal&program_group_id=' + program_group_id + '&showHTML=0';
        
        $.get(url, {program_group_id: program_group_id, course_id: course_id}, function(html){
            Util.closeAllDialogs();
            Links.reloadPortalRecords('edukloud_course#edukloud_courseSubsidyLink', 'edukloud_course');
            Util.hideProgressInd();
        });
    });
}
