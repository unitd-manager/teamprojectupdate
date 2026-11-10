<?
class CP_Admin_Modules_Tradingsg_InvoiceLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_invoiceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
           ,'mainModuleName' => 'tradingsg_order'
        ));
    }
}