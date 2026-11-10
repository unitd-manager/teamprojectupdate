<?
class CPL_Admin_Widgets_Payroll_WorkpermitExpiry_Functions extends CP_Admin_Widgets_Payroll_WorkpermitExpiry_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('payroll_workpermitExpiry');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Work Permit Expiry in Dashboard'
        ));
    }
}
