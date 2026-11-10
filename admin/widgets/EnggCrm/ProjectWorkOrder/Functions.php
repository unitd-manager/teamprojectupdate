<?
class CPL_Admin_Widgets_EnggCrm_ProjectWorkOrder_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectWorkOrder');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Work Order Tab'
        ));
    }
}
