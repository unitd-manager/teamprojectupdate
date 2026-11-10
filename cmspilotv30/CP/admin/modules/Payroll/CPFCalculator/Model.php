<?
class CP_Admin_Modules_Payroll_CPFCalculator_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT cpf.*
              ,(by_employer + by_employee) AS Total
              ,(cap_amount_employer + cap_amount_employee) AS total_cap_amount
        FROM cpf_calculator cpf
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
        $searchVar->mainTableAlias = 'cpf';

        $cpf_calculator_id   = $fn->getReqParam('cpf_calculator_id');
        $year                = $fn->getReqParam('year');

        if ($year == '') {
            if (date('m') == 12) {
                $year  = date('Y') - 1;
            } else {
                $year = date('Y');
            }
        }

        if ($cpf_calculator_id != "") {
            $searchVar->sqlSearchVar[] = "cpf.cpf_calculator_id = '{$cpf_calculator_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "cpf.cpf_calculator_id = '{$tv['record_id']}'";
        } else {
            //$fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.salary_id');

          /*  if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }*/

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "cpf.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(cpf.flag != 1 OR cpf.flag IS null)";
            }

            $searchVar->sqlSearchVar[] = "cpf.year = '{$year}'";

            //$searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('from_age', 'Please enter from age');
        $validate->validateData('to_age', 'Please enter to age');

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
        $fa['year'] = date('Y');
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
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpf_calculator_id   = $fn->getReqParam('cpf_calculator_id');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $cpfRec = $fn->getRecordRowByID('cpf_calculator', 'cpf_calculator_id', $cpf_calculator_id);

        if($cpfRec['from_age'] != $fa['from_age'] ||
           $cpfRec['to_age'] != $fa['to_age'] ||
           $cpfRec['by_employer'] != $fa['by_employer'] ||
           $cpfRec['by_employee'] != $fa['by_employee'] ||
           $cpfRec['year'] != $fa['year']
           ){
            $fa1 = array();
            $fa1['from_age']    = $cpfRec['from_age'];
            $fa1['to_age']      = $cpfRec['to_age'];
            $fa1['by_employer'] = $cpfRec['by_employer'];
            $fa1['by_employee'] = $cpfRec['by_employee'];
            $fa1['year']        = $cpfRec['year'];
            $fa1['cpf_calculator_id'] = $cpfRec['cpf_calculator_id'];
            $fa1['creation_date'] = date("Y-m-d H:i:s");
            $fa1['created_by']    = $fn->getSessionParam('userName');

            $insert = $dbUtil->getInsertSQLStringFromArray($fa1, 'cpf_calculator_history');
            $result = $db->sql_query($insert);
        }

        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'from_age');
        $fa = $fn->addToFieldsArray($fa, 'to_age');
        $fa = $fn->addToFieldsArray($fa, 'by_employer');
        $fa = $fn->addToFieldsArray($fa, 'by_employee');
        $fa = $fn->addToFieldsArray($fa, 'year');
        $fa = $fn->addToFieldsArray($fa, 'cpf_calculator_id');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'from_salary');
        $fa = $fn->addToFieldsArray($fa, 'to_salary');
        $fa = $fn->addToFieldsArray($fa, 'cap_amount_employer');
        $fa = $fn->addToFieldsArray($fa, 'cap_amount_employee');
        $fa = $fn->addToFieldsArray($fa, 'spr_year');

        return $fa;
    }

    /**
     *
     */

}
