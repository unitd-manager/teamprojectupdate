<?
class CPL_Admin_Widgets_EnggCrm_ProjectTimesheet_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectTimesheet');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Timesheet Tab'
        ));
    }
}
