Util.createCPObject('cpw.core.subNav');

cpw.core.subNav.useJqUiStyle = function(){
    $('.subNavTab').addClass('ui-tabs ui-widget ui-widget-content ui-corner-all');
    $('.subNavTab .w-core-subNav ul').addClass('ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all');
    $('.subNavTab .w-core-subNav ul.ui-tabs-nav li').addClass('ui-state-default ui-corner-top');
    $('.subNavTab .w-core-subNav ul.ui-tabs-nav li.active').addClass('ui-tabs-selected ui-state-active');
    $('.subNavTab .ui-widget-header').css('height', '32px');
    $('.subNavTab .subNavTab .ui-tabs-nav li.ui-tabs-selected a').css('cursor', 'pointer');
    $('.subNavTab .ui-tabs-panel').css('min-height', '200px');
}
