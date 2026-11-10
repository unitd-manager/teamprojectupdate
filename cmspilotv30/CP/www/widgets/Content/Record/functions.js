Util.createCPObject('cpw.content.record');

cpw.content.record.simplyScroll = function(exp){
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
}