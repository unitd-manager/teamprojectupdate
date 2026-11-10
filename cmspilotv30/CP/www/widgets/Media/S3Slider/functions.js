Util.createCPObject('cpw.media.s3Slider');

cpw.media.s3Slider.run = function(exp){
    var speed  = exp.speed * 1000;
    var handle = exp.handle;
    var width  = exp.width;
    var height = exp.height;

    $('#' + handle).s3Slider({
        timeOut: speed
    });
    
    if (width){
        $('#' + handle).css('width', width + 'px');
        $('#' + handle + 'Content').css('width', width + 'px');
    }

    if (height){
        $('#' + handle).css('height', height + 'px');
    }
}
