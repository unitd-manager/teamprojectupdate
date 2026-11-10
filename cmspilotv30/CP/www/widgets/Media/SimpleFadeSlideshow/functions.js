Util.createCPObject('cpw.media.simpleFadeSlideshow');

cpw.media.simpleFadeSlideshow.run = function(exp){
    var speed  = exp.speed * 1000;
    var handle = exp.handle;
    var width  = exp.width;
    var height = exp.height;

    $('#' + handle).fadeSlideShow({
         width: width
        ,height: height
        ,interval: speed
   });
}
