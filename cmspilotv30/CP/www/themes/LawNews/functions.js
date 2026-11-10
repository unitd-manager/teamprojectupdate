Util.createCPObject('cpt.lawNews');

cpt.lawNews = {
    init:function(){
        $(function(){
            $('.bodyPanel .w-content-record ul li:nth-child(2n)').css('margin-right', 0);
            $('.correspondentList ul li.correspondent:nth-child(2n)').css('margin-right', 0);
            $('.logged_in_links ul li:last').css('padding-right', 0).css('border', 0);

            cpt.lawNews.disableContextMenu();
            cpt.lawNews.disableCopy();
            cpt.lawNews.loadFontResizer();
        });

    },

    loadFontResizer:function(){
        $('#fontsizer').jfontsizer({
            applyTo: 'body',
            changesmall: '2',
            changelarge: '2',
            expire: 0
        });
    },

    disableContextMenu:function(){
        $(document).bind("contextmenu",function(e){
            return false;
        });
    },

    disableCopy:function(){
        $(document).bind('cut copy', function (e) {
            e.preventDefault();
            Util.alert(Lang.data['t_lawNews_canNotCopy_message']);
        });

        //For IE
        var ctlPressed = false; //Flag to check if pressed the CTL key
        var ctl = 17; //Key code for Ctl Key
        var c = 67; //Key code for "c" key

        $(document).keydown(function(e) {
            if (e.keyCode == ctl){
              ctlPressed = true;
            }
        });

        $(document).keyup(function(e) {
            if (e.keyCode == ctl) {
              ctlPressed = false;
            }
        });

        $("body").keydown(function(e) {
            if (ctlPressed && e.keyCode == c) {
                e.preventDefault();
                Util.alert(Lang.data['t_lawNews_canNotCopy_message']);
                return false;
            }
        });
    },

    changeSiteHeaderLogo:function(logoFile){
        $('#header').css('backgroundImage', "url(" + logoFile + ")");
    },

    changeSiteFooterLogo:function(logoFile){
        $('#footer').css('backgroundImage', "url(" + logoFile + ")");
    }
}