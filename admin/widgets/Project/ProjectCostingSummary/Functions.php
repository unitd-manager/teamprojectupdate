<?
class CPL_Admin_Widgets_Project_ProjectCostingSummary_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_projectCostingSummary');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Costing Summary Tab'
        ));
    }
}
