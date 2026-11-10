Util.createCPObject('cpt.kite');

cpt.kite = {
    init: function(){
        $('.refreshSite').livequery('click', function(){
           	window.location.reload(true);
           	window.location.reload(true);
           	window.location.reload(true);
        });

        $('#dailyActivityForm').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                var mgsalert='Record Saved Successfully';
                alert(mgsalert);

            cpt.kite.reloadDailyActivity();
          },'json');
          return false;
       });

        $("a.contactSchoolLink").livequery('click', function (e){
            e.preventDefault();
            var expObj = {
                wrapperId: '',
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Contact School', 400, 400, expObj);
        });

        $('.parentProfile a').livequery('click', function(e){
            var title = "Parent Profile";
            e.preventDefault();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Updated Successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                    });
                }
            }
            Util.openFormInDialog.call(this, 'parentProfileForm', title, 500, 350, expObj);
        });

        $(".FeedbackButton").livequery('click', function (e){
            e.preventDefault();
            var expObj = {
                wrapperId: '',
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Parent Feedback', 500, 350, expObj);
        });

        //For Home work functionality
        $('.news a').livequery('click', function(){
            window.location.reload(true);
        });

        //For Home work functionality
        $('.staffNews').livequery('click', function(){
            window.location.reload(true);
        });

        //For Home work functionality
        $('.task a').livequery('click', function(){
            var student_id = $(this).attr('student_id');
            var status = $(this).attr('status');
            var archive = $(this).attr('archive');
            $('.news a').removeClass('active');
            $('.task a').addClass('active');

            var url = '/index.php?module=edukiteWeb_notice&_spAction=taskDisplay&showHTML=0';
            $.get(url, {student_id: student_id, status: status, archive: archive}, function(html){
                $('.homeMiddle').html(html);
                    $('.homeMiddle .inner .jqGalleriaSlider').each(function(){
                        var galId = $(this).attr('id');

                        exp = {
                             handle: galId
                            ,width: '380'
                            ,height: '320'
                            ,autoplay: ''
                            ,speed: '5'
                            ,zoom: ''
                            ,showCaption: ''
                            ,thumbnails: ''
                        }
                        cpw.media.relatedImages.run(exp);
                    });
                Util.hideProgressInd();
            });
        });

        /*$('#archiveLink').livequery('click', function(){
            var url = '/index.php?module=edukiteWeb_notice&_spAction=updateSessionStatus&showHTML=0';
            $.get(url, function(html){
                window.location.reload(true);
            });
        });*/

        $('.teacherCommentSubmit').livequery('click', function(){
            var parent = $(this).closest('.innerContent');
            var notice_id = $(this).attr('notice_id');
            var notes = $('#fld_notes').val();
            var url = '/index.php?module=edukiteWeb_notice&_spAction=displayTeacherFeedback&showHTML=0';
            $.get(url, {notice_id:notice_id, notes: notes},  function(html){
                var url = '/index.php?module=edukiteWeb_notice&_spAction=displayTeacherFeedback&showHTML=0';
                $.get(url, {notice_id:notice_id}, function(html){
                    $('#fld_notes', parent).val('');
                    $('.feedbackDisplay', parent).html(html);
                    Util.hideProgressInd();
                });
            });
        });

        $(".readNoticeTask input[type=radio]").livequery('click', function(){
            var parent = $(this).closest('.readNoticeTask');
            var viewed_tagObj = $(this).parents('.readNoticeTask').find('input[type=radio]:checked');
            var viewed_tag = viewed_tagObj.val();
            var recObj = $(this).closest('.readNoticeTask');
            var rec_id = $(recObj).attr('rec_id');
                //alert(viewed_tag);
            var parent = $(this).closest('.innerContent');
            /*if(viewed_tag == 1){
                $('.toggleContent', parent).hide();
            } else {
                $('.toggleContent', parent).show();
            }*/

            var url = '/index.php?module=edukiteWeb_notice&_spAction=updateTaskReadNoticeParent&showHTML=0';
            $.get(url, {viewed_tag: viewed_tag, rec_id: rec_id}, function(json){
            });
        });
    },
    reloadDailyActivity: function(){
        var url = '/index.php?module=edukiteWeb_notice&_spAction=dailyActivityForTeacherForm&showHTML=0';
        $.get(url, function(html){
            $('.dailyActivityForTeacher').html(html);
            Util.hideProgressInd();
        });
    },
}
