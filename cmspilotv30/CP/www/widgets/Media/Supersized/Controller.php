<?
class CP_Www_Widgets_Media_Supersized_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $showArrows      = true; // if true: navigation will be shown as in http://buildinternet.com/project/supersized/slideshow/3.2/demo.html
    var $showNavPanel    = true; // if true: navigation will be shown as in http://buildinternet.com/project/supersized/slideshow/3.2/demo.html
    var $showThumbnail   = true; 
    var $showProgressBar = true; 
    var $showControlBar  = true; 
    var $hideAllNav      = false; 
    var $speed           = 5;
    var $transition      = 1; // 0-None, 1-Fade, 2-Slide Top, 3-Slide Right, 4-Slide Bottom, 5-Slide Left, 6-Carousel Right, 7-Carousel Left
    var $module          = ''; 
    var $mediaType       = '';
    var $recordId        = '';
    var $thumbTrayDiv    = '#thumb-tray';
    var $showCaptionOutsideControlBar = true; 
    var $autoPlay = 1;
}