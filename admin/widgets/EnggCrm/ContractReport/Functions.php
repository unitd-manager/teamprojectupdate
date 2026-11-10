<?
class CPL_Admin_Widgets_EnggCrm_ContractReport_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_contractReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Contract Report'
        ));
    }
}
