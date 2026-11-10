<?
class CPL_Admin_Widgets_EnggCrm_DashboardTopPanel_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_dashboardTopPanel');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Dashboard Top Panel'
        ));
    }
}
