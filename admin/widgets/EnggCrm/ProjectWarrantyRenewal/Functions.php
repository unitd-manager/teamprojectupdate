<?
class CPL_Admin_Widgets_EnggCrm_ProjectWarrantyRenewal_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectWarrantyRenewal');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Warranty Tab'
        ));
    }
}
