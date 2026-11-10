<?
class CP_Admin_Modules_AgileIms_Feedback_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT f.*
        FROM feedback f
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
        $searchVar->mainTableAlias = 'f';

        $feedback_id = $fn->getReqParam('feedback_id');
        $group       = $fn->getReqParam('group');

        if ($feedback_id != "") {
            $searchVar->sqlSearchVar[] = "f.feedback_id = '{$feedback_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "f.feedback_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'f.feedback_id');

            if ($group != '') {
                $searchVar->sqlSearchVar[] = "f.feedback_group = '{$group}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
												f.feedback_group LIKE '%{$tv['keyword']}%' OR
												f.title          LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('feedback_group', 'Please select Group Name');

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
        $validate->validateData('feedback_group', 'Please select Group Name');
        $validate->validateData('title', 'Please enter the Title');

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

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'feedback_group');
        $fa = $fn->addToFieldsArray($fa, 'published');
        
        return $fa;
    }

    /**
     *
     */
    function getStudentFeedbackSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        if (!$this->getStudentFeedbackValidate()){
            return $validate->getErrorMessageXML();
        }

        $title          = $fn->getPostParam("title");
        $batch_id       = $fn->getReqParam('batch_id');
        $feedback_group = $fn->getPostParam('feedback_group');

        $fa1 = array();
        $fa1['batch_id']        = $batch_id;
        $fa1['title']           = $title;           
        $fa1['feedback_group']  = $feedback_group;           
        $id = $fn->addRecord($fa1, 'batch_feedback');

        foreach($_POST AS $key => $val) {
            if (substr($key, 0, 5) == 'marks'){
                $marks_arr = explode('_', $key);
                $student_id = $marks_arr[1];
                $feedback_id  = $marks_arr[2];
                $markVal = $val;

                if ($markVal) {
                    $fa = array();
                    $fa['contact_id'] = $student_id;
                    $fa['batch_id']   = $batch_id;
                    $fa['feedback_id']= $feedback_id;
                    $fa['marks']      = $markVal;           
                    $fa['batch_feedback_id'] = $id;           
                    $fn->addRecord($fa, 'student_feedback');   
                }
            }
        }
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditStudentFeedbackSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        if (!$this->getStudentFeedbackValidate()){
            return $validate->getErrorMessageXML();
        }

        $title         = $fn->getPostParam("title");
        $batch_id      = $fn->getReqParam('batch_id');
        $batchFeedback = $fn->getRecordByCondition('batch_feedback', "batch_id = '{$batch_id}'");

        $fa1 = array();
        $fa1['title'] = $title;           
        $id = $fn->saveRecord($fa1, 'batch_feedback', 'batch_feedback_id', $batchFeedback['batch_feedback_id']);            

        foreach($_POST AS $key => $val){
            if (substr($key, 0, 5) == 'marks'){
                $marks_arr = explode('_', $key);
                $student_id = $marks_arr[1];
                $feedback_id  = $marks_arr[2];
                $student_feedback_id  = $marks_arr[3];
                $markVal = $val;
                
                if ($student_feedback_id != ''){
                    $fa = array();
                    $fa['marks']      = $markVal;           
                    $fn->saveRecord($fa, 'student_feedback', 'student_feedback_id', $student_feedback_id);
                } else {
                    $fa = array();
                    $fa['contact_id'] = $student_id;
                    $fa['batch_id']   = $batch_id;
                    $fa['feedback_id']= $feedback_id;
                    $fa['marks']      = $markVal;           
                    $fa['batch_feedback_id'] = $id;           
                    $fn->addRecord($fa, 'student_feedback');
                }
             }
        }
        return $validate->getSuccessMessageXML();
    }
    
    /**
     *
     */
    function getStudentFeedbackValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
