Util.createCPObject('cpm.edukite.teacher');

cpm.edukite.teacher = {
    init: function(){
        //TO CHANGE PICTURE UPLOAD ICON WHEN HOVER
        $('#media__edukite_teacher__picture a.btnSelectMedia img').hover(function(){
            $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/upload-icon-hover.png');
        },function(){
             $(this).attr('src','/cmspilotv30/CP/www/themes/Manager/images/upload-icon.png');
        });
    }

}