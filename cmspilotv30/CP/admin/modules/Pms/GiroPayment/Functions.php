<?
class CP_Admin_Modules_Pms_GiroPayment_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_giroPayment');
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
           ,'actBtnsList' => array()
           ,'title'     => 'Giro Payment'
        ));
    }
}