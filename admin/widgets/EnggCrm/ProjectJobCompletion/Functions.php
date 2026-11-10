<?
class CPL_Admin_Widgets_EnggCrm_ProjectJobCompletion_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectJobCompletion');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Job Completion'
        ));
    }
}
