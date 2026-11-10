<?
class CPL_Admin_Widgets_EnggCrm_InvoiceChartByMonth_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_invoiceChartByMonth');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Invoice Chart By Month'
        ));
    }
}
