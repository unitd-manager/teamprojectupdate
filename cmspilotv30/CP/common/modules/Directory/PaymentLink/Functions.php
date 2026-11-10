<?
class CP_Common_Modules_Directory_PaymentLink_Functions
{
    function setModuleArray($modules){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $tableName = 'payment';
        $keyField = 'payment_id';
        $modObj = $modules->getModuleObj('directory_paymentLink');
        $modules->registerModule($modObj, array(
             'tableName' => $tableName
            ,'keyField'  => $keyField
        ));
    }
}
