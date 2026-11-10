<?
class CPL_Admin_Widgets_Project_InvoiceSummaryChart_Functions
{
    /**
     *
     */
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('project_invoiceSummaryChart');
    }

    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_invoiceSummaryChart');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Invoice Summary Chart'
        ));
    }
}
