Util.createCPObject('cpt.museumMobile');

cpt.museumMobile = {
    init: function(){
        $(function(){
            cpt.museumMobile.loadYoutubeInModal();
            $('.w-core-mainNav .hlist ul .first a').css('margin-left', 0);
            $('.m-event_event .picWrap .relatedPicture .float_left:nth-child(2n)').css('margin-right', 0);
            $('.m-event_event #fld_event_date').livequery('change', function (e) {
                $('#mobileEventSearchForm').submit();
            });

        });
    },


    loadYoutubeInModal: function(){
        var embedlyApiKey = '3595139e499811e1ab1c4040d3dc5c07';
        $.nmObj({embedly: {key: embedlyApiKey}});
        $(".pic a[href*='youtu']").addClass('nyroModal');
        $(".pic a[href*='youtu']").nyroModal();
    },

    openNoticeModal: function(){
        var url = '/index.php?_theme=museum&_spAction=popupNotice&showHTML=0';
        extraPar = {
            url: url
           ,callbackOnSuccess: function(){
                $('#dialog').dialog('close');
                $('#dialog').dialog('destroy');
                cpp.common.comment.reload();
           }

        };

        Util.openDialogForLink ('Notice', 600, 350, 'showCloseBtn', extraPar)
    }

}
