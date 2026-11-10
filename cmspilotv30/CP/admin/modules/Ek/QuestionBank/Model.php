<?
class CP_Admin_Modules_Ek_QuestionBank_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT qb.*, 
               sb.title as subject_title
        FROM   `question_bank` qb
        LEFT JOIN (subject sb) ON (qb.subject_id = sb.subject_id)
        ";

        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');
        $searchVar->mainTableAlias = 'qb';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "qb.question_bank_id = '{$tv['record_id']}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               qb.question LIKE '%{$tv['keyword']}%'
            OR sb.title LIKE '%{$tv['keyword']}%'
            )";
        }        
    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('question', 'Please enter the question');

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
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('question', 'Please enter the question');

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
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'question');
        $fa = $fn->addToFieldsArray($fa, 'subject_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'launch_date');
        $fa = $fn->addToFieldsArray($fa, 'expiry_date');

        return $fa;
    }
}
