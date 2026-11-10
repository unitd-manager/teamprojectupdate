Util.createCPObject('cpm.edukite.achievement');

cpm.edukite.achievement = {
    init: function(){

    //--------------------------- TO SHOW LEFT PANEL BUTTONS DEFAULT ACTIVE -----------------------------
        $(window).load(function(){
            var activeLayout = $('#activeLayout').attr('value');

            if (activeLayout == 'class') {
                $(".classLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn-active.png');
                $(".classLinkedImg").show();
            } else if (activeLayout == 'cohort') {
                $(".cohortLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn-active.png');
                $(".cohertLinkedImg").show();
            } else if (activeLayout == 'student') {
                $(".studentLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/child-btn-active.png');
                $(".childLinkedImg").show();
            }
        });

    //--------------------------- CLASS LINK IN ACHIEVEMENT -----------------------------
        //TO DISPLAY ACHIEVEMENT LIST WHEN CLASS IMAGE IS CLICKED IN LEFT PANEL
        $('.classLinkInAchievement').click(function(e){
            e.preventDefault();
            var src = $(".classLinkInAchievement img").attr('src');
            var newsrc;

            $(".classLinkedImg").show();
            $(".childLinkedImg").hide();
            $(".staffLinkedImg").hide();
            $(".parentLinkedImg").hide();
            $(".cohertLinkedImg").hide();

            $(".classLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn-active.png');
            $(".studentLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/child-btn.png');
            $(".cohortLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png');

            var achievement_id = $(this).attr('achievement_id');
            var notice_id      = $(this).attr('notice_id');
            var url = '/index.php?module=edukite_achievement&_spAction=classList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED CLASS IN RIGHT PANEL
            var url = '/index.php?module=edukite_achievement&_spAction=linkedClassList' +
                      '&showHTML=0';
            $.get(url, {achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                Util.hideProgressInd();
            });
        });

        //TO MOVE CLASS FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.classLinkArrow', function(e){
            e.preventDefault();
            var class_id       = $(this).attr('class_id');
            var achievement_id = $(this).attr('achievement_id');
            var notice_id      = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_achievement&_spAction=linkClassToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');

            $.get(url, {class_id:class_id, achievement_id:achievement_id, notice_id:notice_id} , function(data){
                if(data == ''){
                    alert('Please note No Students are Linked for this Class');
                    Util.hideProgressInd();
                }
                else{
                    //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
                    $("a.classLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

                    $('.rightInfoList').html(data);
                    Util.hideProgressInd();
                }
            });
        });

        //TO REMOVE CLASS FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.classLinkDelete', function(e){
            e.preventDefault();
            var class_id       = $(this).attr('class_id');
            var achievement_id = $(this).attr('achievement_id');
            var notice_id      = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_achievement&_spAction=deleteLinkedClasses' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {class_id:class_id, achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.achievement.reloadLeftPanelClass(achievement_id, notice_id);
            });

        });


    //--------------------------- COHORT LINK IN ACHIEVEMENT -----------------------------
        //TO DISPLAY COHORT LIST WHEN COHORT IMAGE IS CLICKED IN LEFT PANEL
        $('.cohortLinkInAchievement').click(function(e){
            e.preventDefault();
            var src = $(".yearGroupLinkInStudent img").attr('src');
            var newsrc;

            $(".cohertLinkedImg").show();
            $(".classLinkedImg").hide();
            $(".childLinkedImg").hide();
            $(".staffLinkedImg").hide();
            $(".parentLinkedImg").hide();

            $(".cohortLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn-active.png');
            $(".classLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn.png');
            $(".studentLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/child-btn.png');

            var achievement_id = $(this).attr('achievement_id');
            var notice_id      = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_achievement&_spAction=cohortList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED CLASS IN RIGHT PANEL
            var url = '/index.php?module=edukite_achievement&_spAction=linkedCohortList' +
                      '&showHTML=0';
            $.get(url, {achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                Util.hideProgressInd();
            });
        });

        //TO MOVE COHORT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.cohortLinkArrow', function(e){
            e.preventDefault();
            var year_group_id  = $(this).attr('year_group_id');
            var achievement_id = $(this).attr('achievement_id');
            var notice_id      = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_achievement&_spAction=linkCohortToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');

            $.get(url, {year_group_id:year_group_id, achievement_id:achievement_id, notice_id:notice_id} , function(data){
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

        //TO REMOVE COHORT FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.cohortLinkDelete', function(e){
            e.preventDefault();
            var year_group_id  = $(this).attr('year_group_id');
            var achievement_id = $(this).attr('achievement_id');
            var notice_id      = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_achievement&_spAction=deleteLinkedCohort' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {year_group_id:year_group_id, achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.achievement.reloadLeftPanelCohort(achievement_id, notice_id);
            });

        });

    //--------------------------- STUDENT LINK IN ACHIEVEMENT -----------------------------
        //TO DISPLAY STUDENT LIST WHEN STUDENT IMAGE IS CLICKED IN LEFT PANEL
        $('.studentLinkInAchievement').click(function(e){
            e.preventDefault();

            $(".childLinkedImg").show();
            $(".classLinkedImg").hide();
            $(".parentLinkedImg").hide();
            $(".staffLinkedImg").hide();
            $(".cohertLinkedImg").hide();

            $(".studentLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/child-btn-active.png');
            $(".classLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn.png');
            $(".cohortLinkInAchievement img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png');

            var achievement_id = $(this).attr('achievement_id');
            var notice_id = $(this).attr('notice_id');
            var url = '/index.php?module=edukite_achievement&_spAction=studentList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED STUDENT IN RIGHT PANEL
            var url = '/index.php?module=edukite_achievement&_spAction=linkedStudentList' +
                      '&showHTML=0';
            $.get(url, {achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                Util.hideProgressInd();
            });
        });

        //TO MOVE STUDENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.studentLinkArrow', function(e){
            e.preventDefault();
            var student_id  = $(this).attr('student_id');
            var achievement_id = $(this).attr('achievement_id');
            var notice_id = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_achievement&_spAction=linkStudentToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');
            $("a.studentLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

            $.get(url, {student_id:student_id, achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                Util.hideProgressInd();
            });

        });

        //TO REMOVE STUDENT FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.studentLinkDelete', function(e){
            e.preventDefault();
            var student_id     = $(this).attr('student_id');
            var achievement_id = $(this).attr('achievement_id');
            var notice_id      = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_achievement&_spAction=deleteLinkedStudents' +
                      '&showHTML=0';
            Util.showProgressInd();

            $.get(url, {student_id:student_id, achievement_id:achievement_id, notice_id:notice_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.achievement.reloadLeftPanelStudent(achievement_id, notice_id);
            });
        });

        //TO EXPAND CLASS IN Left PANEL
        $('.leftInfoList').on('click', 'a.classLinkExpand', function(e){
            e.preventDefault();
            $('tr#leftPanelExpandedList').remove(); // Removes already expanded data or rows of student records
            var achievement_id = $(this).attr('achievement_id');
            var class_id  = $(this).attr('class_id');
            var notice_id = $(this).attr('notice_id');
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
                var url = '/index.php?module=edukite_achievement&_spAction=expandClassInLeftPanel' + '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {class_id:class_id, notice_id:notice_id, achievement_id:achievement_id} , function(data){
                    $( "<tr id='leftPanelExpandedList'><td colspan='3' class='rightPadding'>" + data + "</td></tr>" ).insertAfter(trObj);
                    Util.hideProgressInd();
                });
            }
        });

        //TO EXPAND CLASS IN RIGHT PANEL
        $('.rightInfoList').on('click', 'a.classLinkExpand', function(e){
            e.preventDefault();
            $('tr#rightPanelExpandedList').remove(); // Removes already expanded data or rows of student records
            var class_id  = $(this).attr('class_id');
            var notice_id = $(this).attr('notice_id');
            var achievement_id = $(this).attr('achievement_id');
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
                var url = '/index.php?module=edukite_achievement&_spAction=expandClassInRightPanel' + '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {class_id:class_id, notice_id:notice_id, achievement_id:achievement_id} , function(data){
                    $( "<tr id='rightPanelExpandedList'><td colspan='4' class='rightPadding'>" + data + "</td></tr>" ).insertAfter(trObj);
                    Util.hideProgressInd();
                });
            }
        });

        //TO EXPAND COHORT IN RIGHT PANEL
        $('.rightInfoList').on('click', 'a.cohortLinkExpand', function(e){
            e.preventDefault();
            $('tr#rightPanelExpandedList').remove(); // Removes already expanded data or rows of student records
            var year_group_id  = $(this).attr('year_group_id');
            var notice_id      = $(this).attr('notice_id');
            var achievement_id = $(this).attr('achievement_id');
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
                var url = '/index.php?module=edukite_achievement&_spAction=expandCohortInRightPanel' + '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {year_group_id:year_group_id, notice_id:notice_id, achievement_id:achievement_id} , function(data){
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
            var achievement_id = $(this).attr('achievement_id');
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
                var url = '/index.php?module=edukite_achievement&_spAction=expandCohortInLeftPanel' + '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {year_group_id:year_group_id, notice_id:notice_id, achievement_id:achievement_id} , function(data){
                    $( "<tr id='leftPanelExpandedList'><td colspan='3' class='rightPadding'>" + data + "</td></tr>" ).insertAfter(trObj);
                    Util.hideProgressInd();
                });
            }
        });

        //TO REMOVE A STUDENT FROM CLASS RIGHT PANEL
        $('.rightInfoList').on('click', 'a.studentInClassLinkDelete', function(e){
            e.preventDefault();
            var achievement_student_id  = $(this).attr('achievement_student_id');
            var achievement_id = $(this).attr('achievement_id');
            var notice_id = $(this).attr('notice_id');
            var student_id = $(this).attr('student_id');

            var url = '/index.php?module=edukite_achievement&_spAction=deleteLinkedStudentsFromClass' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {achievement_student_id:achievement_student_id, notice_id:notice_id, student_id:student_id, achievement_id:achievement_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.achievement.reloadLeftPanelClass(achievement_id, notice_id);
            });
        });

        //TO MOVE STUDENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.classStudentLinkArrow', function(e){
            e.preventDefault();
            var student_id  = $(this).attr('student_id');
            var notice_id = $(this).attr('notice_id');
            var class_id  = $(this).attr('class_id');
            var achievement_id = $(this).attr('achievement_id');

            var url = '/index.php?module=edukite_achievement&_spAction=linkClassStudentToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');
            $.get(url, {student_id:student_id, class_id:class_id, notice_id:notice_id, achievement_id:achievement_id}, function(data){
                $("a.classStudentLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');
                $("a.classLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

                $('.rightInfoList').html(data);
                Util.hideProgressInd();
                //cpm.edukite.notice.reloadLeftPanelStudent(notice_id);
            });
        });

        //TO REMOVE A STUDENT FROM COHORT RIGHT PANEL
        $('.rightInfoList').on('click', 'a.studentInCohortLinkDelete', function(e){
            e.preventDefault();
            var achievement_student_id  = $(this).attr('achievement_student_id');
            var achievement_id = $(this).attr('achievement_id');
            var notice_id = $(this).attr('notice_id');
            var student_id = $(this).attr('student_id');

            var url = '/index.php?module=edukite_achievement&_spAction=deleteLinkedStudentsFromCohort' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {achievement_student_id:achievement_student_id, notice_id:notice_id, student_id:student_id, achievement_id:achievement_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.achievement.reloadLeftPanelCohort(achievement_id, notice_id);
            });
        });

        //TO MOVE COHORT STUDENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.cohortStudentLinkArrow', function(e){
            e.preventDefault();
            var year_group_id  = $(this).attr('year_group_id');
            var notice_id      = $(this).attr('notice_id');
            var student_id  = $(this).attr('student_id');
            var achievement_id = $(this).attr('achievement_id');

            var url = '/index.php?module=edukite_achievement&_spAction=linkCohortStudentToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');

            $.get(url, {year_group_id:year_group_id, notice_id:notice_id, student_id:student_id, achievement_id:achievement_id} , function(data){
                //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
                $("a.cohortStudentLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

                $('.rightInfoList').html(data);
                Util.hideProgressInd();
            });
        });

        $('#achievementHelpBtn').on('click', function(e){
            var title = "";
            e.preventDefault();
            Util.openDialogForLink.call(this, title, '', '', true);
        });

    },

    /* RELOAD LEFT PANEL STUDENT WHEN A STUDENT IS ADDED OR DELETED */
    reloadLeftPanelStudent: function(achievement_id, notice_id){
        var url = '/index.php?module=edukite_achievement&_spAction=studentList&showHTML=0';
        $.get(url, {achievement_id:achievement_id, notice_id:notice_id} , function(data){
            $('.leftInfoList').html(data);
             Util.hideProgressInd();
        });
    },

    reloadLeftPanelClass: function(achievement_id, notice_id){
        var url = '/index.php?module=edukite_achievement&_spAction=classList' +
                  '&showHTML=0';
        Util.showProgressInd();
        $.get(url, {achievement_id:achievement_id, notice_id:notice_id} , function(data){
            $('.leftInfoList').html(data);
            Util.hideProgressInd();
        });
    },

    reloadLeftPanelCohort: function(achievement_id, notice_id){
        var url = '/index.php?module=edukite_achievement&_spAction=cohortList' +
                  '&showHTML=0';
        Util.showProgressInd();
        $.get(url, {achievement_id:achievement_id, notice_id:notice_id} , function(data){
            $('.leftInfoList').html(data);
            Util.hideProgressInd();
        });
    }
}