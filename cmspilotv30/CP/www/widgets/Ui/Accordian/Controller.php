<?
class CP_Www_Widgets_Ui_Accordian_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $handle        = 'jqToolsAccordian1';
    var $lblArray      = array(); // array('title1', 'title2', 'title3')
    var $contentArray  = array(); // array($content1, $content2, $content3)
    var $type          = 'byArray'; //byArray, byContentDataArray, byContentType
    var $openFirstRow  = false; //if we need to show the first row in the accordian when the page is loaded
    var $toggleOnSecondClick  = false; //when a tab is opened you cannot click again, change this to true if you need so
    var $fadeInSpeed = 3000;
}