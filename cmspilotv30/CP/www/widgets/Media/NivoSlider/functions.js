Util.createCPObject('cpw.media.nivoSlider');

cpw.media.nivoSlider.run = function(exp){
    var handle = exp.handle;
    var speed = exp.speed * 1000;
    var controlNav = exp.controlNav;
    var effect = exp.effect;
    $('#' + handle).nivoSlider({
        pauseTime: speed
       ,controlNav: controlNav
       ,effect: effect
    })
}
