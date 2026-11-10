Util.createCPObject('cpw.core.mainNav');

cpw.core.mainNav.animageBg = function(){
    $('.hlist.animateBg ul li a').hover(
		function(){
		    if (!$(this).closest('li').hasClass('active')){
                $(this).css({backgroundPosition: 'left bottom'}).animate({backgroundPosition: 'right top'}, 1000, 'easeOutBounce'); 
            }
		},
		function(){
		    if (!$(this).closest('li').hasClass('active')){
                $(this).css({backgroundPosition: 'right top'}).animate({backgroundPosition: 'left bottom'}, 1000, 'easeOutBounce'); 
            }
		}
	);
}
