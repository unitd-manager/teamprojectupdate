<?
class CPL_Admin_Widgets_EnggCrm_InvoiceByMonthReports_Functions extends CP_Admin_Widgets_EnggCrm_InvoiceByMonthReports_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_invoiceByMonthReports');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Invoice By Month'
        ));
    }
}
