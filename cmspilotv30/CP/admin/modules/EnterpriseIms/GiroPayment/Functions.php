<?
class CP_Admin_Modules_EnterpriseIms_GiroPayment_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_giroPayment');
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
           ,'actBtnsList' => array()
           ,'title'     => 'Giro Payment'
        ));
    }
}