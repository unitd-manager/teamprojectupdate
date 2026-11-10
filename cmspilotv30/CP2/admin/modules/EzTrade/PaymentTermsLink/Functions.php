<?
class CP_Admin_Modules_EzTrade_PaymentTermsLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_paymentTermsLink');
        $modules->registerModule($modObj, array(
            'tableName'   => 'payment_terms'
           ,'keyField'    => 'payment_terms_id'
        ));
    }

    /**
     *
     */
    function getPaymentTermsSQL($company_id) {
        $sql = "
        SELECT description
        FROM payment_terms
        WHERE company_id = {$company_id}
        ORDER BY description
        ";
        return $sql;
    }

    /**
     *
     */
    function getPaymentTermsSupplierSQL($company_id) {
        $sql = "
        SELECT description
        FROM payment_terms
        WHERE company_id = {$company_id}
        ORDER BY description
        ";
        return $sql;
    }

}
