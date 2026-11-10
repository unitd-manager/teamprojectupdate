Util.createCPObject('cpw.media.supersized');

cpw.media.supersized.run = function(exp){
    $(function(){
        var slidesObj = jQuery.parseJSON(exp.slidesObj);

        $('.page').addClass('withSuperSizeBg');
        
        $.supersized.themeVars.image_path = $('#jssPath').val() + 'jquery/supersized-3.2.5/images/';
        $.supersized.themeVars.slide_caption = exp.slideCaptionDiv;
        $.supersized.themeVars.thumb_tray    = exp.thumbTrayDiv;
        var autoplay = parseInt(exp.autoPlay);

    	$.supersized({
    		 slides: slidesObj
            ,slide_links: 'blank' // Individual links for each slide (Options: false, 'num', 'name', 'blank')
            ,thumb_links: 1
            ,new_window: 0
            ,autoplay: autoplay
            ,transition: 1
            ,transition_speed: 1000
    	});
    });
}
