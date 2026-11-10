Util.createCPObject('cpw.menu.megaMenu');

cpw.menu.megaMenu.run = function(exp){
    var classParent     = exp.classParent;
    var classContainer  = exp.classContainer;
    var classSubLink    = exp.classSubLink;
    var rowItems        = exp.rowItems;
    var speed           = exp.speed;
    var effect          = exp.effect;
    var event           = exp.event;
    var fullWidth       = exp.fullWidth;

    $('ul.mega-menu').dcMegaMenu({
        classParent: classParent,
        classContainer: classContainer,
        classSubLink: classSubLink,
        rowItems: rowItems,
        speed: speed,
        effect: effect,
        event: event,
        fullWidth: fullWidth
    });
}
