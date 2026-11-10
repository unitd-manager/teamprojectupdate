<?
class CP_Admin_Modules_Tradingsg_Receipt_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_receipt');
        $modules->registerModule($modObj, array(
            'tableName' => 'receipt'
           ,'keyField'  => 'receipt_id'
        ));
    }
}