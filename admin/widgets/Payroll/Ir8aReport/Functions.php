<?
class CPL_Admin_Widgets_Payroll_Ir8aReport_Functions extends CP_Admin_Widgets_Payroll_Ir8aReport_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('payroll_ir8aReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'IR8A Report'
        ));
    }
}
