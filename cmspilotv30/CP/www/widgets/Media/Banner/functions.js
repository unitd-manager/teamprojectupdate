Util.createCPObject('cpw.media.banner');

cpw.media.banner.showSuperSizedImage = function(exp){
    $(function(){
        var slidesObj = jQuery.parseJSON(exp.slidesObj);
        $('.page').addClass('withSuperSizeBg');
    	$.supersized({
    		 slides: slidesObj
            ,slide_links: 'blank'	// Individual links for each slide (Options: false, 'num', 'name', 'blank')
    	});
    });
}
