<?
class CP_Admin_Widgets_Project_TaskSummary_Functions
{
    //==================================================================//
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('project_taskSummary');
    }

    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_taskSummary');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Task Summary'
        ));
    }
}
