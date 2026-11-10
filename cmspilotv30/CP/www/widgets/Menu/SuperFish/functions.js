Util.createCPObject('cpw.menu.superFish');

cpw.menu.superFish.run = function(exp){
    $('ul.sf-menu').superfish();
    $('ul.sf-menu > li:first-child').addClass('first');
    $('ul.sf-menu > li:last-child').addClass('last');
}
