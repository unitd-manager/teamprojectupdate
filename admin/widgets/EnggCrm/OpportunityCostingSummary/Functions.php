<?
class CPL_Admin_Widgets_EnggCrm_OpportunityCostingSummary_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_opportunityCostingSummary');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Opportunity Costing Summary Tab'
        ));
    }
}
