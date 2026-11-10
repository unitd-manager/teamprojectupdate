<?
class CP_Admin_Modules_Trading_PaymentTermsLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{

    function getChooseValues() {
        $fn = Zend_Registry::get('fn');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');
        
        $company_id = $fn->getReqParam('record_id');
        $field_name = $fn->getReqParam('field_name');
        $SQL = $this->model->getPaymentTermsSQL($company_id);
        
        $text = $fnsModGrp->getTermsSelectionList($SQL, $field_name);
        return $text;
    }
}