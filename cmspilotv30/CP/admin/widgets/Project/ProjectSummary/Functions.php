<?
class CP_Admin_Widgets_Project_ProjectSummary_Functions
{
    //==================================================================//
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('project_projectSummary');
    }

    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_projectSummary');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Summary'
        ));
    }
}
