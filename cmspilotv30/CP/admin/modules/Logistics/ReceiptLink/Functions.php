<?
class CP_Admin_Modules_Logistics_ReceiptLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('logistics_receiptLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'receipt'
           ,'keyField'  => 'receipt_id'
           ,'mainModuleName' => 'logistics_order'
        ));
    }
}