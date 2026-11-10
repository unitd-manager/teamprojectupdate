Util.createCPObject('cpm.edukloud.attendance');

cpm.edukloud.attendance.init = function(){
    $(".takeAttendanceBtnWrap #takeAttendance").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');
        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 800, 600, expObj);        
    });
}
