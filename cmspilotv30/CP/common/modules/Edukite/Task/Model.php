<?
class CP_Common_Modules_Edukite_Task_Model extends CP_Common_Lib_ModuleModelAbstract
{
    //==================================================================//
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT t.*
              ,s.title AS subject_title
              ,CONCAT_WS(' ', te.first_name, te.last_name ) AS teacher_name
        FROM task t
       LEFT JOIN (teacher te) ON (t.teacher_id = te.teacher_id )
       LEFT JOIN (subject s)  ON (t.subject_id = s.subject_id )
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
        
        return $fa;
    }

    //==================================================================//

}
