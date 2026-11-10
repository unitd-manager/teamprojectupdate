<?
class CP_Admin_Modules_Edukloud_PaymentSummary_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_paymentSummary');
        $modObj['listLimit'] = 25;
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
           ,'actBtnsList' => array()
           ,'title'     => 'Payment Summary'
        ));
    }
}