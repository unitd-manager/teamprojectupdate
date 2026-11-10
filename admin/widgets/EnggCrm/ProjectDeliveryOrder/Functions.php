<?
class CPL_Admin_Widgets_EnggCrm_ProjectDeliveryOrder_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectDeliveryOrder');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Delivery Order Tab'
        ));
    }
}
