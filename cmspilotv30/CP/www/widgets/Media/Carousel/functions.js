Util.createCPObject('cpw.media.carousel');

cpw.media.carousel.run = function(exp){
    var handle    = exp.handle;
    var speed     = parseInt(exp.speed) * 1000;
    var items     = parseInt(exp.items);
    var fx        = exp.fx;
    var showNav   = Util.stringToBoolean(exp.showNav);
    var autoStart = Util.stringToBoolean(exp.autoStart);
    var showPager = Util.stringToBoolean(exp.showPager);
    var width     = parseInt(exp.width);
    var height    = parseInt(exp.height);
    var anchorBuilder = exp.anchorBuilder ? exp.anchorBuilder : null;

    var obj = $('#' + handle + ' ul');
    obj.carouFredSel({
           auto: autoStart
          ,scroll: {
               fx: fx
              ,pauseDuration: speed
              ,anchorBuilder: anchorBuilder
              ,pauseOnHover: true
          }
    });

    if (items){
       obj.trigger('configuration', ['items', items]);
    }

    if (width){
        obj.trigger('configuration', ['width', width]);
    }

    if (height){
        obj.trigger('configuration', ['height', height]);
    }

    if (showPager){
        pager = '#' + handle + '_pag';
        obj.trigger('configuration', ['pagination', pager]);
        obj.trigger("updatePageStatus", true); // use "true" to re-built the pagination
    }

    /*** this does not work now -- may be a bug with the plugin ****/
    if (showNav){
        btnName1 = '#' + handle + '_prev';
        btnName2 = '#' + handle + '_next';
        obj.trigger('configuration', {
            prev : {
              button: btnName1
            },
            next : {
              button: btnName2
            }
        });

        $(btnName1).click(function(e) {
            e.preventDefault();
            obj.trigger("prev");
            return false;
        });
        $(btnName2).click(function(e) {
            e.preventDefault();
            obj.trigger("next");
            return false;
        })
    }
}
