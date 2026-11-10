<?
class CP_Admin_Modules_Logistics_InvoiceLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('logistics_invoiceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
           ,'mainModuleName' => 'logistics_order'
        ));
    }
}