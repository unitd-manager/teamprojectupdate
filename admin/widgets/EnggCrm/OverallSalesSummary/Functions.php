<?
class CPL_Admin_Widgets_EnggCrm_OverallSalesSummary_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_overallSalesSummary');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Overall Sales Summary'
        ));
    }
}
