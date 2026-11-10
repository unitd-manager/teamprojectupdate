<?
class CP_Admin_Modules_EnterpriseIms_PaymentSummary_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_paymentSummary');
        $modObj['listLimit'] = 10;
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
           ,'actBtnsList' => array()
           ,'title'     => 'Payment Summary'
        ));
    }
}