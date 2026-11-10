Util.createCPObject('cpw.media.flexSlider');

cpw.media.flexSlider.run = function(exp){
    
    
    var handle   = exp.handle;
    var slideshowSpeed = exp.slideshowSpeed;
    var animationSpeed = exp.animationSpeed;
    var animationType  = exp.animationType;
    var direction  = exp.direction;
    var showNav  = exp.showNav;
    var showNavArrows  = exp.showNavArrows;
    var autoPlay = Util.stringToBoolean(exp.autoPlay);

  $('#' + handle).flexslider({
     animation: animationType
    ,slideshowSpeed: slideshowSpeed * 1000
    ,animationSpeed: animationSpeed * 1000
    ,direction: direction
    ,slideshow: autoPlay
    ,controlNav: showNav
    ,directionNav: showNavArrows
    ,easing: "swing"
    ,useCSS: false
  });

}
