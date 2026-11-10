<?
class CP_Www_Widgets_Ui_Tabs_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $handle       = 'jqToolsTabs1';
    var $panesId      = 'jqToolsPanes1';
    var $lblArray     = array(); // array('title1', 'title2', 'title3')
    var $contentArray = array(); // array($content1, $content2, $content3)
    var $type         = 'byArray'; //byArray, byContentDataArray, byContentType
    var $accordian    = false;
}