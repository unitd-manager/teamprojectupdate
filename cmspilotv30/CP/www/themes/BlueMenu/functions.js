LoadReady.loadThemeDefautls = function(){
    $(function(){
        Cufon.replace('h1') ('h2') ('h3') ('h4') ('h5') ('form legend');
    
        Cufon.replace('.hlist li a', {
        	hover: true
        });

        ddsmoothmenu.init({
        	mainmenuid: 'smoothmenu1', //menu DIV id
        	orientation: 'h', //Horizontal or vertical menu: Set to 'h' or 'v'
        	classname: 'ddsmoothmenu', //class added to menu's outer DIV
        	//customtheme: ['#1c5a80', '#18374a'],
        	contentsource: 'markup' //'markup' or ['container_id', 'path_to_menu_file']
        })

        //ddaccordion.init({
        //    headerclass: "submenuheader", //Shared CSS class name of headers group
        //	contentclass: "submenu", //Shared CSS class name of contents group
        //	revealtype: "click", //Reveal content when user clicks or onmouseover the header? Valid value: "click", "clickgo", or "mouseover"
        //	mouseoverdelay: 200, //if revealtype="mouseover", set delay in milliseconds before header expands onMouseover
        //	collapseprev: true, //Collapse previous content (so only one open at any time)? true/false 
        //	defaultexpanded: [], //index of content(s) open by default [index1, index2, etc] [] denotes no content
        //	onemustopen: false, //Specify whether at least one header should be open always (so never all headers closed)
        //	animatedefault: false, //Should contents open by default be animated into view?
        //	persiststate: true, //persist state of opened contents within browser session?
        //	toggleclass: ["", ""], //Two CSS classes to be applied to the header when it's collapsed and expanded, respectively ["class1", "class2"]
        //	togglehtml: ["suffix", "<img src='images/ic_plus.gif' class='statusicon' alt='' />", "<img src='images/ic_minus.gif' class='statusicon' alt='' />"], //Additional HTML added to the header when it's collapsed and expanded, respectively  ["position", "html1", "html2"] (see docs)
        //	animatespeed: "fast", //speed of animation: integer in milliseconds (ie: 200), or keywords "fast", "normal", or "slow"
        //	oninit:function(headers, expandedindices){ //custom code to run when headers have initalized
        //		//do nothing
        //	},
        //	onopenclose:function(header, index, state, isuseractivated){ //custom code to run whenever a header is opened or closed
        //		//do nothing
        //	}
        //})
    });
}

$(function(){
    LoadReady.loadThemeDefautls();
});
