Util.createCPObject('cpt.museum');

cpt.museum = {
    init: function(){
        $(function(){
            cpt.museum.megaMenu();
            cpt.museum.loadYoutubeInModal();
            cpt.museum.changeInnerBannerBgColor();
            $('.w-common-breadcrumb a:last').css('text-decoration', 'underline');
            $('.home-content .w-content-record ul li:nth-child(2n)').css('margin-right', 0);
            $('.m-event_event .picWrap .relatedPicture .float_right:nth-child(1n)').css('margin-left', '10px');

            // $('form#tagSearch').livequery(function() {
            //     var beforeSubmit = cpt.museum.tagSearchBeforeSubmit;
            //     var callback = cpt.museum.tagSearchSuccess;
            //     var opts = {
            //     };        
            //     Util.setUpAjaxFormGeneral('tagSearch', callback, beforeSubmit, opts);
            // });
            
            // $('#tagSearch .tagItem').livequery('click', function(e){
            //     cpt.museum.tagItemClick.call(this, e);
            // });            
            $('.mediaFilesDisplayThin a.pdf').attr('target', '_blank');

            $('a.eventForm').livequery('click', function (e){
                var title = Lang.get('m_event_event_registerForm_heading', 'Event Register Form');
                var successMsg = Lang.get('m_event_event_registerForm_message_success', 'Event Registration successfully submitted.');
                e.preventDefault();
                var expObj = {
                    validate: true,
                    callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        Util.alert(successMsg);
                    }
                }
                Util.openFormInDialog.call(this, 'eventRegisterForm', title, 600, 430, expObj);
            });  

            //open people wall, trade & commerce description in modal
            $(".pwTcItem").live('click', function(e) {
                e.preventDefault();
                var title = $(this).attr('title');
                var width = $(this).attr('dlg-w');
                var height = $(this).attr('dlg-h');
                Util.openDialogForLink.call(this, title, 720, height);
            });

        });
    },

    megaMenu:function(){
        $('ul.mega-menu li:last a').css('padding-right', '7px');
        //$('ul.mega-menu li:first a.dc-mega').css('color', '#06559f');
        //$('ul.mega-menu li:nth-child(2) a.dc-mega').css('color', '#06559f');
    },

    loadHomeCalendar: function(eventUrl){
        //var monthPrefix = Lang.data('cp_home_calendar_whatson');
        var monthPrefix = "What's on";
        var monthNames = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        for(i = 0; i < 11; i++){
            monthNames[i] = monthPrefix + ' ' +monthNames[i];
        }

        $('#home_calendar').datepicker({
            onSelect: function(dateText, dpInst) {
                cpt.museum.eventDateSelect(dateText, dpInst, eventUrl);
            }
            ,dayNamesMin: ['S', 'M', 'T', 'W', 'T', 'F', 'S']
            ,monthNames: monthNames
        });
    },

    loadHomeEventScroll: function(exp){
        var speed  = parseInt(exp.speed);
        var frameRate  = parseInt(exp.frameRate);
        var handle = exp.handle;
        var scrollClass = exp.scrollClass;
        var autoMode = exp.autoMode;
        var scrollDirection = exp.scrollDirection;
        var horizontal = (scrollDirection == 'vertical') ? false : true;

        $("#" + handle).simplyScroll({
            className: scrollClass,
            horizontal: horizontal,
            frameRate: frameRate,
            speed: speed,
            autoMode: autoMode,
            pauseOnHover: true
        });
        
        $("#" + handle).show();
    },

    eventDateSelect:function(dateText, dpInst, eventUrl) {
        var month =  dpInst.selectedMonth + 1; //month starts from 0
        var url = eventUrl + '?day=' + dpInst.selectedDay;
        url += '&month=' + month;
        url += '&year=' + dpInst.selectedYear

        document.location = url;
    },

    loadYoutubeInModal: function(){
/*        var embedlyApiKey = '3595139e499811e1ab1c4040d3dc5c07';
        $.nmObj({embedly: {key: embedlyApiKey}});
        $(".pic a[href*='youtu']").addClass('nyroModal');
        $(".pic a[href*='youtu']").nyroModal();*/
        $(".pic a[href*='youtu']").attr({
            rel: 'prettyPhoto'
        });

        $("a[rel^='prettyPhoto']").prettyPhoto({
             social_tools: false
            ,show_title: false
            ,allow_resize: false
            ,deeplinking: false    
            ,markup: '\
            <div class="pp_pic_holder">\
                    <div class="ppt">&nbsp;</div> \
                    <div class="pp_top"> \
                            <div class="pp_left"></div> \
                            <div class="pp_middle"></div> \
                            <div class="pp_right"></div> \
                    </div> \
                    <div class="pp_content_container"> \
                            <div class="pp_left"> \
                            <div class="pp_right"> \
                                    <div class="pp_content"> \
                                            <div class="pp_loaderIcon"></div> \
                                            <div class="pp_fade"> \
                                                    <a href="#" class="pp_expand" title="Expand the image">Expand</a> \
                                                    <div class="pp_hoverContainer"> \
                                                            <a class="pp_next" href="#">next</a> \
                                                            <a class="pp_previous" href="#">previous</a> \
                                                    </div> \
                                                    <div id="pp_full_res"></div> \
                                                    <div class="pp_details"> \
                                                            <div class="pp_nav"> \
                                                                    <a href="#" class="pp_arrow_previous">Previous</a> \
                                                                    <p class="currentTextHolder">0/0</p> \
                                                                    <a href="#" class="pp_arrow_next">Next</a> \
                                                                    <a href="#" class="pp_show_hide_description">Show Description</a> \
                                                            </div> \
                                                            <p class="pp_description"></p> \
                                                            {pp_social} \
                                                            <a class="pp_close" href="#">Close</a> \
                                                    </div> \
                                            </div> \
                                    </div> \
                            </div> \
                            </div> \
                    </div> \
                    <div class="pp_bottom"> \
                            <div class="pp_left"></div> \
                            <div class="pp_middle"></div> \
                            <div class="pp_right"></div> \
                    </div> \
            </div> \
            <div class="pp_overlay"></div>'        
        });
    },

    changeInnerBannerBgColor: function(){
        var bgcolor = $('.w-media-banner  li').attr('bgcolor');
        if(bgcolor != ''){
            $('.w-media-banner li .subcolumns .subcr').css('background', bgcolor);
            $('.w-media-banner li .caption').css('background', bgcolor);
        }
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
    },
            
    // tagItemClick:function(e){
    //     e.preventDefault();
    //     $(this).toggleClass('selected');
    //     var selected = $(this).hasClass('selected');
        
    //     var id = $(this).attr('id');
    //     var idArr = id.split('__');
    //     var fldName  = idArr[0];
    //     var fldValue = idArr[1];
    //     if(selected){ //if the item is slelected
    //         if(fldValue == 'all'){
    //             $("[id^='fld_"+ fldName +"']").val('');
    //             $("[id^='"+ fldName +"']").removeClass('selected');
    //             $("[id^='"+ fldName +"__all']").addClass('selected');
    //         } else {
    //             $("[id^='fld_"+ id +"']").val(fldValue);
    //             $("[id^='"+ fldName +"__all']").removeClass('selected');

    //         }
    //     } else { //if the item is un slelected
    //         $("[id^='fld_"+ id +"']").val('');
    //     }
    //     cpt.museum.tagSearchSubmit();
    // },

    tagItemClick:function(e){
        e.preventDefault();
        $(this).toggleClass('selected');
        var selected = $(this).hasClass('selected');
        
        var id = $(this).attr('id');
        var idArr = id.split('__');
        var fldName  = idArr[0];
        var fldValue = idArr[1];

        if(selected){ //if the item is slelected
            if(fldValue == 'all'){
                $("[id^='fld_"+ fldName +"']").val('');
                $("[id^='"+ fldName +"']").removeClass('selected');
                $("[id^='"+ fldName +"__all']").addClass('selected');
            } else {
                $("[id^='fld_"+ fldName +"']").val('');
                $("[id^='fld_"+ id +"']").val(fldValue);
                $("[id^='"+ fldName +"']").removeClass('selected');
                $("[id^='"+ id +"']").addClass('selected');

            }
        } else { //if the item is un slelected
            $("[id^='fld_"+ id +"']").val('');
            $("[id^='"+ fldName +"']").removeClass('selected');            
            $("[id^='"+ fldName +"__all']").addClass('selected');            
        }
        
        //$("[id='fld_searchBarKW']").val('');
        cpt.museum.tagSearchSubmit();
    },    
    
    tagSearchBeforeSubmit:function(){
        Util.showProgressInd();
        var kw = $("[id='fld_searchBarKW']").val();
        if(kw != ''){
            var fldName = 'tags_ids';
            $("[id^='fld_"+ fldName +"']").val('');
            $("[id^='"+ fldName +"']").removeClass('selected');            
            $("[id^='"+ fldName +"__all']").addClass('selected');             
        }
    },

    tagSearchSubmit:function(){
        $('#tagSearch').submit();
    },    
            
    tagSearchSuccess:function(json){
        $('#eventRows').html(json.successMsg);
        Util.prepopulatedTextbox();
        Util.hideProgressInd();
    }            

}
