<?
class CPL_Admin_Widgets_EnggCrm_MaterialDelivered_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
    	$cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_materialDelivered');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Material Delivered'
        ));
    }
}
