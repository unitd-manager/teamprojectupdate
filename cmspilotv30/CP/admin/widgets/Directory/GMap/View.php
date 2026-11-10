<?
class CP_Admin_Widgets_Directory_GMap_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('googleMap');
    //==================================================================//
    function getWidget() {
        $c = &$this->controller;
        
        $saveLatLngLink = '';
        if($c->saveLatLngUrl != ''){
            $saveLatLngLink = "
            <a href='javascript:void(0)' link='{$c->saveLatLngUrl}' id='saveLatLng' class='button mt10 mb10'>Save Position</a>    
            ";
        }
        
        $text = "
        <div id='infoPanel'>
            <b>Marker status:</b>
            <div id='markerStatus'><i>Click and drag the marker.</i></div>
            <b>Current position:</b>
            <div id='info'></div>
            <b>Closest matching address:</b>
            <div id='address'></div>
        </div>   
        {$saveLatLngLink}
        <div id='{$c->handle}'/>          
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,zoom: {$c->zoom}
                ,centerLat: '{$c->centerLat}'
                ,centerLng: '{$c->centerLng}'
                ,lat: '{$c->lat}'
                ,lng: '{$c->lng}'
                ,address: '{$c->address}'
            }
            cpw.directory.gMap.initMap(exp);
        "));        
        
        return $text;
    }

}