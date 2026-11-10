Util.createCPObject('cpm.edukiteWeb.notice');

cpm.edukiteWeb.notice = {
    init: function(){
        $(window).load(function(){
            $( ".calendarBannerActive" ).text('Calendar')
            $( ".noticeBannerActive" ).text('Notice')
            $( ".lockerBannerActive" ).text('Locker')

            $( ".dailyDairyBannerActive" ).text('Daily Diaries')
            $( ".kitePostBannerActive" ).text('Learning Journeys')
            $( ".galleryBannerActive" ).text('Gallery')
        });

        $('.btnSubmit').livequery('click', function(){
            var notice_id = $(this).attr('notice_id');
            var student_id = $(this).attr('student_id');
            var notes = $('#fld_notes').val();
            var url = '/index.php?module=webBasic_home&_spAction=displayFeedback&showHTML=0';
            $.get(url, {notice_id:notice_id, student_id: student_id, notes: notes},  function(html){
                var url = '/index.php?module=webBasic_home&_spAction=displayFeedback&showHTML=0';
                $.get(url, {notice_id:notice_id, student_id: student_id}, function(html){
                    $('#fld_notes').val('');
                    $('.feedbackDisplay').html(html);
                    Util.hideProgressInd();
                });
            });
        });

        $('a.popUp').livequery('click', function (e){
            var notice_id = $(this).attr('notice_id');

            var url = 'index.php?module=edukiteWeb_notice&_spAction=detailPopUp'
                    + '&notice_id=' + notice_id
                    + '&showHTML=0';
            var exp = {
                url: url
            };

            Util.openDialogForLink('Detail',  900, 500, 0, exp);
        });
    }
}
