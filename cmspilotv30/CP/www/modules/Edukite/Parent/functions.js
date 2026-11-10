Util.createCPObject('cpm.edukite.parent');

cpm.edukite.parent = {
    init: function(){
    //--------------------------- STUDENT LINK IN parent-----------------------------
        //TO MOVE STUDENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.studentLinkArrow', function(e){
            e.preventDefault();

            var student_id  = $(this).attr('student_id');
            var parent_id    = $(this).attr('parent_id');

            var url = '/index.php?module=edukite_parent&_spAction=linkStudentToRightPanel' +
                      '&showHTML=0';
            Util.showProgressInd();

            //TO CHANGE THE GREY IMAGE TO RED ARROW IMAGE
            var trObj = $(this).closest('tr');
            $("a.studentLinkArrow img", trObj).attr('src','/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png');

            $.get(url, {student_id:student_id, parent_id:parent_id} , function(data){
                $('.rightInfoList').html(data);
                Util.hideProgressInd();
                //cpm.edukite.parent.reloadLeftPanelStudent(parent_id);
            });

        });

        //TO REMOVE STUDENT FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.studentLinkDelete', function(e){
            e.preventDefault();
            var student_id  = $(this).attr('student_id');
            var parent_id    = $(this).attr('parent_id');

            var url = '/index.php?module=edukite_parent&_spAction=deleteLinkedStudents' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {student_id:student_id, parent_id:parent_id} , function(data){
                $('.rightInfoList').html(data);
                cpm.edukite.parent.reloadLeftPanelStudent(parent_id);
            });
        });

        //TO MOVE ALL STUDENT FROM LEFT TO RIGHT PANEL
        $('.leftInfoList').on('click', 'a.selectAllStudent', function(e){
            e.preventDefault();
            var parent_id = $(this).attr('parent_id');

            msg = "Do you like to move  all students to audience?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_parent&_spAction=linkAllStudentToRightPanel' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {parent_id:parent_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.parent.reloadLeftPanelStudent(parent_id);
                });
            }
        });

        //TO REMOVE ALL STUDENT FROM RIGHT PANEL
        $('.rightInfoList').on('click', 'a.removeAllStudent', function(e){
            e.preventDefault();
            var parent_id = $(this).attr('parent_id');

            msg = "Do you like to remove all students?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var url = '/index.php?module=edukite_parent&_spAction=deleteAllLinkedStudents' +
                          '&showHTML=0';
                Util.showProgressInd();
                $.get(url, {parent_id:parent_id} , function(data){
                    $('.rightInfoList').html(data);
                    cpm.edukite.parent.reloadLeftPanelStudent(parent_id);
                });
            }
        });

        //LEFT PANEL STUDENT SEARCH
        $("#studentSearch a.submit").livequery('click', function(){
            var student_name  = $("#studentSearch input[name='student']").val();
            var parent_id     = $(this).attr('parent_id');
        
        	var url = '/index.php?module=edukite_parent&_spAction=studentDisplayAfterSearch' +
                      '&showHTML=0';
            Util.showProgressInd();
            $.get(url,{student_name: student_name, parent_id: parent_id}, function(html){
                $('.leftInfoList table').html(html);
                Util.hideProgressInd();
            });
        });

        //TO CHANGE FIND BUTTON ON LEFT PANEL WHEN HOVER
        $('#studentSearch img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/find-active.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/find.png');
        });
    },

    /* RELOAD LEFT PANEL WHEN ALL STUDENTS ARE REMOVED OR ADDED*/
    reloadLeftPanelStudent: function(parent_id){
        var url = '/index.php?module=edukite_parent&_spAction=studentList&showHTML=0';
        $.get(url, {parent_id:parent_id} , function(data){
            $('.leftInfoList').html(data);
             Util.hideProgressInd();
        });
    }
}