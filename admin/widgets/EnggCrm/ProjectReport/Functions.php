<?
class CPL_Admin_Widgets_EnggCrm_ProjectReport_Functions extends CP_Admin_Widgets_EnggCrm_ProjectReport_Functions
{
    //==================================================================//
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectReport');
    }

    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Report'
        ));
    }
}
