<?
class CPL_Admin_Widgets_EnggCrm_ProjectMaterialTransferred_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_projectMaterialTransferred');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Project Materials Transferred Tab'
        ));
    }
}
