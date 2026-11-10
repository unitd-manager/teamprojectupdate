<?
class CP_Admin_Modules_Tradingsg_ReceiptLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_receiptLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'receipt'
           ,'keyField'  => 'receipt_id'
           ,'mainModuleName' => 'tradingsg_order'
        ));
    }
}