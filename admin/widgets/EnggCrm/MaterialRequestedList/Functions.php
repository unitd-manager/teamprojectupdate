<?
class CPL_Admin_Widgets_EnggCrm_MaterialRequestedList_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
    	$cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_materialRequestedList');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Material Requested List'
        ));
    }
}
