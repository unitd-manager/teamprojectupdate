<?
class CP_Admin_Widgets_Directory_GMap_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $handle    = 'map-canvas';
    var $lat       = '';
    var $lng       = '';
    var $centerLat = '';
    var $centerLng = '';
    var $address   = '';
    var $zoom      = 10;
    var $saveLatLngUrl = '';
}