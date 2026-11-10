<?
class CP_Admin_Modules_Labsg_Receipt_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('labsg_receipt');
        $modules->registerModule($modObj, array(
            'tableName' => 'receipt'
           ,'keyField'  => 'receipt_id'
        ));
    }
}