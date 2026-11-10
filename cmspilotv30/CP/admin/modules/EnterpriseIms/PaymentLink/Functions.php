<?
class CP_Admin_Modules_EnterpriseIms_PaymentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_paymentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'payment'
           ,'keyField'  => 'payment_id'
        ));
    }
}
