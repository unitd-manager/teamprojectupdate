//$("input:radio[value=Present]").attr('checked', 'checked');

Util.createCPObject('cpm.pms.batch');
cpm.pms.batch.init = function(){
    $('.pms_batch__pms_studentFeedbackLink select[name=group]').livequery('change', function(){
        Util.showProgressInd('Populating related content.... Please wait');

        cpm.pms.batch.populateRelatedQuestions.call(this);
    });

    $('.studentGrade').livequery('click', cpm.pms.batch.newStudentGrade);
    $('.studentAssessment .createGradeMarksTd .type-text input').livequery('change', cpm.pms.batch.createUpdateStudentGrade);
    $('.studentAssessment .createGradeRemarksMarksTd .type-text textarea').livequery('change', cpm.pms.batch.createUpdateStudentGrade);
    $('.editAssessmentSurahForm .createGradeMarksTd .type-text input').livequery('change', cpm.pms.batch.createUpdateEditStudentGrade);
    $('.editAssessmentSurahForm .createGradeRemarksMarksTd .type-text textarea').livequery('change', cpm.pms.batch.createUpdateEditStudentGrade);
    $('.studentAssessment select[name=subject]').livequery('change', cpm.pms.batch.subjectChange);
    $('.studentAssessment #fld_assessment_year').livequery('change', cpm.pms.batch.yearChange);
    $('.editStudentGrade').livequery('click', cpm.pms.batch.editStudentGrade);
    $('.deleteStudentGrade').livequery('click', cpm.pms.batch.deleteStudentGrade);
    $('.viewSurahLink').livequery('click', cpm.pms.batch.newAssessmentSurah);
    $('.editSurahLink').livequery('click', cpm.pms.batch.editAssessmentSurah);
    $('.addAssessmentSurahForm .fld_iqrayear_row').livequery('change', cpm.pms.batch.newAssessmentSurahYear);
    $('.editAssessmentSurahForm .fld_iqrayear_row').livequery('change', cpm.pms.batch.newAssessmentSurahYear);

    $('.addAssessmentSurahForm .fld_iqratype_row').livequery('change', cpm.pms.batch.newAssessmentSurahType);
    $('.editAssessmentSurahForm .fld_iqratype_row').livequery('change', cpm.pms.batch.newAssessmentSurahType);
    $('.enrollContactFromWaitingList').livequery('click', cpm.pms.batch.enrollContactFromWaitingList);
    $('.cancelContactFromWaitingList').livequery('click', cpm.pms.batch.cancelContactFromWaitingList);
    $('#newStudentGradeFormPopup .ui-dialog-titlebar-close').livequery('click', cpm.pms.batch.reloadBatchModule);
    $('#editStudentGradeFormPopup .ui-dialog-titlebar-close').livequery('click', cpm.pms.batch.reloadBatchModule);
    $('.studentAssessment select[name=exam_type]').livequery('change', cpm.pms.batch.reloadBatchStudents);
    $('.studentAssessment select[name=subject]').livequery('change', cpm.pms.batch.reloadBatchStudents);
}

cpm.pms.batch.populateRelatedQuestions = function(){
    feedback_group = $(this).val();
    batch_id = $('input:hidden[name=batch_id]').val();

    var url = $('#scopeRootAlias').val() + 'index.php?module=pms_batch&_spAction=feedbackQuestions&showHTML=0';
    $.get(url,{feedback_group: feedback_group, batch_id: batch_id}, function(html){

        $('#questionsList').html(html);

        var tblWidth = $('#questionsList table.thinlist th').length;
        $('#questionsList table.thinlist').css('width', tblWidth * 200 + 'px');
        Util.hideProgressInd();
    });
}

/*$("#printAttendance").livequery('click', function (e){
    var title = "Print Attendance";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('pms_batch#pms_contactLink', 'pms_batch');
            });
        }
    }
    Util.openFormInDialog.call(this, '', title, 500, 500, expObj);
}); */

$("#showEvaluation").livequery('click', function (e){
    var title = "Assessment";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('pms_batch#pms_contactLink', 'pms_batch');
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);
});

