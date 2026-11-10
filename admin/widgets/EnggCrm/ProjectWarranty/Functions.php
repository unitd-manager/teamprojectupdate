<?
class CPL_Admin_Widgets_EnggCrm_ProjectWarranty_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectWarranty');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Warranty Tab'
        ));
    }
}
