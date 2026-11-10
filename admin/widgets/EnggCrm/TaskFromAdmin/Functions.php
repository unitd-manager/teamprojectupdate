<?
class CPL_Admin_Widgets_EnggCrm_TaskFromAdmin_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_taskFromAdmin');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Task From Admin'
        ));
    }
}
