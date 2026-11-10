<?
class CP_Admin_Widgets_Project_Opportunity_Functions
{
    //==================================================================//
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('project_opportunity');
    }

    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_opportunity');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Opportunity Report'
        ));
    }
}
