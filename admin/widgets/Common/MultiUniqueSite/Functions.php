<?
class CPL_Admin_Widgets_Common_MultiUniqueSite_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('common_multiUniqueSite');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Multi-Site'
        ));
    }
}
