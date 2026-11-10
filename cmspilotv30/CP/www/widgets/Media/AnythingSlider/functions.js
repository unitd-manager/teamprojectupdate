Util.createCPObject('cpw.media.anythingSlider');

cpw.media.anythingSlider.run = function(exp){
    var speed    = exp.speed;
    var showNav  = exp.showNav;
    var showNavArrows  = exp.showNavArrows;
    var theme    = exp.theme;
    var handle   = exp.handle;
    var width    = exp.width;
    var height   = exp.height;
    var autoPlay = Util.stringToBoolean(exp.autoPlay);
    var resizeContents = exp.resizeContents;

    $('#' + handle).anythingSlider({
        autoPlay: autoPlay,
        width: width,
        height: height,
        resizeContents: resizeContents,
        theme: theme,
        autoPlayLocked: true,  // If true, user changing slides will not stop the slideshow
        resumeDelay: 15000, // Resume slideshow after user interaction, only if autoplayLocked is true (in milliseconds).
        buildNavigation: showNav,
        buildArrows: showNavArrows,
        delay: speed * 1000,
        easing: 'swing'
    })

    .anythingSliderFx({
         '.caption-top'    : [ 'caption-Top', '50px' ]
        ,'.caption-right'  : [ 'caption-Right', '130px' ]
        ,'.caption-bottom' : [ 'caption-Bottom', '50px' ]
        ,'.caption-left'   : [ 'caption-Left', '130px' ]

        // // base FX definitions can be mixed and matched in here too.
        // '.fade' : [ 'fade' ],
        //
        // // for more precise control, use the "inFx" and "outFx" definitions
        // // inFx = the animation that occurs when you slide "in" to a panel
        // inFx : {
        //     'img'  : { opacity: 1, top  : 0, time: 400, easing : 'swing' }
        // },
        // // out = the animation that occurs when you slide "out" of a panel
        // // (it also occurs before the "in" animation)
        // outFx : {
        //     'img': { opacity: 0, top: '-100px', time: 350 }
        // }

        //,inFx: {
        //    'img' : { opacity: 1, time: 500 }
        //},
        //outFx: {
        //    'img' : { opacity: 0, time: 0 }
        //}

    })
    .find('div[class*=caption]')
    .css({ position: 'absolute' })
    .prepend('<span class="close">x</span>')
    .find('.close').click(function(){
      var cap = $(this).parent(),
       ani = { bottom : -50 }; // bottom
      if (cap.is('.caption-top')) { ani = { top: -50 }; }
      if (cap.is('.caption-left')) { ani = { left: -150 }; }
      if (cap.is('.caption-right')) { ani = { right: -150 }; }
      cap.animate(ani, 400, function(){ cap.hide(); } );
    });
}
