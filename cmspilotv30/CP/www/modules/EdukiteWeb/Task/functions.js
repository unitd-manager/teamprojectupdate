Util.createCPObject('cpm.edukiteWeb.task');

cpm.edukiteWeb.task = {
    init: function(){
        $('.uploadButton').livequery('click', function (e){
            $('.uploadTask').toggle();
            $('.displayUploadedTask').toggle();
            $('.uploadButton').hide();
        });

        $(".type-button input[name='submit']").livequery('click', function (e){
            var notice_id = $(this).attr('notice_id');
            var student_id = $(this).attr('student_id');
            //cpm.edukiteWeb.task.reloadHomeworkTask(notice_id, student_id);
            Util.hideProgressInd();
            window.setTimeout(function () {
                alert('Saved Successfully')
            }, 1000);
        });

        $("#reload").livequery('click', function (){
            window.location.reload(true);
        });

        $(".postcommentCheckBox input[type=checkbox]").livequery('click', function (e){
            var cboxObj   = $(this);
            if (!cboxObj.attr('checked')){
                $('.addCommentNote').addClass("addCommentRemove");
            }else{
                $('.addCommentNote').removeClass("addCommentRemove");
            }
        });

        $('.myChat .btnSubmit').livequery('click', function(){
            var notice_id = $(this).attr('notice_id');
            var student_id = $(this).attr('student_id');
            var task_student_id = $(this).attr('task_student_id');
            var teacherKite = $(this).attr('teacherKite');
            var comments = $('#fld_comments').val();
            //var url = '/index.php?module=edukiteWeb_task&_spAction=addCommentSubmit&showHTML=0';
            //$.get(url, {notice_id:notice_id, student_id: student_id, comments: comments, task_student_id: task_student_id},  function(html){
            window.setTimeout(function () {
                var url = '/index.php?module=edukiteWeb_task&_spAction=displayComment&showHTML=0';
                $.get(url, {notice_id:notice_id, student_id: student_id, task_student_id: task_student_id, teacherKite: teacherKite}, function(html){
                    $('#fld_comments').val('');
                    $('.commentDisplay').html(html);
                    Util.hideProgressInd();
                });
           	}, 1000);
            //});
        });

    },
    reloadHomeworkTask: function(notice_id, student_id){
        var url = '/index.php?module=edukiteWeb_task&_spAction=myHomework&showHTML=0';
        $.get(url, {notice_id:notice_id, student_id:student_id}, function(html){
            $('.myHomeworkList').html(html);
        });
    }
}
