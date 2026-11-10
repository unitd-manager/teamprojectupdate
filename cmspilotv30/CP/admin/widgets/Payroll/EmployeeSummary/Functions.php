<?
class CP_Admin_Widgets_Payroll_EmployeeSummary_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('payroll_employeeSummary');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Employee Summary'
        ));
    }
}
