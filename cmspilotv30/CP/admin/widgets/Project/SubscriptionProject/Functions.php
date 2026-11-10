<?
class CP_Admin_Widgets_Project_SubscriptionProject_Functions
{
    //==================================================================//
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('project_subscriptionProject');
    }

    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_subscriptionProject');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Subscription Project'
        ));
    }
}