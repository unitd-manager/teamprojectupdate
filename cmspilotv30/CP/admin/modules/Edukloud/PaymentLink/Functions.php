<?
class CP_Admin_Modules_Edukloud_PaymentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_paymentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'payment'
           ,'keyField'  => 'payment_id'
        ));
    }
}
