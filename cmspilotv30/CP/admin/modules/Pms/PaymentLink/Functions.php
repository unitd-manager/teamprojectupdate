<?
class CP_Admin_Modules_Pms_PaymentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_paymentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'payment'
           ,'keyField'  => 'payment_id'
        ));
    }
}
