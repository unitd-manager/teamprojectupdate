<?
class CP_Www_Widgets_Social_GMap_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $handle    = 'map-canvas';
    var $class     = 'map-canvas';
    var $lat       = '';
    var $lng       = '';
    var $centerLat = '';
    var $centerLng = '';
    var $address   = '';
    var $addressDisplay = '';
    var $zoom      = 10;
    var $saveLatLngUrl = '';
}