$(".takeAttendance").livequery('click', function (e){
    var title = "Take Attendance";
    e.preventDefault();

    $('#fld_date').livequery('change', function(e){
        var date = $(this).val();
        var batch_id = $('input:hidden[name=batch_id]').val();
        var url = 'index.php?module=pms_batch&_spAction=validateAttendanceDate&showHTML=0';
        $.get(url, {date: date, batch_id: batch_id}, function(html){
            if(html == 'Yes'){
                alert('Attendance already taken for this date. Please choose another date.');
                $('#nameDisplay').hide();
                $('#attDisplay').hide();
            } else if (html == 'Holiday') {
                alert('Chosen date is holiday. Please choose another date.');
                $('#nameDisplay').hide();
                $('#attDisplay').hide();
            } else {
                var url1 = 'index.php?module=pms_batch&_spAction=attendanceRecords&showHTML=0';
                Util.showProgressInd();
                $.get(url1, {date: date, batch_id: batch_id}, function(html){
                    $('#nameDisplay').show();
                    $('#attDisplay').show();
                    $('#attDisplay tbody').html(html);
                    Util.hideProgressInd();
                });
            }
        });
    });

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
                Links.reloadPortalRecords('pms_batch#pms_attendance', 'pms_batch');
                window.location.reload(true);
            });
        }
    }

    Util.openFormInDialog.call(this, 'portalForm', title, 1000, 500, expObj);
});

cpm.pms.batch.newStudentGrade = function(){
    var title = "Student Grade";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('pms_batch#pms_studentGrade', 'pms_batch');
                window.location.reload(true);
            });
        }
    }

    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);
}

cpm.pms.batch.subjectChange = function(){
}

cpm.pms.batch.yearChange = function(){
}

$(".editAttendance").livequery('click', function (e){
    var title = "Edit Attendance";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('pms_batch#pms_attendance', 'pms_batch');
                window.location.reload(true);
            });
        }
    }

    Util.openFormInDialog.call(this, 'portalForm', title, 1000, 500, expObj);
});

$('.deleteAttendance').live('click', function (e){
    msg = "Do you like to delete this Attendance?";

    if (!confirm(msg)){
        return false;
    }
    else{
        var batch_id = $(this).attr('batch_id');
        var date = $(this).attr('date');
        var teacher_name = $(this).attr('teacher_name');
        var url = 'index.php?module=pms_batch&_spAction=deleteAttendance'
                + '&showHTML=0';
        $.get(url, {batch_id:batch_id, date:date} ,function(html){
            alert('Deleted Successfully!');
            window.location.reload(true);
        });
    }
});

cpm.pms.batch.editStudentGrade = function(){
    var title = "Edit Student Grade";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('pms_batch#pms_studentGrade', 'pms_batch');
                window.location.reload(true);
            });
        }
    }

    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);
}

cpm.pms.batch.deleteStudentGrade = function(){
}

cpm.pms.batch.editAssessmentSurah = function(){
}

cpm.pms.batch.createUpdateEditStudentGrade = function(){
}

cpm.pms.batch.createUpdateStudentGrade = function(){
}

cpm.pms.batch.reloadBatchStudents = function(){
}

cpm.pms.batch.reloadBatchModule = function(){
}

cpm.pms.batch.newAssessmentSurah = function(){
}

cpm.pms.batch.newAssessmentSurahYear = function(){
}

cpm.pms.batch.newAssessmentSurahType = function(){
}

$("#studentFeedback").livequery('click', function (e){
    var title = "Student Feedback";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('pms_batch#pms_feedback', 'pms_batch');
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
                Links.reloadPortalRecords('pms_batch#pms_student_feedback', 'pms_batch');
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 600,500, expObj);
});

$('input#marks').livequery('change', function(){
    var mark = $(this).val();
    var batch_history_id = $(this).closest('tr').attr('id');
    var url = 'index.php?module=pms_batch&_spAction=updateGrade&showHTML=0';
    grade = 'input#fld_' + batch_history_id + '_grade';
    $.get(url, {mark: mark}, function(html){
        $(grade).val(html);
    });

    var url = 'index.php?module=pms_batch&_spAction=updateStudentResult&showHTML=0';
    studentResult = 'input#fld_' + batch_history_id + '_student_result';
    $.get(url, {mark: mark}, function(html){
        $(studentResult).val(html);
    });
});
