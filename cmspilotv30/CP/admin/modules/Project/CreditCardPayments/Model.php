<?
class CP_Admin_Modules_Project_CreditCardPayments_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT ccp.*
        FROM credit_card_payment ccp
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'ccp';

        $refund_id     = $fn->getReqParam('credit_card_payment_id');
        $month = $fn->getReqParam('month');

        if ($refund_id != "") {
            $searchVar->sqlSearchVar[] = "ccp.credit_card_payment_id = '{$credit_card_payment_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "ccp.credit_card_payment_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'ccp.credit_card_payment_id');

            if ($month == '') {
                $month = 'Current Month';
            }

            if ($month == "Current Month") {
                $searchVar->sqlSearchVar[] = "month(due_date) = MONTH(NOW())";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        ccp.type_of_card   LIKE '%{$tv['keyword']}%'
                                       )";
            }
        }        
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'type_of_card');
        $fa = $fn->addToFieldsArray($fa, 'due_date');
        $fa = $fn->addToFieldsArray($fa, 'date_of_statement');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'minimum_amount');
        $fa = $fn->addToFieldsArray($fa, 'reminder');
        $fa = $fn->addToFieldsArray($fa, 'reminder_date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'credit_card_payment_id');
        $fa = $fn->addToFieldsArray($fa, 'paid_date');
        
        return $fa;
    }

}
