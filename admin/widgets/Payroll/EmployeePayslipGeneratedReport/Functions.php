<?
class CPL_Admin_Widgets_Payroll_EmployeePayslipGeneratedReport_Functions extends CP_Admin_Widgets_Payroll_EmployeePayslipGeneratedReport_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('payroll_employeePayslipGeneratedReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Payslip Generated Report'
        ));
    }
}
