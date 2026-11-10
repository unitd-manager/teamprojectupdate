Util.createCPObject('cpm.enterpriseIms.batch');
cpm.enterpriseIms.batch.init = function(){
    $('.enterpriseIms_batch__enterpriseIms_studentFeedbackLink select[name=group]').livequery('change', function(){
        Util.showProgressInd('Populating related content.... Please wait');

        cpm.enterpriseIms.batch.populateRelatedQuestions.call(this);
    });
    
}

cpm.enterpriseIms.batch.populateRelatedQuestions = function(){
    feedback_group = $(this).val();
    batch_id = $('input:hidden[name=batch_id]').val();
    
    var url = $('#scopeRootAlias').val() + 'index.php?module=enterpriseIms_batch&_spAction=feedbackQuestions&showHTML=0';
    $.get(url,{feedback_group: feedback_group, batch_id: batch_id}, function(html){
        
        $('#questionsList').html(html);

        var tblWidth = $('#questionsList table.thinlist th').length;
        $('#questionsList table.thinlist').css('width', tblWidth * 200 + 'px');
        Util.hideProgressInd();
    });
}

$("#showEvaluation").livequery('click', function (e){
    var title = "Assessment";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_batch#enterpriseIms_contactLink', 'enterpriseIms_batch');
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);        
});

$("#takeAttendance").livequery('click', function (e){
    var title = "Take Attendance";
    e.preventDefault();

    $('a.allPresent').livequery('click', function (e){
        $("input:radio[value=Present]").attr('checked', 'checked');
    });

    $('a.allAbsent').livequery('click', function (e){
        $("input:radio[value=Absent]").attr('checked', 'checked');
    });

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_batch#enterpriseIms_attendance', 'enterpriseIms_batch');
                window.location.reload(true);
            });
        }
    }

    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);    
});

$("#studentGrade").livequery('click', function (e){
    var title = "Student Grade";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_batch#enterpriseIms_studentGrade', 'enterpriseIms_batch');
                window.location.reload(true);
            });
        }
    }

    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);    
});

$(".editAttendance").livequery('click', function (e){
    var title = "Edit Attendance";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_batch#enterpriseIms_attendance', 'enterpriseIms_batch');
                window.location.reload(true);
            });
        }
    }

    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);    
});

$(".editStudentGrade").livequery('click', function (e){
    var title = "Edit Student Grade";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_batch#enterpriseIms_studentGrade', 'enterpriseIms_batch');
                window.location.reload(true);
            });
        }
    }

    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);    
});

$("#studentFeedback").livequery('click', function (e){
    var title = "Student Feedback";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_batch#enterpriseIms_feedback', 'enterpriseIms_batch');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 800, 500, expObj);        
});

$(".editStudentFeedback").livequery('click', function (e){
    var title = "Edit Student Feedback";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_batch#enterpriseIms_student_feedback', 'enterpriseIms_batch');
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 600,500, expObj);        
});

$('input#marks').livequery('change', function(){
    var mark = $(this).val();
    var batch_history_id = $(this).closest('tr').attr('id');
    var url = 'index.php?module=enterpriseIms_batch&_spAction=updateGrade&showHTML=0';
    grade = 'input#fld_' + batch_history_id + '_grade';
    $.get(url, {mark: mark}, function(html){
        $(grade).val(html);
    });

    var url = 'index.php?module=enterpriseIms_batch&_spAction=updateStudentResult&showHTML=0';
    studentResult = 'input#fld_' + batch_history_id + '_student_result';
    $.get(url, {mark: mark}, function(html){
        $(studentResult).val(html);
    });
});