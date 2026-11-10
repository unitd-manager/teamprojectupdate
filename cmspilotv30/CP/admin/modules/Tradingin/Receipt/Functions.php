<?
class CP_Admin_Modules_Tradingin_Receipt_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingin_receipt');
        $modules->registerModule($modObj, array(
            'tableName' => 'receipt'
           ,'keyField'  => 'receipt_id'
        ));
    }
}