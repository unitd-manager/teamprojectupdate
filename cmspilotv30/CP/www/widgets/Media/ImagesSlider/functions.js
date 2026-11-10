Util.createCPObject('cpw.media.relatedImages');

cpw.media.relatedImages.run = function(exp){
    var speed    = exp.speed;
    var handle   = exp.handle;
    var autoplay = exp.autoplay;
    var lightbox = exp.zoom;
    var showInfo = exp.showCaption;
    var showInfo = exp.showCaption;
    var thumbnails = exp.thumbnails;

    if (thumbnails != '' && thumbnails != 'numbers' ){
        thumbnails = true;
    }

    if (autoplay == true){
        autoplay = parseInt(speed*1000)
    }

    var width    = parseInt(exp.width);
    var height   = parseInt(exp.height);

    $('#' + handle).galleria({
         'width' : width
        ,'height' : height
        ,'lightbox' : lightbox
        ,'autoplay' : autoplay
        ,'easing' : 'galleria'
        ,'showCounter' : false
        ,'showInfo' : showInfo
        ,'thumbnails' : thumbnails
    })
}
