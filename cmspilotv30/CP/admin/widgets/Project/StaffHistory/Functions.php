<?
class CP_Admin_Widgets_Project_StaffHistory_Functions
{
    //==================================================================//
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('project_staffHistory');
    }

    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_staffHistory');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Staff History'
        ));
    }
}
