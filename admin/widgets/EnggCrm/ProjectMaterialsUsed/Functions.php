<?
class CPL_Admin_Widgets_EnggCrm_ProjectMaterialsUsed_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectMaterialsUsed');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Materials Used Tab'
        ));
    }
}
