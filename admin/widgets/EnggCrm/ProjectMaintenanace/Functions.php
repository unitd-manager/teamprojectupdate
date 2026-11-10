<?
class CPL_Admin_Widgets_EnggCrm_ProjectMaintenanace_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectMaintenanace');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Maintenanace Tab'
        ));
    }
}
