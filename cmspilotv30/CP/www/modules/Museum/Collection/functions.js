Util.createCPObject('cpm.museum.collection');

cpm.museum.collection = {
    init: function(){
        $('.pp_show_hide_description').live('click', function(e){
            e.preventDefault();
            Util.showSimpleMessageInDialog($('.pp_description').html());
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

        //open sponsor description in modal
        $(".description a[href*='_spAction=sponsor&showHTML=0']").live('click', function(e) {
            e.preventDefault();
            var title = $(this).attr('title');
            var width = $(this).attr('dlg-w');
            var height = $(this).attr('dlg-h');
            Util.openDialogForLink.call(this, title, 720, height);
        });
    }
}
