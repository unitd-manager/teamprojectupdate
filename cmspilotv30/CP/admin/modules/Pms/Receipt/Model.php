<?
class CP_Admin_Modules_Pms_Receipt_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
                
        if ($cpCfg['cp.forAceIms']) {
            $SQL = "
            SELECT r.*
                ,cont.contact_id
                ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
                ,co.company_id
                ,co.title
            FROM receipt r
            LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
            LEFT JOIN (contact cont) ON (o.contact_id = cont.contact_id)
            LEFT JOIN (company co) ON (o.company_id = co.company_id)
            ";
        } else {
            $SQL = "
            SELECT DISTINCT r.receipt_id
                  ,r.receipt_code
                  ,r.amount
                  ,r.mode_of_payment
                  ,r.cheque_no
                  ,r.cheque_date
                  ,r.bank_name
                  ,r.remarks
                  ,r.receipt_status
                  ,r.date
                  ,r.flag
                  ,r.order_id
                  ,r.creation_date
                  ,r.modification_date
                  ,c.contact_id
                  ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (contact c) ON (i.contact_id = c.contact_id)
            ";
        }
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'r';

        $receipt_id     = $fn->getReqParam('receipt_id');
        $receipt_date1  = $fn->getReqParam('receipt_date1');
        $receipt_date2  = $fn->getReqParam('receipt_date2');
        $mode_of_payment = $fn->getReqParam('mode_of_payment');

        if ($receipt_id != "") {
            $searchVar->sqlSearchVar[] = "r.receipt_id = '{$receipt_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "r.receipt_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'r.receipt_id');

            if ($receipt_date1 != "" && $receipt_date2 != "") {
                $searchVar->sqlSearchVar[] = "(r.date BETWEEN '{$receipt_date1} 00:00:00' AND '{$receipt_date2} 23:59:59')";
            }
    
            if ($mode_of_payment != "") {
                $searchVar->sqlSearchVar[] = "r.mode_of_payment = '{$mode_of_payment}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        r.order_id LIKE '%{$tv['keyword']}%' 
                                     OR r.receipt_code LIKE '%{$tv['keyword']}%'
                                     OR c.first_name LIKE '%{$tv['keyword']}%'
                                     OR c.last_name LIKE '%{$tv['keyword']}%'
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

        $fa = $fn->addToFieldsArray($fa, 'receipt_date');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'order_id');
        
        return $fa;
    }

}
