<?
class CPL_Admin_Widgets_Project_InvoiceSummary_Functions extends CP_Admin_Widgets_Project_InvoiceSummary_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('project_invoiceSummary');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Invoice Summary'
        ));
    }

    //==================================================================//
    function setPluginArray($widgets){
        $widgetObj = $widgets->getWidgetObj('project_invoiceSummary');
    }
}
