<?
class CP_Admin_Modules_Edukloud_GiroPayment_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_giroPayment');
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
           ,'actBtnsList' => array()
           ,'title'     => 'Giro Payment'
        ));
    }
}