<?
class CP_Admin_Widgets_Project_DomainHostingSummary_Functions
{
    //==================================================================//
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('project_domainHostingSummary');
    }

    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_domainHostingSummary');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Domain Hosting Summary'
        ));
    }
}
