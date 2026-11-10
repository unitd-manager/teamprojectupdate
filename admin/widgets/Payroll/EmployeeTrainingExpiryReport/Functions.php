<?
class CPL_Admin_Widgets_Payroll_EmployeeTrainingExpiryReport_Functions extends CP_Admin_Widgets_Payroll_EmployeeTrainingExpiryReport_Functions
{
   function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('payroll_employeeTrainingExpiryReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Employee Training Expiry Report'
        ));
    }
}
