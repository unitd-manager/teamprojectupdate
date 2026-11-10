Util.createCPObject('cpm.lawNews.newsArchive');

cpm.lawNews.newsArchive = {
    init: function(){
        if($("#advancedSearch input[name='keyword']").length){
            Util.prepopulatedTextbox();
            $('#advancedSearch').submit(function(e){
                e.preventDefault();
                Util.clearPrepopulatedTextbox($(this));
                $(this).unbind('submit');
                Util.showProgressInd();
                $(this).trigger('submit');
                Util.hideProgressInd();
            });
        }

        $("#clearJurisdiction").live('click', function (e) {
            e.preventDefault();
            $("select[name^='jurisdiction_id']").val('').removeAttr('slected');
        });

        $('.article_memeber_links ul li:last').addClass('article_memeber_links_ul_li_last');

        $('.print_article').livequery('click', function(){
            window.print();
        });

        $('.clip_it').livequery('click', function(e){
            cpm.lawNews.newsArchive.saveToMyClips.call(this, e);
        });

        $('.email_to_friend').livequery('click', function(e){
            cpm.lawNews.newsArchive.emailToFriend.call(this, e);
        });
    },

    saveToMyClips: function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        if (url == '' || url == 'javascript:void(0)'){
            url = $(this).attr('link');
        }

        Util.showProgressInd();
        $.getJSON(url, function(json) {
            var dialogTitle = (json.status == 'success') ? Lang.data['cp_lbl_success'] : Lang.data['cp_lbl_error'];
            Util.alert(json.message, '', dialogTitle );
        });
        Util.hideProgressInd();
    },

    emailToFriend: function(e){
        e.preventDefault();
        Util.showProgressInd();
        var title = $(this).attr('dialogTitle');
        var expObj = {
            validate: true,
            submitBtnText: Lang.data['cp_lbl_submit'],
            cancelBtnText: Lang.data['cp_lbl_cancel'],
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Util.alert(Lang.data['w_social_emailToFriend_message_success']);
            }
        }
        Util.openFormInDialog.call(this, 'emailToFriendForm', title, 450, 550, expObj);
        Util.hideProgressInd();
    }
}
