<?
class CP_Admin_Modules_Ek_Task_Model extends CP_Common_Lib_ModuleModelAbstract
{
    //==================================================================//
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT t.*
              ,b.title AS class_title
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS staff_name
              ,d.title AS subject_title
        FROM task t
       LEFT JOIN (class b)   ON (t.class_id   = b.class_id )
       LEFT JOIN (staff c)   ON (t.staff_id   = c.staff_id )
       LEFT JOIN (subject d) ON (t.subject_id = d.subject_id )
        ";

        return $SQL;
    }

    //==================================================================//
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');
        $searchVar->mainTableAlias = 't';

        $taskType     = $fn->getReqParam('type');
        $class_id     = $fn->getReqParam('class_id');
        $subject_id   = $fn->getReqParam('subject_id');
        $staff_id     = $fn->getReqParam('staff_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.task_id = '{$tv['record_id']}'";
        }
        if ($taskType != '') {
            $searchVar->sqlSearchVar[] = "t.type = '{$taskType}'";
        }
        if ($subject_id != '') {
            $searchVar->sqlSearchVar[] = "t.subject_id = '{$subject_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               t.title LIKE '%{$tv['keyword']}%'
            )";
        }        
    }

    //========================================================//
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    //==================================================================//
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

    //========================================================//
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    //==================================================================//
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

    //==================================================================//
    function getFields() {
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'class_id');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'subject_id');
        $fa = $fn->addToFieldsArray($fa, 'type');
        $fa = $fn->addToFieldsArray($fa, 'due_date');
        $fa = $fn->addToFieldsArray($fa, 'launch_date');
        $fa = $fn->addToFieldsArray($fa, 'expiry_date');
        $fa = $fn->addToFieldsArray($fa, 'links');
        $fa = $fn->addToFieldsArray($fa, 'embed_text');
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        
        //-----------------------------------------------------------------------//
        if($cpCfg['generateSEOUrl'] == 1 && ($tv['lang'] == "eng" || $tv['lang'] == "")){
            $fa['seo_title'] = strtolower( $fn->_prepare_url_text($fa[$titleLang]));
        }

        return $fa;
    }

    //==================================================================//

}
