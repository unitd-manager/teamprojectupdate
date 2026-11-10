<?
class CPL_Admin_Widgets_Payroll_PassportExpiry_Functions extends CP_Admin_Widgets_Payroll_PassportExpiry_Functions
{
    function setWidgetArray($widgets){
        $widgetObj = $widgets->getWidgetObj('payroll_passportExpiry');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Passport Expiry in Dashboard'
        ));
    }
}
