<?
class CP_Admin_Modules_Pms_PaymentSummary_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_paymentSummary');
        $modObj['listLimit'] = 6;
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
           ,'actBtnsList' => array()
           ,'title'     => 'Payment Summary'
        ));
    }
}