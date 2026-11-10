Util.createCPObject('cpm.edukite.student');

cpm.edukite.student = {
    init: function(){
    //--------------------------- CLASS LINK IN student-----------------------------
        //TO DISPLAY CLASS LIST WHEN CLASS IMAGE IS CLICKED IN LEFT PANEL
        $('.classLinkInStudent').click(function(e){
            e.preventDefault();
            var src = $(".classLinkInStudent img").attr('src');
            var newsrc;

            $(".classLinkedImg").show();
            $(".childLinkedImg").hide();
            $(".staffLinkedImg").hide();
            $(".parentLinkedImg").hide();
            $(".cohertLinkedImg").hide();

            $(".classLinkInStudent img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn-active.png');
            $(".parentLinkInStudent img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/parent-btn.png');
            $(".cohortLinkInStudent img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png');

            var student_id = $(this).attr('student_id');
            //alert(student_id);
            var url = '/index.php?module=edukite_student&_spAction=classList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {student_id:student_id} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED CLASS IN RIGHT PANEL
            var url = '/index.php?module=edukite_student&_spAction=linkedClassList' +
                      '&showHTML=0';
            $.get(url, {student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                Util.hideProgressInd();
            });
        });

        //TO DISPLAY CLASS LIST WHEN CLASS IMAGE IS CLICKED IN LEFT PANEL
        $('.cohortLinkInStudent').click(function(e){
            e.preventDefault();
            var src = $(".yearGroupLinkInStudent img").attr('src');
            var newsrc;

            $(".cohertLinkedImg").show();
            $(".classLinkedImg").hide();
            $(".childLinkedImg").hide();
            $(".staffLinkedImg").hide();
            $(".parentLinkedImg").hide();

            $(".cohortLinkInStudent img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn-active.png');
            $(".classLinkInStudent img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn.png');
            $(".parentLinkInStudent img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/parent-btn.png');

            var student_id = $(this).attr('student_id');
            //alert(student_id);
            var url = '/index.php?module=edukite_student&_spAction=cohortList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {student_id:student_id} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED CLASS IN RIGHT PANEL
            var url = '/index.php?module=edukite_student&_spAction=linkedCohortList' +
                      '&showHTML=0';
            $.get(url, {student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                Util.hideProgressInd();
            });
        });

        //TO MOVE CLASS FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.classLinkArrow', function(e){
            e.preventDefault();
            var class_id   = $(this).attr('class_id');
            var student_id = $(this).attr('student_id');

            var url = '/index.php?module=edukite_student&_spAction=linkClassToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');
            $("a.classLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

            $.get(url, {class_id:class_id, student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                Util.hideProgressInd();
                //cpm.edukite.student.reloadLeftPanelClass(student_id);
            });
        });

        //TO REMOVE CLASS FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.classLinkDelete', function(e){
            e.preventDefault();
            var class_id  = $(this).attr('class_id');
            var student_id = $(this).attr('student_id');

            var url = '/index.php?module=edukite_student&_spAction=deleteLinkedClasses' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {class_id:class_id, student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.student.reloadLeftPanelClass(student_id);
                Util.hideProgressInd();
            });

        });

        //TO EXPAND CLASS IN RIGHT PANEL
        $('.rightInfoList').on('click', 'a.classLinkExpand', function(e){
            e.preventDefault();
            $('tr#rightPanelExpandedList').remove(); // Removes already expanded data or rows of student records
            var class_id  = $(this).attr('class_id');
            var student_id = $(this).attr('student_id');
            var trObj = $(this).closest('tr');

            if (trObj.attr('childrenShown') == 1){
                trObj.attr('childrenShown', 0)
                $('.classLinkExpand ', trObj).removeClass('minus');
            } else {
                trObj.attr('childrenShown', 1)
                $('.classLinkExpand ', trObj).addClass('minus');
                var url = '/index.php?module=edukite_student&_spAction=expandClassInRightPanel' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {class_id:class_id, student_id:student_id} , function(data){
                    $( "<tr id='rightPanelExpandedList'><td colspan='2'>" + data + "</td></tr>" ).insertAfter(trObj);
                    Util.hideProgressInd();
                });
            }
        });

        //TO MOVE ALL CLASS IN LEFT PANEL TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.selectAllClass', function(e){
            e.preventDefault();
            var student_id = $(this).attr('student_id');

            msg = "Do you like to move all classes to Audience (Right Panel)?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_student&_spAction=linkAllClassToRightPanel' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {student_id:student_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.student.reloadLeftPanelClass(student_id);
                });
            }
        });

        //TO REMOVE ALL CLASS FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.removeAllClass', function(e){
            e.preventDefault();
            var student_id = $(this).attr('student_id');

            msg = "Do you like to remove all classes?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_student&_spAction=deleteAllLinkedClasses' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {student_id:student_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.student.reloadLeftPanelClass(student_id);
                });
            }

        });
    //--------------------------- PARENT LINK IN NOTICE-----------------------------
        //TO DISPLAY PARENT LIST WHEN PARENT IMAGE IS CLICKED IN LEFT PANEL
        $('.parentLinkInStudent').click(function(e){
            e.preventDefault();

            $(".parentLinkedImg").show();
            $(".classLinkedImg").hide();
            $(".childLinkedImg").hide();
            $(".staffLinkedImg").hide();
            $(".cohertLinkedImg").hide();

            $(".parentLinkInStudent img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/parent-btn-active.png');
            $(".classLinkInStudent img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/class-btn.png');
            $(".cohortLinkInStudent img").attr('src', '/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png');

            var student_id = $(this).attr('student_id');
            //alert(student_id);
            var url = '/index.php?module=edukite_student&_spAction=parentList' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {student_id:student_id} , function(data){
                $('.leftInfoList').html(data);
                $('.leftInfoList').slideDown('slow');
            });

            //TO DISPLAY LINKED PARENT IN RIGHT PANEL
            var url = '/index.php?module=edukite_student&_spAction=linkedParentList' +
                      '&showHTML=0';
            $.get(url, {student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                $('.rightInfoList').slideDown('slow');
                Util.hideProgressInd();
            });
        });

        //TO MOVE PARENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.parentLinkArrow', function(e){
            e.preventDefault();
            var parent_id  = $(this).attr('parent_id');
            var student_id = $(this).attr('student_id');

            var url = '/index.php?module=edukite_student&_spAction=linkParentToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE BLUE IMAGE TO YELLOW ARROW IMAGE
            var trObj = $(this).closest('tr');
            $("a.parentLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

            $.get(url, {parent_id:parent_id, student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                Util.hideProgressInd();
                //cpm.edukite.student.reloadLeftPanelParent(student_id);
            });

        });

        //TO REMOVE PARENT FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.parentLinkDelete', function(e){
            e.preventDefault();
            var parent_id  = $(this).attr('parent_id');
            var student_id = $(this).attr('student_id');

            var url = '/index.php?module=edukite_student&_spAction=deleteLinkedParents' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {parent_id:parent_id, student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.student.reloadLeftPanelParent(student_id);
            });
        });

        //TO MOVE ALL PARENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.selectAllStudent', function(e){
            e.preventDefault();
            var student_id = $(this).attr('student_id');

            msg = "Do you like to move  all parents to audience?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_student&_spAction=linkAllStudentToRightPanel' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {student_id:student_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.student.reloadLeftPanelParent(student_id);
                });
            }
        });

        //TO MOVE YEAR GROUP FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.cohortLinkArrow', function(e){
            e.preventDefault();
            var year_group_id   = $(this).attr('year_group_id');
            var student_id      = $(this).attr('student_id');

            var url = '/index.php?module=edukite_student&_spAction=linkCohortToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');
            $("a.cohortLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

            $.get(url, {year_group_id:year_group_id, student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                Util.hideProgressInd();
            });
        });

        //TO REMOVE YEAR GROUP FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.cohortLinkDelete', function(e){
            e.preventDefault();
            var year_group_id   = $(this).attr('year_group_id');
            var student_id      = $(this).attr('student_id');

            var url = '/index.php?module=edukite_student&_spAction=deleteLinkedCohort' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {year_group_id:year_group_id, student_id:student_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.student.reloadLeftPanelCohort(student_id);
                Util.hideProgressInd();
            });

        });

        //TO CHANGE GO KITE IMAGE WHEN HOVER
        $('.goKite img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/goKite-hover.gif');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/goKite.gif');
        });

        //TO CHANGE PICTURE UPLOAD ICON WHEN HOVER
        $('#media__edukite_student__picture a.btnSelectMedia img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/upload-icon-hover.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/upload-icon.png');
        });

        //TO CHANGE PRINT IMAGE WHEN HOVER
        $('.downloadImg img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/printB.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/printA.png');
        });

        //TO CHANGE FIND BUTTON ON LEFT PANEL WHEN HOVER
        $('#achievementSearch img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/find-active.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/find.png');
        });

        //LIST ACHIEVEMENT SEARCH
        $("#achievementSearch a.submit").livequery('click', function(){
            var student_id     = $(this).attr('student_id');
            var achievement_id = $(this).attr('achievement_id');
            var achievement    = $("#achievementSearch input[name='achievement']").val();
            //alert(achievement_code);

        	var url = '/index.php?module=edukite_student&_spAction=achievementDisplayAfterSearch' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url,{achievement: achievement, achievement_id: achievement_id, student_id: student_id}, function(html){
                $('.achievementListView').html(html);
                Util.hideProgressInd();
            });
        });
    },

    reloadLeftPanelClass: function(student_id){
        var url = '/index.php?module=edukite_student&_spAction=classList' +
                  '&showHTML=0';
        Util.showProgressInd();
        $.get(url, {student_id:student_id} , function(data){
            $('.leftInfoList').html(data);
            Util.hideProgressInd();
        });
    },

    reloadLeftPanelCohort: function(student_id){
        var url = '/index.php?module=edukite_student&_spAction=cohortList' +
                  '&showHTML=0';
        Util.showProgressInd();
        $.get(url, {student_id:student_id} , function(data){
            $('.leftInfoList').html(data);
            Util.hideProgressInd();
        });
    },

    reloadLeftPanelParent: function(student_id){
        var url = '/index.php?module=edukite_student&_spAction=parentList&showHTML=0';
        $.get(url, {student_id:student_id} , function(data){
            $('.leftInfoList').html(data);
             Util.hideProgressInd();
        });
    }

}