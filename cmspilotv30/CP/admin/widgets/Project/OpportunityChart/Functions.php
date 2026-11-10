<?
class CP_Admin_Widgets_Project_OpportunityChart_Functions
{
    /**
     *
     */
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('project_opportunityChart');
    }

    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_opportunityChart');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Opportunity Chart'
        ));
    }
}
