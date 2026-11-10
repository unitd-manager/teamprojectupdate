<?
class CP_Admin_Modules_Tradingin_Ledger_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingin_ledger');
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
           ,'actBtnsList' => array()
           ,'title'     => 'Ledger'
        ));
    }
}