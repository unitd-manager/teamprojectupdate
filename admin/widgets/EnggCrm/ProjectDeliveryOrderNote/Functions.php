<?
class CPL_Admin_Widgets_EnggCrm_ProjectDeliveryOrderNote_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectDeliveryOrderNote');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Delivery Note'
        ));
    }
}
