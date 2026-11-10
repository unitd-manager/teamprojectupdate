<?
class CP_Www_Widgets_Core_MainNav_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $btnPos     = '';
    var $surroundUl = true;
    var $class      = 'hlist';
    var $ulClass    = '';
    var $includeSecID = true;
    var $animate = false;
    var $animateType = 'bgPosition';
    var $showAsTree = false;
    var $showFullNavStructure = false;
    
    //slidingDoor means add <span> tag to <a> tag
    //<a href="#"><span>button text</span></a>
    var $hasSlidingDoorBtn = false; 
}