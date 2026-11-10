//$("input:radio[value=Present]").attr('checked', 'checked');

Util.createCPObject('cpm.agileIms.batch');
cpm.agileIms.batch.init = function(){
    $('.agileIms_batch__agileIms_studentFeedbackLink select[name=feedback_group]').livequery('change', function(){
        Util.showProgressInd('Populating related content.... Please wait');
        cpm.agileIms.batch.populateRelatedQuestions.call(this);
    });

    $('.m-agileIms_batch #frmEdit select#fld_course_id').livequery('change', function(){
        Util.showProgressInd('Populating Related Subjects.... Please wait');
        var course_id = $(this).val();

        var url = 'index.php?module=agileIms_subjectLink&_spAction=subjectsByCourseJSON&showHTML=0';
        $.get(url, {course_id: course_id}, function (data) {
            $('#fld_subject_id').cp_loadSelect(data);
        }, 'json');
        Util.hideProgressInd();
    });
}

cpm.agileIms.batch.populateRelatedQuestions = function(){
    feedback_group = $(this).val();
    batch_id = $('input:hidden[name=batch_id]').val();

    var url = $('#scopeRootAlias').val() + 'index.php?module=agileIms_feedback&_spAction=feedbackQuestions&showHTML=0';
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
                Links.reloadPortalRecords('agileIms_batch#agileIms_contactLink', 'agileIms_batch');
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
                Links.reloadPortalRecords('agileIms_batch#agileIms_attendance', 'agileIms_batch');
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
                Links.reloadPortalRecords('agileIms_batch#agileIms_studentGrade', 'agileIms_batch');
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
                Links.reloadPortalRecords('agileIms_batch#agileIms_attendance', 'agileIms_batch');
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
                Links.reloadPortalRecords('agileIms_batch#agileIms_studentGrade', 'agileIms_batch');
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
                Links.reloadPortalRecords('agileIms_batch#agileIms_feedback', 'agileIms_batch');
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
                Links.reloadPortalRecords('agileIms_batch#agileIms_student_feedback', 'agileIms_batch');
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 800,500, expObj);
});

$('input#marks').livequery('change', function(){
    var mark = $(this).val();
    var batch_history_id = $(this).closest('tr').attr('id');
    var url = 'index.php?module=agileIms_batch&_spAction=updateGrade&showHTML=0';
    grade = 'input#fld_' + batch_history_id + '_grade';
    $.get(url, {mark: mark}, function(html){
        $(grade).val(html);
    });

    var url = 'index.php?module=agileIms_batch&_spAction=updateStudentResult&showHTML=0';
    studentResult = 'input#fld_' + batch_history_id + '_student_result';
    $.get(url, {mark: mark}, function(html){
        $(studentResult).val(html);
    });
});

/* Sending of email to Students on batch update */
$('#alertBatchChangesToStudents').livequery('click', function (e){
    var title = "Alert Batch Changes";
    e.preventDefault();
    
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Email sent successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 300, expObj);
});
