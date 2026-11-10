Util.createCPObject('cpw.media.bsCarousel');

cpw.media.bsCarousel.run = function(exp){
    var speed    = exp.speed;
    var handle   = exp.handle;
    var autoplay = exp.autoplay;
    var lightbox = exp.zoom;
    var showInfo = exp.showCaption;
    var showInfo = exp.showCaption;

    if (autoplay == true){
        autoplay = parseInt(speed*1000)
    }

    $('.carousel').carousel({
      interval: 0
    })

    $('.carousel-control').css({'bottom': 'auto'});

    var width    = parseInt(exp.width);
    var height   = parseInt(exp.height);

    /*$('#' + handle).bsCarousel({
         'width' : width
        ,'height' : height
        ,'lightbox' : lightbox
        ,'autoplay' : autoplay
        ,'easing' : 'galleria'
        ,'showCounter' : false
        ,'showInfo' : showInfo
    })*/
}
