<?
class CPL_Admin_Widgets_EnggCrm_ProjectQuote_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectQuote');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Quote Tab'
        ));
    }
}
