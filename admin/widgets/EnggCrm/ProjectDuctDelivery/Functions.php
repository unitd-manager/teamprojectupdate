<?
class CPL_Admin_Widgets_EnggCrm_ProjectDuctDelivery_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectDuctDelivery');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Duct Delivery Tab'
        ));
    }
}
