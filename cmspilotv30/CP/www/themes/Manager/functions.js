Util.createCPObject('cpt.manager');

cpt.manager = {
    init: function(){
        $('#btnSaveRecord').click(function(e){
            e.preventDefault();
            Util.showProgressInd('Progressing');
            var expiry_date = $('#fld_expiry_date').val();
            var newdate = new Date();

            var dd = ("0" + newdate.getDate()).slice(-2);
            var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
            var y  = newdate.getFullYear();

            var endDate = dd + '-'+ mm + '-' + y;
            if(expiry_date < endDate && expiry_date != ''){
                Util.hideProgressInd();
                $('#fld_expiry_date').val('');
                var str = new String("Past dates are invalid, please place a future expiry date.");
                Util.alert(str.bold());
            } else{
                $('#frmEdit').submit();
                alert('Record Successfully Saved')
            }
        });


        $('#btnAddRecord').click(function(e){
            e.preventDefault();
            $('#frmNew').submit();
        });

        $('a.sortMedia').livequery('click', cpt.manager.sortMedia);

        $('a.rotateMedia').livequery('click', cpt.manager.rotateMedia);

        $('.mediaFilesDisplayWrap .imageDesc input[name=caption]').livequery('change', function(){
            var parent = $(this).closest('tr');
            var captionObj = $(this).parents('tr').find('input[name=caption]');
            var caption    = captionObj.val();
            var media_id   = $(this).attr('id');

            var url = '/index.php?module=edukite_notice&_spAction=updateCaptionInMedia&showHTML=0';

            $.get(url, {caption: caption, media_id: media_id}, function(json){
            });
        });

        //Notice Read Summary
        $('.noticeRead').livequery('click', function (e){
            var notice_id = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=NoticeReadSummary'
                    + '&notice_id=' + notice_id
                    + '&showHTML=0';
            var exp = {
                url: url
            };

            Util.openDialogForLink('Notice Read Summary',  500, 500, 0, exp);
        });

        //Homework Summary
        $('.homeworkSummary a').livequery('click', function (e){
            var notice_id = $(this).attr('notice_id');

            var url = '/index.php?module=edukite_notice&_spAction=homeworkSummary'
                    + '&notice_id=' + notice_id
                    + '&showHTML=0';
            var exp = {
                url: url
            };

            Util.openDialogForLink('Homework Summary',  500, 500, 0, exp);
        });


        /*$('a.launchNowImage img').click(function(){
            $('#frmEdit').submit();
            Util.hideProgressInd();
        });*/

        //TO CHANGE KITE ICON IMAGE WHEN HOVER
        $('.kiteIcon img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/kite-icon-hover.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png');
        });

        //TO CHANGE NAVIGATION ARROW ICON IMAGE WHEN HOVER
        $('.preBtn img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/ArrowL-hover.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/ArrowL.png');
        });

        $('.nxtBtn img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/ArrowR-hover.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/ArrowR.png');
        });
    },

    rotateMedia: function(e){
        e.preventDefault();
        var media_id = $(this).attr('id');
        $('#frmEdit').submit();
        var url = '/index.php?module=edukite_notice&_spAction=rotateMediaRecord' +
                  '&showHTML=0';
        $.get(url, {media_id:media_id} , function(data){
            Util.hideProgressInd();
            window.location.reload(true);
            //Util.alert('The Image is rotated, please refresh to see the rotated image')
        });
    },

    sortMedia: function(e){
        e.preventDefault();
        var media_id = $(this).attr('id');
        var link = $(this);
        $('#frmEdit').submit();
        Util.showProgressInd('Updated Succesfully');
        var url = '/index.php?module=edukite_notice&_spAction=sortMediaRecord' +
                  '&showHTML=0';
        $.get(url, {media_id:media_id} , function(data){
            Util.hideProgressInd();
            window.location.reload(true);
        });
    },

    cbAfterEdit: function(){
        Util.hideProgressInd();
    }
}


