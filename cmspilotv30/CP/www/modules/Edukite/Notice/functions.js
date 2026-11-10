Util.createCPObject('cpm.edukite.notice');

cpm.edukite.notice = {
    init: function(){

    //--------------------------- TO SHOW LEFT PANEL BUTTONS DEFAULT ACTIVE -----------------------------
        $(window).load(function(){
            var activeLayout = $('#activeLayout').attr('value');

            if (activeLayout == 'class') {
                $(".classLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn-active.png');
                $(".classLinkedImg").show();
            } else if (activeLayout == 'cohort') {
                $(".cohortLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn-active.png');
                $(".cohertLinkedImg").show();
            } else if (activeLayout == 'student') {
                $(".studentLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/child-btn-active.png');
                $(".childLinkedImg").show();
            } else if (activeLayout == 'staff') {
                $(".staffLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/staff-btn-active.png');
                $(".staffLinkedImg").show();
            }

            $('.row_activity_date img').attr('src','/cmspilotv30/CP/www/themes/Manager/images/date-icon.jpg').show();
            $('.row_expiry_date img').attr('src','/cmspilotv30/CP/www/themes/Manager/images/date-icon.jpg').show();
            $('.row_launch_date img').attr('src','/cmspilotv30/CP/www/themes/Manager/images/date-icon.jpg').show();
        });

    //--------------------------- LAUNCH TO KITE VALIDATIONS -----------------------------
        $('.launchNowImage').livequery('click', function (e){
        //launchNowImage: function(room, rowID, currentValue, reUploadRecord){
            cpm.edukite.notice.reloadLaunchNow();

    		var noticeTitle = $('#fld_title').val();
    		var noticeDescription = $('#fld_description').val();
            var room        = $(this).attr('module');
            var rowID        = $(this).attr('rowID');
            var currentValue = $(this).attr('currentValue');
            var linking      = $(this).attr('linking');
            //alert (linking);

            if (currentValue == 0 && noticeTitle == '') {
                alert('Please enter the notice title')
                return;
            }

            if (currentValue == 0 && noticeDescription == '') {
                alert('Please enter the notice description')
                return;
            }

            var url = $('#scopeRootAlias').val() + "index.php?module=edukite_notice&_spAction=launchNowToKites&showHTML=0";

            var cell = "#txt__launch_now__" + rowID

            //$(cell).html('processing');
            var data = {
                 record_id: rowID
                ,room: room
                ,currentValue: currentValue
            };
            $.post(url, data, function (data) {
                if(data == 'Yes'){
                    alert('Please select an audience before launching your notice to the kites');
                }
                else{
                    $(cell).html(data);
                    Util.alert('Your notice has now been launched to the kites')
                }
            });
            //$('#btnSaveRecord').trigger('click');

        });

        //Homework Summary Popup In Notice
        $('.viewCommentChat').livequery('click', function (e){
            var notice_id = $(this).attr('notice_id');
            var student_id = $(this).attr('student_id');
            var task_student_id = $(this).attr('task_student_id');

            var url = '/index.php?module=edukite_notice&_spAction=viewCommentHistory'
                    + '&notice_id=' + notice_id
                    + '&student_id=' + student_id
                    + '&task_student_id=' + task_student_id
                    + '&showHTML=0';
            var exp = {
                url: url
            };

            Util.openDialogForLink('Student Comments History',  400, 600, 0, exp);
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

        //TO CHANGE ON OFF BUTTON
        $('.row_parent_email_sent').click(function(){
        //var parent_email_sent = $(this).attr('parent_email_sent');
        var parent_email_sent = $('parent_email_sent').val();

            if (parent_email_sent == 1 ) {
                $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/on.jpeg');
            } else {
                 $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/off.jpeg');
            }
        });

        //TO CHANGE LAUNCH TO KITES IMAGE WHEN HOVER
        /*$('.launchNowImage img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/launch-kite-active.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/launch-kite.png');
        });*/

        //TO CHANGE FIND BUTTON ON LEFT PANEL WHEN HOVER
        $('#studentSearch img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/find-active.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/find.png');
        });

        //TO CHANGE PICTURE UPLOAD ICON WHEN HOVER
        $('#media__edukite_notice__picture a.btnSelectMedia img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/upload-icon-hover.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/upload-icon.png');
        });

    //--------------------------- CLASS LINK IN NOTICE-----------------------------
        //TO DISPLAY CLASS LIST WHEN CLASS IMAGE IS CLICKED IN LEFT PANEL
        $('.classLinkInNotice').click(function(e){
            e.preventDefault();

            $(".classLinkedImg").show();
            $(".childLinkedImg").hide();
            $(".parentLinkedImg").hide();
            $(".staffLinkedImg").hide();
            $(".cohertLinkedImg").hide();

            $(".classLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn-active.png');
            $(".studentLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/child-btn.png');
            $(".cohortLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png');
            $(".staffLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/staff-btn.png');

            var notice_id = $(this).attr('notice_id');
            var status = $(this).attr('status');
            //alert(notice_id);
            var url = '/index.php?module=edukite_notice&_spAction=classList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {notice_id:notice_id, status:status} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED CLASS IN RIGHT PANEL
            var url = '/index.php?module=edukite_notice&_spAction=linkedClassList' +
                      '&showHTML=0';
            $.get(url, {notice_id:notice_id, status:status} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                Util.hideProgressInd();
            });
        });

        //TO MOVE CLASS FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.classLinkArrow', function(e){
            e.preventDefault();
            var class_id  = $(this).attr('class_id');
            var notice_id = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=linkClassToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');

            $.get(url, {class_id:class_id, notice_id:notice_id} , function(data){
                if(data == ''){
                    alert('Please note No Students are Linked for this Class');
                    Util.hideProgressInd();
                    //cpm.edukite.notice.reloadLeftPanelClass(notice_id);
                }
                else{
                    //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
                    $("a.classLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

                    $('.rightInfoList').html(data);
                    Util.hideProgressInd();
                    //cpm.edukite.notice.reloadLeftPanelClass(notice_id);
                }
            });
        });

        //TO MOVE STUDENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.classStudentLinkArrow', function(e){
            e.preventDefault();
            var student_id  = $(this).attr('student_id');
            var notice_id = $(this).attr('notice_id');
            var class_id  = $(this).attr('class_id');

            var url = '/index.php?module=edukite_notice&_spAction=linkClassStudentToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');
            $.get(url, {student_id:student_id, class_id:class_id, notice_id:notice_id}, function(data){
                $("a.classStudentLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');
                $("a.classLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

                $('.rightInfoList').html(data);
                Util.hideProgressInd();
                //cpm.edukite.notice.reloadLeftPanelStudent(notice_id);
            });
        });


        //TO REMOVE CLASS FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.classLinkDelete', function(e){
            e.preventDefault();
            var class_id  = $(this).attr('class_id');
            var notice_id = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=deleteLinkedClasses' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {class_id:class_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.notice.reloadLeftPanelClass(notice_id);
            });

        });

        //TO EXPAND CLASS IN RIGHT PANEL
        $('.rightInfoList').on('click', 'a.classLinkExpand', function(e){
            e.preventDefault();
            $('tr#rightPanelExpandedList').remove(); // Removes already expanded data or rows of student records
            var class_id  = $(this).attr('class_id');
            var notice_id = $(this).attr('notice_id');
            var status = $(this).attr('status');
            var trObj = $(this).closest('tr');

            if (trObj.attr('childrenShown') == 1){
                //$('.classLinkExpand').addClass('plus');
                $('.classLinkExpand ', trObj).removeClass('minus');
                trObj.attr('childrenShown', 0);
            } else {
                //to expand class.
                $('.rightInfoList .classLinkExpand ').removeClass('minus');
                $('.rightInfoList tr').attr('childrenShown', 0);
                trObj.attr('childrenShown', 1)
                $('.classLinkExpand ', trObj).addClass('minus');
                var url = '/index.php?module=edukite_notice&_spAction=expandClassInRightPanel' + '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {class_id:class_id, notice_id:notice_id, status:status} , function(data){
                    $( "<tr id='rightPanelExpandedList'><td colspan='4' class='rightPadding'>" + data + "</td></tr>" ).insertAfter(trObj);
                    Util.hideProgressInd();
                });
            }
        });

        //TO EXPAND CLASS IN Left PANEL
        $('.leftInfoList').on('click', 'a.classLinkExpand', function(e){
            e.preventDefault();
            $('tr#leftPanelExpandedList').remove(); // Removes already expanded data or rows of student records
            var class_id  = $(this).attr('class_id');
            var notice_id = $(this).attr('notice_id');
            var status = $(this).attr('status');
            var trObj = $(this).closest('tr');

            if (trObj.attr('childrenShown') == 1){
                //$('.classLinkExpand').addClass('plus');
                $('.classLinkExpand ', trObj).removeClass('minus');
                trObj.attr('childrenShown', 0);
            } else {
                //to expand class.
                $('.leftInfoList .classLinkExpand ').removeClass('minus');
                $('.leftInfoList tr').attr('childrenShown', 0);
                trObj.attr('childrenShown', 1)
                $('.classLinkExpand ', trObj).addClass('minus');
                var url = '/index.php?module=edukite_notice&_spAction=expandClassInLeftPanel' + '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {class_id:class_id, notice_id:notice_id, status:status} , function(data){
                    $( "<tr id='leftPanelExpandedList'><td colspan='3' class='rightPadding'>" + data + "</td></tr>" ).insertAfter(trObj);
                    Util.hideProgressInd();
                });
            }
        });

        //TO MOVE ALL CLASS IN LEFT PANEL TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.selectAllClass', function(e){
            e.preventDefault();
            var notice_id = $(this).attr('notice_id');

            msg = "Do you like to move all classes to Audience (Right Panel)?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_notice&_spAction=linkAllClassToRightPanel' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {notice_id:notice_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.notice.reloadLeftPanelClass(notice_id);
                });
            }
        });

        //TO REMOVE ALL CLASS FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.removeAllClass', function(e){
            e.preventDefault();
            var notice_id = $(this).attr('notice_id');

            msg = "Do you like to remove all classes?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_notice&_spAction=deleteAllLinkedClasses' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {notice_id:notice_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.notice.reloadLeftPanelClass(notice_id);
                });
            }

        });

        //TO REMOVE A STUDENT FROM CLASS RIGHT PANEL
        $('.rightInfoList').on('click', 'a.studentInClassLinkDelete', function(e){
            e.preventDefault();
            var notice_student_id  = $(this).attr('notice_student_id');
            var notice_id = $(this).attr('notice_id');
            var student_id = $(this).attr('student_id');
            var class_id = $(this).attr('class_id');

            var url = '/index.php?module=edukite_notice&_spAction=deleteLinkedStudentsFromClass' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {notice_student_id:notice_student_id, notice_id:notice_id, class_id:class_id, student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.notice.reloadLeftPanelClass(notice_id);
            });
        });

    //--------------------------- STUDENT LINK IN NOTICE-----------------------------
        //TO DISPLAY STUDENT LIST WHEN STUDENT IMAGE IS CLICKED IN LEFT PANEL
        $('.studentLinkInNotice').click(function(e){
            e.preventDefault();

            $(".childLinkedImg").show();
            $(".classLinkedImg").hide();
            $(".parentLinkedImg").hide();
            $(".staffLinkedImg").hide();
            $(".cohertLinkedImg").hide();

            $(".studentLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/child-btn-active.png');
            $(".classLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn.png');
            $(".staffLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/staff-btn.png');
            $(".cohortLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png');

            var notice_id = $(this).attr('notice_id');
            var status = $(this).attr('status');
            //alert(notice_id);
            var url = '/index.php?module=edukite_notice&_spAction=studentList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {notice_id:notice_id, status:status} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED STUDENT IN RIGHT PANEL
            var url = '/index.php?module=edukite_notice&_spAction=linkedStudentList' +
                      '&showHTML=0';
            $.get(url, {notice_id:notice_id, status:status} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                cpm.edukite.notice.reloadLeftPanelStudent(notice_id, status);
            });
        });

        //TO MOVE STUDENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.studentLinkArrow', function(e){
            e.preventDefault();
            var student_id  = $(this).attr('student_id');
            var notice_id = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=linkStudentToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');
            $("a.studentLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

            $.get(url, {student_id:student_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                Util.hideProgressInd();
                //cpm.edukite.notice.reloadLeftPanelStudent(notice_id);
            });

        });

        //TO REMOVE STUDENT FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.studentLinkDelete', function(e){
            e.preventDefault();
            var student_id  = $(this).attr('student_id');
            var notice_id = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=deleteLinkedStudents' +
                      '&showHTML=0';
            Util.showProgressInd();

            $.get(url, {student_id:student_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.notice.reloadLeftPanelStudent(notice_id);
            });
        });

        //TO MOVE ALL STUDENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.selectAllStudent', function(e){
            e.preventDefault();
            var notice_id = $(this).attr('notice_id');

            msg = "Do you like to move  all students to audience?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_notice&_spAction=linkAllStudentToRightPanel' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {notice_id:notice_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.notice.reloadLeftPanelStudent(notice_id);
                });
            }
        });

        //TO REMOVE ALL STUDENT FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.removeAllStudent', function(e){
            e.preventDefault();
            var notice_id = $(this).attr('notice_id');

            msg = "Do you like to remove all students?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_notice&_spAction=deleteAllLinkedStudents' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {notice_id:notice_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.notice.reloadLeftPanelStudent(notice_id);
                });
            }
        });

    //--------------------------- STAFF LINK IN NOTICE-----------------------------
        //TO DISPLAY STAFF LIST WHEN STAFF IMAGE IS CLICKED IN LEFT PANEL
        $('.staffLinkInNotice').click(function(e){
            e.preventDefault();

            $(".childLinkedImg").hide();
            $(".classLinkedImg").hide();
            $(".parentLinkedImg").hide();
            $(".staffLinkedImg").show();
            $(".cohertLinkedImg").hide();

            $(".staffLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/staff-btn-active.png');
            $(".classLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn.png');
            $(".studentLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/child-btn.png');
            $(".cohortLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png');

            var notice_id = $(this).attr('notice_id');
            var status = $(this).attr('status');
            //alert(status);
            var url = '/index.php?module=edukite_notice&_spAction=staffList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {notice_id:notice_id, status:status} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED STAFF IN RIGHT PANEL
            var url = '/index.php?module=edukite_notice&_spAction=linkedStaffList' +
                      '&showHTML=0';
            $.get(url, {notice_id:notice_id, status:status} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                cpm.edukite.notice.reloadLeftPanelStaff(notice_id, status);
            });
        });

        //TO MOVE STAFF FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.staffLinkArrow', function(e){
            e.preventDefault();
            var teacher_id  = $(this).attr('teacher_id');
            var notice_id   = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=linkStaffToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');
            $("a.staffLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

            $.get(url, {teacher_id:teacher_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                Util.hideProgressInd();
                cpm.edukite.notice.reloadLeftPanelStaff(notice_id);
            });

        });

        //TO REMOVE STAFF FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.staffLinkDelete', function(e){
            e.preventDefault();
            var teacher_id  = $(this).attr('teacher_id');
            var notice_id   = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=deleteLinkedStaff' +
                      '&showHTML=0';
            Util.showProgressInd();

            $.get(url, {teacher_id:teacher_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.notice.reloadLeftPanelStaff(notice_id);
            });
        });

        //TO MOVE ALL STAFF FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.selectAllStaff', function(e){
            e.preventDefault();
            var notice_id = $(this).attr('notice_id');

            msg = "Do you want all staff members to receive this notice?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_notice&_spAction=linkAllStaffToRightPanel' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {notice_id:notice_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.notice.reloadLeftPanelStaff(notice_id);
                });
            }
        });

        //TO REMOVE ALL STAFF FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.removeAllStaff', function(e){
            e.preventDefault();
            var notice_id = $(this).attr('notice_id');

            msg = "Do you like to remove all staffs?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_notice&_spAction=deleteAllLinkedStaff' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {notice_id:notice_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.notice.reloadLeftPanelStaff(notice_id);
                });
            }
        });

    //--------------------------- COHORT LINK IN NOTICE-----------------------------
        $('.cohortLinkInNotice').click(function(e){
            e.preventDefault();

            $(".cohertLinkedImg").show();
            $(".childLinkedImg").hide();
            $(".classLinkedImg").hide();
            $(".parentLinkedImg").hide();
            $(".staffLinkedImg").hide();

            $(".cohortLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn-active.png');
            $(".classLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn.png');
            $(".studentLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/child-btn.png');
            $(".staffLinkInNotice img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/staff-btn.png');

            var notice_id = $(this).attr('notice_id');
            var status = $(this).attr('status');
            //alert(notice_id);
            var url = '/index.php?module=edukite_notice&_spAction=cohortList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {notice_id:notice_id, status:status} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED YEAR GROUP IN RIGHT PANEL
            var url = '/index.php?module=edukite_notice&_spAction=linkedCohortList' +
                      '&showHTML=0';
            $.get(url, {notice_id:notice_id, status:status} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                Util.hideProgressInd();
            });
        });

        //TO MOVE COHORT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.cohortLinkArrow', function(e){
            e.preventDefault();
            var year_group_id  = $(this).attr('year_group_id');
            var notice_id      = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=linkCohortToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');

            $.get(url, {year_group_id:year_group_id, notice_id:notice_id} , function(data){
                if(data == ''){
                    alert('Please note No Students are Linked for this Cohort');
                    Util.hideProgressInd();
                }
                else{
                    //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
                    $("a.cohortLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

                    $('.rightInfoList').html(data);
                    Util.hideProgressInd();
                }
            });
        });

        //TO MOVE COHORT STUDENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.cohortStudentLinkArrow', function(e){
            e.preventDefault();
            var year_group_id  = $(this).attr('year_group_id');
            var notice_id      = $(this).attr('notice_id');
            var student_id  = $(this).attr('student_id');

            var url = '/index.php?module=edukite_notice&_spAction=linkCohortStudentToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');

            $.get(url, {year_group_id:year_group_id, notice_id:notice_id, student_id:student_id} , function(data){
                //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
                $("a.cohortStudentLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

                $('.rightInfoList').html(data);
                Util.hideProgressInd();
            });
        });

        //TO REMOVE COHORT FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.cohortLinkDelete', function(e){
            e.preventDefault();
            var year_group_id  = $(this).attr('year_group_id');
            var notice_id      = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=deleteLinkedCohort' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {year_group_id:year_group_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.notice.reloadLeftPanelCohort(notice_id);
            });

        });

        //TO REMOVE A STUDENT FROM COHORT RIGHT PANEL
        $('.rightInfoList').on('click', 'a.studentInCohortLinkDelete', function(e){
            e.preventDefault();
            var notice_student_id  = $(this).attr('notice_student_id');
            var notice_id = $(this).attr('notice_id');
            var student_id = $(this).attr('student_id');
            var year_group_id = $(this).attr('year_group_id');

            var url = '/index.php?module=edukite_notice&_spAction=deleteLinkedStudentsFromCohort' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {notice_student_id:notice_student_id, notice_id:notice_id, year_group_id:year_group_id, student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.notice.reloadLeftPanelCohort(notice_id);
            });
        });

        //TO EXPAND COHORT IN RIGHT PANEL
        $('.rightInfoList').on('click', 'a.cohortLinkExpand', function(e){
            e.preventDefault();
            $('tr#rightPanelExpandedList').remove(); // Removes already expanded data or rows of student records
            var year_group_id  = $(this).attr('year_group_id');
            var notice_id      = $(this).attr('notice_id');
            var status = $(this).attr('status');
            var trObj          = $(this).closest('tr');

            if (trObj.attr('childrenShown') == 1){
                $('.cohortLinkExpand ', trObj).removeClass('minus');
                trObj.attr('childrenShown', 0);
            } else {
                //to expand class.
                $('.rightInfoList .cohortLinkExpand ').removeClass('minus');
                $('.rightInfoList tr').attr('childrenShown', 0);
                trObj.attr('childrenShown', 1)
                $('.cohortLinkExpand ', trObj).addClass('minus');
                var url = '/index.php?module=edukite_notice&_spAction=expandCohortInRightPanel' + '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {year_group_id:year_group_id, notice_id:notice_id, status:status} , function(data){
                    $( "<tr id='rightPanelExpandedList'><td colspan='4' class='rightPadding'>" + data + "</td></tr>" ).insertAfter(trObj);
                    Util.hideProgressInd();
                });
            }
        });

        //TO EXPAND COHORT IN LEFT PANEL
        $('.leftInfoList').on('click', 'a.cohortLinkExpand', function(e){
            e.preventDefault();
            $('tr#leftPanelExpandedList').remove(); // Removes already expanded data or rows of student records
            var year_group_id  = $(this).attr('year_group_id');
            var notice_id      = $(this).attr('notice_id');
            var status = $(this).attr('status');
            var trObj          = $(this).closest('tr');

            if (trObj.attr('childrenShown') == 1){
                $('.cohortLinkExpand ', trObj).removeClass('minus');
                trObj.attr('childrenShown', 0);
            } else {
                //to expand class.
                $('.leftInfoList .cohortLinkExpand ').removeClass('minus');
                $('.leftInfoList tr').attr('childrenShown', 0);
                trObj.attr('childrenShown', 1)
                $('.cohortLinkExpand ', trObj).addClass('minus');
                var url = '/index.php?module=edukite_notice&_spAction=expandCohortInLeftPanel' + '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {year_group_id:year_group_id, notice_id:notice_id, status:status} , function(data){
                    $( "<tr id='leftPanelExpandedList'><td colspan='3' class='rightPadding'>" + data + "</td></tr>" ).insertAfter(trObj);
                    Util.hideProgressInd();
                });
            }
        });

        //TO MOVE ALL CLASS IN LEFT PANEL TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.selectAllCohort', function(e){
            e.preventDefault();
            var notice_id = $(this).attr('notice_id');

            msg = "Do you like to move all cohort to Audience (Right Panel)?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_notice&_spAction=linkAllCohortToRightPanel' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {notice_id:notice_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.notice.reloadLeftPanelCohort(notice_id);
                });
            }
        });

        //TO REMOVE ALL CLASS FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.removeAllCohort', function(e){
            e.preventDefault();
            var notice_id = $(this).attr('notice_id');

            msg = "Do you like to remove all cohort?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_notice&_spAction=deleteAllLinkedCohort' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {notice_id:notice_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.notice.reloadLeftPanelCohort(notice_id);
                });
            }

        });

        //LEFT PANEL STUDENT SEARCH
        $("#studentSearch a.submit").livequery('click', function(){
            var student_name = $("#studentSearch input[name='student']").val();
            var notice_id    = $(this).attr('notice_id');

        	var url = '/index.php?module=edukite_notice&_spAction=studentDisplayAfterSearch' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url,{student_name: student_name, notice_id: notice_id}, function(html){
                $('.leftInfoList table').html(html);
                Util.hideProgressInd();
            });
        });

        //LEFT PANEL STAFF SEARCH
        $("#staffSearch a.submit").livequery('click', function(){
            var staff_name = $("#staffSearch input[name='staff']").val();
            var notice_id    = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=staffDisplayAfterSearch' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url,{staff_name: staff_name, notice_id: notice_id}, function(html){
                $('.leftInfoList table').html(html);
                Util.hideProgressInd();
            });
        });

        //LIST ACHIEVEMENT SEARCH
        $("#achievementSearch a.submit").livequery('click', function(){
            var achievement_id = $(this).attr('achievement_id');
            var notice_id      = $(this).attr('notice_id');
            var achievement    = $("#achievementSearch input[name='achievement']").val();

        	var url = '/index.php?module=edukite_notice&_spAction=achievementDisplayAfterSearch' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url,{achievement: achievement, achievement_id: achievement_id, notice_id: notice_id}, function(html){
                $('.achievementListView').html(html);
                Util.hideProgressInd();
            });
        });

        var timeoutId2;
        /* AUTO UPDATE NOTICE TITLE FIELD */
        $(".m-edukite_notice input[name=title]").livequery("keyup", function(){
            var title = $(this).val();
            var notice_id   = $(this).attr('id');
            //alert(notice_id);

            /*var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';

            $.get(url, {notice_id: notice_id, title: title}, function(){
                Util.hideProgressInd();
            });*/

            clearTimeout(timeoutId2);
            timeoutId2 = setTimeout(function() {
            // Runs 1 second (1000 ms) after the last change
                var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';
                $.post(url, {notice_id: notice_id, title: title}, function(){
                    Util.hideProgressInd();
                });
            }, 1000);
        });

        var timeoutId;
        /* AUTO UPDATE NOTICE DESCRIPTION FIELD */
        $(".m-edukite_notice #fld_description").livequery("keyup", function(){
            var description = $(this).val();
            var notice_id   = $(this).attr('class');

            /*var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';

            $.get(url, {notice_id: notice_id, description: description}, function(){
                Util.hideProgressInd();
            });*/

            clearTimeout(timeoutId);
            timeoutId = setTimeout(function() {
            // Runs 1 second (1000 ms) after the last change
                var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';

                $.post(url, {notice_id: notice_id, description: description}, function(){
                    Util.hideProgressInd();
                    /*var mgsalert2='Please note, hit the Save button in the top to save the record, you might lose information if it is not saved.';
                    var n = noty({
                        text: mgsalert2,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 10000,
                    });*/
                });
            }, 1000);
        });

        /* AUTO UPDATE NOTICE WEB LINKS FIELD */
        var timeoutId3;
        $(".m-edukite_notice #fld_links").livequery("keyup", function(){
            var web_links = $(this).val();
            var notice_id   = $(this).attr('class');

            /*var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';

            $.get(url, {notice_id: notice_id, web_links: web_links}, function(){
                Util.hideProgressInd();
            });*/

            clearTimeout(timeoutId3);
            timeoutId3 = setTimeout(function() {
            // Runs 1 second (1000 ms) after the last change
                var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';
                $.post(url, {notice_id: notice_id, web_links: web_links}, function(){
                    Util.hideProgressInd();
                });
            }, 1000);
        });

        /* AUTO UPDATE NOTICE YOUTUBE FIELD */
        var timeoutId4;
        $(".m-edukite_notice #fld_youtube_links").livequery("keyup", function(){
            var youtube_links = $(this).val();
            var notice_id   = $(this).attr('class');

            /*var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';

            $.get(url, {notice_id: notice_id, youtube_links: youtube_links}, function(){
                Util.hideProgressInd();
            });*/

            clearTimeout(timeoutId4);
            timeoutId4 = setTimeout(function() {
            // Runs 1 second (1000 ms) after the last change
                var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';
                $.post(url, {notice_id: notice_id, youtube_links: youtube_links}, function(){
                    Util.hideProgressInd();
                });
            }, 1000);
        });

        /* AUTO UPDATE NOTICE SUBJECT FIELD */
        var timeoutId5;
        $(".m-edukite_notice #fld_subject_id").livequery("keyup", function(){
            var subject_id = $(this).val();
            var notice_id   = $(this).attr('class');

            /*var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';

            $.get(url, {notice_id: notice_id, subject_id: subject_id}, function(){
                Util.hideProgressInd();
            });*/

            clearTimeout(timeoutId5);
            timeoutId5 = setTimeout(function() {
            // Runs 1 second (1000 ms) after the last change
                var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';
                $.post(url, {notice_id: notice_id, subject_id: subject_id}, function(){
                    Util.hideProgressInd();
                });
            }, 1000);
        });

        /* AUTO UPDATE NOTICE EXPIRY DATE FIELD */
        var timeoutId6;
        $(".m-edukite_notice #fld_expiry_date").livequery("keyup", function(){
            var expiry_date = $(this).val();
            var notice_id   = $(this).attr('fldid');

            /*var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';

            $.get(url, {notice_id: notice_id, expiry_date: expiry_date}, function(){
                Util.hideProgressInd();
            });*/

            clearTimeout(timeoutId6);
            timeoutId6 = setTimeout(function() {
            // Runs 1 second (1000 ms) after the last change
                var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';
                $.post(url, {notice_id: notice_id, expiry_date: expiry_date}, function(){
                    Util.hideProgressInd();
                });
            }, 1000);
        });

        /* AUTO UPDATE NOTICE ACTIVITY DATE FIELD */
        var timeoutId7;
        $(".m-edukite_notice #fld_activity_date").livequery("keyup", function(){
            var activity_date = $(this).val();
            var notice_id   = $(this).attr('fldid');

            /*var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';

            $.get(url, {notice_id: notice_id, activity_date: activity_date}, function(){
                Util.hideProgressInd();
            });*/

            clearTimeout(timeoutId7);
            timeoutId7 = setTimeout(function() {
            // Runs 1 second (1000 ms) after the last change
                var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';
                $.post(url, {notice_id: notice_id, activity_date: activity_date}, function(){
                    Util.hideProgressInd();
                });
            }, 1000);

        });

        /* AUTO UPDATE NOTICE LAUNCH DATE FIELD */
        var timeoutId8;
        $(".m-edukite_notice #fld_launch_date").livequery("keyup", function(){
            var launch_date = $(this).val();
            var notice_id   = $(this).attr('fldid');

            /*var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';

            $.get(url, {notice_id: notice_id, launch_date: launch_date}, function(){
                Util.hideProgressInd();
            });*/

            clearTimeout(timeoutId8);
            timeoutId8 = setTimeout(function() {
            // Runs 1 second (1000 ms) after the last change
                var url = '/index.php?module=edukite_notice&_spAction=autoUpdateFields&showHTML=0';
                $.post(url, {notice_id: notice_id, launch_date: launch_date}, function(){
                    Util.hideProgressInd();
                });
            }, 1000);
        });

	    /*** ACHIEVEMENTS PROCESSING ******/
        $('.achievementListView').on('click', 'a.achievementLinkArrow', function(e){
            e.preventDefault();
            var notice_id 		= $(this).attr('notice_id');
            var achievement_id  = $(this).attr('achievement_id');

            var url = '/index.php?module=edukite_notice&_spAction=createAchievementHistoryRecord&showHTML=0';
            Util.showProgressInd();

            var trObj = $(this).closest('tr');
            $("a.achievementLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/achievement-linked.png');

            $.get(url,{notice_id: notice_id, achievement_id: achievement_id}, function(data){
                $('.achievementListView').html(data);
                Util.hideProgressInd();
            });
        });

        $('.achievementListView').on('click', 'a.achievementLinkedArrow', function(e){
            e.preventDefault();
            var notice_id 		= $(this).attr('notice_id');
            var achievement_id  = $(this).attr('achievement_id');

            var url = '/index.php?module=edukite_notice&_spAction=deleteAchievementHistoryRecord&showHTML=0';
            Util.showProgressInd();

            var trObj = $(this).closest('tr');
            $("a.achievementLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/achievement-not-linked.png');

            $.get(url,{notice_id: notice_id, achievement_id: achievement_id}, function(data){
                $('.achievementListView').html(data);
                Util.hideProgressInd();
            });
        });

        //ACHIEVEMENT SUB CATEGORY LIST BY CATEGORY
        $(".category").livequery('click', function(){
            $('table.subCategory').remove();
            var achievement_id  = $(this).attr('achievement_id');
            var notice_id       = $(this).attr('notice_id');
            var student_id       = $(this).attr('student_id');
            var parent = $(this).closest('table.categorySubCategoryLink td');
            var trObj = $(this).closest('tr.categoryTitle');
            $('.category').css({"font-weight":"normal"});
            $('.category', parent).css({"font-weight":"bold"});
            $(trObj).css({"background-color":"#D9E8CC"});;

            var change_arrow  = $("a.achievementSubHeadLink", trObj).attr('changedArrow');
            $("a.achievementSubHeadLink").attr('changedArrow','');
            $("a.achievementSubHeadLink img").attr('src','/cmspilotv30/CP/www/themes/Manager/images/plus.png');
            if(change_arrow == 1){
                $("a.achievementSubHeadLink img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/plus.png');
                $("a.achievementSubHeadLink", trObj).attr('changedArrow','');
            } else {
                $("a.achievementSubHeadLink img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/minus.png');
                $("a.achievementSubHeadLink", trObj).attr('changedArrow','1');

                var url = '/index.php?module=edukite_notice&_spAction=achievementSubCategoryDisplay' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url,{notice_id: notice_id, achievement_id: achievement_id, student_id:student_id}, function(html){
                    //$('.subCategory', parent).html(html);
                    $( "<tr class='categorySubCategoryLink2'>"
                       +"<td colspan=3 style='padding:0'>"
                       +"<table class='subCategory'>" + html + "</table></td></tr>").insertAfter(trObj);
                    Util.hideProgressInd();
                });
            }
        });

        $(".categoryHead").livequery('click', function(){
            var achievement_id = $(this).attr('achievement_id');
            var trObj = $(this).closest('tr.categoryHeadTitle');
            var change_arrow  = $("a.achievementHeadLink", trObj).attr('changedArrowHead');

            $("a.achievementHeadLink").attr('changedArrowHead','');
            $("a.achievementHeadLink img").attr('src','/cmspilotv30/CP/www/themes/Manager/images/plus.png');

            if(change_arrow == 1){
                $("a.achievementHeadLink img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/plus.png');
                $("a.achievementHeadLink", trObj).attr('changedArrowHead','');
                $('table.subCategory').remove();
            } else {
                $("a.achievementHeadLink img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/minus.png');
                $("a.achievementHeadLink", trObj).attr('changedArrowHead','1');

                $('.category_'+achievement_id).trigger('click');
            }
        });
    },


    /* RELOAD LEFT PANEL CLASS WHEN A CLASS IS ADDED OR DELETED */
    reloadLeftPanelClass: function(notice_id){
        var url = '/index.php?module=edukite_notice&_spAction=classList' +
                  '&showHTML=0';
        Util.showProgressInd();
        $.get(url, {notice_id:notice_id} , function(data){
            $('.leftInfoList').html(data);
            Util.hideProgressInd();
        });
    },

    /* RELOAD LEFT PANEL COHORT WHEN A COHORT IS ADDED OR DELETED */
    reloadLeftPanelCohort: function(notice_id){
        var url = '/index.php?module=edukite_notice&_spAction=cohortList' +
                  '&showHTML=0';
        Util.showProgressInd();
        $.get(url, {notice_id:notice_id} , function(data){
            $('.leftInfoList').html(data);
            Util.hideProgressInd();
        });
    },

    /* RELOAD LEFT PANEL STUDENT WHEN A STUDENT IS ADDED OR DELETED */
    reloadLeftPanelStudent: function(notice_id, status){
        var url = '/index.php?module=edukite_notice&_spAction=studentList&showHTML=0';
        $.get(url, {notice_id:notice_id, status:status} , function(data){
            $('.leftInfoList').html(data);
             Util.hideProgressInd();
        });
    },

    /* RELOAD LEFT PANEL STAFF WHEN A STUDENT IS ADDED OR DELETED */
    reloadLeftPanelStaff: function(notice_id, status){
        var url = '/index.php?module=edukite_notice&_spAction=staffList&showHTML=0';
        $.get(url, {notice_id:notice_id, status:status} , function(data){
            $('.leftInfoList').html(data);
             Util.hideProgressInd();
        });
    },

    emailPublishOnOffImage: function(room, rowID, currentValue, reUploadRecord){

        if(reUploadRecord){
            reUpload = 1;
        } else {
            reUpload = 0;
        }

        var url = $('#scopeRootAlias').val() + "index.php?module=edukite_notice&_spAction=emailPublishNoticeRecordByID&showHTML=0";

        var cell = "#txt__parent_email_sent__" + rowID

        //$(cell).html('processing');
        var data = {
             record_id: rowID
            ,room: room
            ,currentValue: currentValue
            ,reUpload: reUpload
        };
        $.post(url, data, function (data) {
            $(cell).html(data);
        });
    },

    chatPublishOnOffImage: function(room, rowID, currentValue, reUploadRecord){

        if(reUploadRecord){
            reUpload = 1;
        } else {
            reUpload = 0;
        }

        var url = $('#scopeRootAlias').val() + "index.php?module=edukite_notice&_spAction=chatPublishNoticeRecordByID&showHTML=0";

        var cell = "#txt__kite_chat__" + rowID

        //$(cell).html('processing');
        var data = {
             record_id: rowID
            ,room: room
            ,currentValue: currentValue
            ,reUpload: reUpload
        };
        $.post(url, data, function (data) {
            $(cell).html(data);
        });
    },

    teacherChatPublishOnOffImage: function(room, rowID, currentValue, reUploadRecord){

        if(reUploadRecord){
            reUpload = 1;
        } else {
            reUpload = 0;
        }

        var url = $('#scopeRootAlias').val() + "index.php?module=edukite_notice&_spAction=teacherChatPublishNoticeRecordByID&showHTML=0";

        var cell = "#txt__teacherKite_chat__" + rowID

        //$(cell).html('processing');
        var data = {
             record_id: rowID
            ,room: room
            ,currentValue: currentValue
            ,reUpload: reUpload
        };
        $.post(url, data, function (data) {
            $(cell).html(data);
        });
    },

    globalKitePublishOnOffImage: function(room, rowID, currentValue, reUploadRecord){

        if(reUploadRecord){
            reUpload = 1;
        } else {
            reUpload = 0;
        }

        var url = $('#scopeRootAlias').val() + "index.php?module=edukite_notice&_spAction=globalKitePublishNoticeRecordByID&showHTML=0";

        var cell = "#txt__global_kite__" + rowID

        //$(cell).html('processing');
        var data = {
             record_id: rowID
            ,room: room
            ,currentValue: currentValue
            ,reUpload: reUpload
        };
        $.post(url, data, function (data) {
            $(cell).html(data);
        });
    },

    homeWorkChatOnOffImage: function(room, rowID, currentValue, reUploadRecord){

        if(reUploadRecord){
            reUpload = 1;
        } else {
            reUpload = 0;
        }

        var url = $('#scopeRootAlias').val() + "index.php?module=edukite_notice&_spAction=homeWorkChatNoticeRecordByID&showHTML=0";

        var cell = "#txt__homerwork_chat__" + rowID

        //$(cell).html('processing');
        var data = {
             record_id: rowID
            ,room: room
            ,currentValue: currentValue
            ,reUpload: reUpload
        };
        $.post(url, data, function (data) {
            $(cell).html(data);
        });
    },

    reloadLaunchNow: function(){
            var url = '/index.php?module=edukite_notice&_spAction=launchNowImageIcon&showHTML=0';
            $.get(url,  function(html){
                //$('#invoicePaymentDetails').html(html);
                Util.hideProgressInd();
            });
    }

}