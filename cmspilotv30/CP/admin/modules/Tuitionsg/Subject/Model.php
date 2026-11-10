<?
class CP_Admin_Modules_Tuitionsg_Subject_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT s.*
        FROM subject s
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
        $searchVar->mainTableAlias = 's';

        $subject_id     = $fn->getReqParam('subject_id');

        if ($subject_id != "") {
            $searchVar->sqlSearchVar[] = "s.subject_id = '{$subject_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.subject_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.subject_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        s.subject_id   LIKE '%{$tv['keyword']}%'
                                     OR s.title        LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('code', 'Please enter Subject code');

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
        $validate->validateData('code', 'Please enter Subject code');

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

        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'fees');
        $fa = $fn->addToFieldsArray($fa, 'synopsys');
        $fa = $fn->addToFieldsArray($fa, 'outcome');

        return $fa;
    }

    /**
     *
     */
    function getSubjectByCourseJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $course_id = $fn->getReqParam('course_id');

        $json  = array();

        if ($course_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT s.subject_id
              ,CONCAT_WS(' - ', s.code, s.title) AS subject_details
        FROM subject s
        LEFT JOIN (course_subject cs) ON (s.subject_id = cs.subject_id)
        WHERE cs.course_id = '{$course_id}'
        ORDER BY s.title ASC
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['subject_id'], "caption" => $row['subject_details']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getAddSubjectToSession(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $subject_id     = $fn->getReqParam('subject_id');
        $contact_id     = $fn->getReqParam('contact_id');
        $checkedVal     = $fn->getReqParam('checkedVal');

        if ($checkedVal == 1) {
            $_SESSION['selectedSubjectIds'][] = $contact_id . '_' . $subject_id;
        } else {
            $s = &$_SESSION['selectedSubjectIds'];
            if(($key = array_search($contact_id . '_' . $subject_id, $s)) !== false){
                unset($s[$key]);
            }
            //below code is to remove related batch id from the batch id session.
            if(is_array($batch_longterm_id_arr)){
                foreach ($_SESSION['selectedBatchIds'] AS $trainee_id_batch_id_subject_id) {
                    $sessionExplode = explode('_', $trainee_id_batch_id_subject_id);

                    if($sessionExplode[0] == $contact_id && $sessionExplode[2] == $subject_id){
                        $s = &$_SESSION['selectedBatchIds'];
                        if(($key = array_search($trainee_id_batch_id_subject_id, $s)) !== false){
                            unset($s[$key]);
                        }
                    }
                }
            }
        }
    }

    /**
     *
     */
    function getAddBatchToSession(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $batch_id     = $fn->getReqParam('batch_id');
        $contact_id   = $fn->getReqParam('contact_id');
        //print_r($_SESSION['selectedBatchIds']);

        $position   = strpos($batch_id, '_');
        $subject_id = substr($batch_id, $position+1);

        foreach ($_SESSION['selectedBatchIds'] AS $trainee_id_batch_id_subject_id) {
            $sessionExplode = explode('_', $trainee_id_batch_id_subject_id);

            if($sessionExplode[0] == $contact_id && $sessionExplode[2] == $subject_id){
                $s = &$_SESSION['selectedBatchIds'];
                if(($key = array_search($trainee_id_batch_id_subject_id, $s)) !== false){
                    unset($s[$key]);
                }
            }
        }

        $_SESSION['selectedBatchIds'][] = $contact_id . '_' . $batch_id;
            //print_r($_SESSION['selectedBatchIds']);
    }

    /**
     *
     */
    function getAddSubjectToSessionInEditEnrollment(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('order_id');

        $_SESSION['selectedSubjectIds'] = array();
        $_SESSION['selectedBatchIds'] = array();

        $sqlSubject  = "
        SELECT ccsh.course_contact_subject_history_id
              ,ccsh.course_contact_id
              ,ccsh.subject_id
              ,ccsh.batch_id
              ,cc.contact_id
        FROM course_contact_subject_history ccsh
        LEFT JOIN course_contact cc ON (ccsh.course_contact_id = cc.course_contact_id)
        WHERE cc.order_id = {$order_id}
        ";
        $result   = $db->sql_query($sqlSubject);

        while ($row = $db->sql_fetchrow($result)) {
            if($row['subject_id']){
                $_SESSION['selectedSubjectIds'][] = $row['contact_id'] . '_' . $row['subject_id'];
            }
            if($row['batch_id']){
                $_SESSION['selectedBatchIds'][] = $row['contact_id'] . '_' . $row['batch_id'] . '_' . $row['subject_id'];
            }
        }
    }
}
