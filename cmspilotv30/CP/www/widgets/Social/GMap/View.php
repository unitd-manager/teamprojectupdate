<?
class CP_Www_Widgets_Social_GMap_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('googleMap');
    //==================================================================//
    function getWidget() {
        $c = &$this->controller;
        
        $text = "
        <div class='{$c->class}' id='{$c->handle}'/>          
        ";
        
        $address = str_replace("'", "\'", $c->address);
        $addressDisplay = str_replace("'", "\'", $c->addressDisplay);
        
        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,zoom: {$c->zoom}
                ,centerLat: '{$c->centerLat}'
                ,centerLng: '{$c->centerLng}'
                ,lat: '{$c->lat}'
                ,lng: '{$c->lng}'
                ,address: '{$address}'
                ,addressDisplay: '{$addressDisplay}'
            }
            cpw.social.gMap.initMap(exp);
        "));        
        
        return $text;
    }

}