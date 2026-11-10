<?
class CPL_Admin_Widgets_EnggCrm_ProjectPurchaseOrder_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectPurchaseOrder');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Purchase Order Tab'
        ));
    }
}
