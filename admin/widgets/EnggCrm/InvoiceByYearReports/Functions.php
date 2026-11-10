<?
class CPL_Admin_Widgets_EnggCrm_InvoiceByYearReports_Functions extends CP_Admin_Widgets_EnggCrm_InvoiceByYearReports_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_invoiceByYearReports');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Invoice by Year'
        ));
    }
}
