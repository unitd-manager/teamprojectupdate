<?
class CPL_Admin_Widgets_EnggCrm_EmployeeReport_Functions extends CP_Admin_Widgets_EnggCrm_EmployeeReport_Functions
{
    //==================================================================//
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('enggCrm_employeeReport');
    }

    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_employeeReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Employee Report'
        ));
    }
}
