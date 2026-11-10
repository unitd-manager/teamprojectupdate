<?
class CPL_Admin_Widgets_EnggCrm_AgeingReport_Functions extends CP_Admin_Widgets_EnggCrm_AgeingReport_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_ageingReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Ageing Report'
        ));
    }
}
