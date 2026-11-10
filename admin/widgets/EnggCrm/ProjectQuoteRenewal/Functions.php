<?
class CPL_Admin_Widgets_EnggCrm_ProjectQuoteRenewal_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectQuoteRenewal');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Renewal Tab'
        ));
    }
}
