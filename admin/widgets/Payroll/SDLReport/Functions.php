<?
class CPL_Admin_Widgets_Payroll_SDLReport_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('payroll_sDLReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'SDL Report'
        ));
    }
}
