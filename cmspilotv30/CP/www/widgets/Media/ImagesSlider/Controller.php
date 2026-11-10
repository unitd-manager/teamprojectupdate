<?
class CP_Www_Widgets_Media_ImagesSlider_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $module = 'webBasic_content';
    var $recordType1 = 'picture';
    var $recordType2 = 'relatedPicture';
    var $record_id   = '';
    var $totalRecords = '';
    var $useRecType1Only = false; // if you want to show this plugin for just one record type

    var $autoplay  = false;
    var $speed     = 5;
    var $handle    = 'galleria1';
    var $showCaption = true;
    var $width     = 400;
    var $height    = 400;
    var $zoom      = true;
    var $thumbnails = true; // you can set to empty or numbers
    var $executeScript = true;
}