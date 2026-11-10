<?
class CP_Www_Widgets_Media_BsCarousel_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $module = 'webBasic_content';
    var $recordType1 = 'picture';
    var $recordType2 = 'relatedPicture';
    var $record_id   = '';
    var $totalRecords = '';
    var $useRecType1Only = false; // if you want to show this plugin for just one record type

    var $autoplay  = false;
    var $speed     = 5;
    var $handle    = 'bsCarousel1';
    var $executeScript = true;
}