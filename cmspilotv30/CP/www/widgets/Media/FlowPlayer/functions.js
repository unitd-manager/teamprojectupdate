Util.createCPObject('cpw.media.flowPlayer');

cpw.media.flowPlayer.init = function(exp){
	// setup overlay actions to buttons
	$(".cpOverlayVideo").overlay({
		// use the Apple effect for overlay
		effect: 'apple',		
		expose: '#789',				
		
		onLoad: function(content) {
			// find the player contained inside this overlay and load it
			this.getOverlay().find("a.cpFlowPlayer").flowplayer(0).load();
		},
		
		onClose: function(content) {
			$f().unload();
		}
	});				

    var jssPath = $('#jssPath').val();

	// install flowplayers
	$("a.cpFlowPlayer").flowplayer(jssPath + "/flash/flowplayer-3.2.7/flowplayer-3.2.7.swf"); 
	
}
