<?
class CPL_Admin_Widgets_EnggCrm_ProjectFinance_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectFinance');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Finance Tab'
        ));
    }
}
