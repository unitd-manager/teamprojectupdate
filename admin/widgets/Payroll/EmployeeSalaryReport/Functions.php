<?
class CPL_Admin_Widgets_Payroll_EmployeeSalaryReport_Functions extends CP_Admin_Widgets_Payroll_EmployeeSalaryReport_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('payroll_employeeSalaryReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Employee Salary Report'
        ));
    }
}
