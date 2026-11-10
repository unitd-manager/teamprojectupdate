<?
class CPL_Admin_Widgets_EnggCrm_ProjectClaim_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectClaim');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Claim Tab'
        ));
    }
}
