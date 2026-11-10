<?
class CP_Admin_Widgets_Payroll_PassportExpiry_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('payroll_passportExpiry');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Passport Expiry'
        ));
    }
}
