<?
class CP_Www_Modules_Edukloud_Task_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_student') {
           $SQL = "
            SELECT t.*
                  ,sub.title AS subject_title
                  ,ts.student_id
                  ,ts.mark AS mark
                  ,ts.grade AS grade
                  ,ts.special_notes AS special_notes
                  ,ts.task_student_id
                  ,ts.status AS task_student_status
                  ,ts.star_rating
                  ,ts.favourite
                  ,CONCAT_WS(' ', st.first_name, st.last_name) AS staff_name
                  ,(SELECT count(*)
                    FROM comment
                    WHERE read_status = 0
                      AND record_id = ts.task_student_id
                      AND user_type = 'Staff'
                      AND room_name = 'Task Student'
                   ) AS comment_no_read_count
            FROM task t
		    LEFT JOIN (subject sub) ON (t.subject_id = sub.subject_id) 
		    JOIN (task_student ts) ON (t.task_id = ts.task_id) 
		    LEFT JOIN (student s) ON (ts.student_id = s.student_id) 
		    LEFT JOIN (staff st) ON (t.staff_id = st.staff_id) 
            ";
        } else if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_parent') {
            $SQL = "
            SELECT t.*
                  ,sub.title AS subject_title
                  ,ts.mark AS mark
                  ,ts.grade AS grade
                  ,ts.special_notes AS special_notes
                  ,ts.task_student_id
                  ,ts.student_status
                  ,ts.star_rating
                  ,ts.favourite
                  ,ts.status AS task_student_status
                  ,CONCAT_WS(' ', st.first_name, st.last_name) AS staff_name
                  ,(SELECT count(*)
                    FROM comment
                    WHERE read_status = 0
                      AND record_id = ts.task_student_id
                      AND user_type = 'Staff'
                      AND room_name = 'Task Student'
                   ) AS comment_no_read_count
            FROM task t
		    LEFT JOIN (subject sub) ON (t.subject_id  = sub.subject_id) 
		    JOIN (task_student ts) ON (t.task_id = ts.task_id) 
		    LEFT JOIN (staff st) ON (t.staff_id = st.staff_id) 
            ";
        } else {
            $SQL = "
            SELECT t.*
                  ,sub.title AS subject_title 
            FROM task t
		    LEFT JOIN (subject sub) ON (t.subject_id = sub.subject_id)
            ";
        }
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 't';

        $type = $fn->getReqParam('type');
        $subject_id = $fn->getReqParam('subject_id');
        $status = $fn->getReqParam('status');
        $staff_id = $fn->getReqParam('staff_id');
        $current_date =  date('Y-m-d');
        $current_time =  date('H:i');

        $searchVar->sqlSearchVar['published'] = "t.published = 1";

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar['task_id'] = "t.task_id = '{$tv['record_id']}'";
        }
        
        if ($tv['categoryType'] == 'EAE'){
            $this->sqlSearchVar[] = "t.type = 'EAE'";
            if ($_SESSION['cpLoginTypeWWW'] != 'edukloud_staff') {
                $this->sqlSearchVar[] = "t.start_time <= '{$current_time}'";
                $this->sqlSearchVar[] = "t.end_time >= '{$current_time}'";
            }
        } else {
            $this->sqlSearchVar[] = "t.type != 'EAE'";
        }    

        if ($_SESSION['cpLoginTypeWWW'] != 'edukloud_staff') {
        	$this->sqlSearchVar[] = "t.launch_date <= '{$current_date}'";
        	$this->sqlSearchVar[] = "t.expiry_date >= '{$current_date}'";
        }

        if ($tv['categoryType'] == 'My Info' || $tv['categoryType'] == "Child's Info"){
            $this->sqlSearchVar[] = "t.type = 'Info'";
        }

        if ($tv['categoryType'] == 'Favourite Task' || $tv['categoryType'] == "Child's Favourite Task"){
            $this->sqlSearchVar[] = "ts.favourite = 1";
        }

        if ($type != ''){
            $this->sqlSearchVar[] = "t.type = '{$type}'";
        }

        if ($subject_id != '' ){
            $this->sqlSearchVar[] = "t.subject_id = '{$subject_id}'";
        }

        if ($status != '' ){
            $this->sqlSearchVar[] = "t.status = '{$status}'";
        }

        if ($staff_id != '' ){
            $this->sqlSearchVar[] = "st.staff_id = '{$staff_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               t.title LIKE '%{$tv['keyword']}%' OR
               t.description LIKE '%{$tv['keyword']}%'
            )";
        }        

        if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_student') {
            $this->sqlSearchVar[] = "ts.student_id  = {$_SESSION['cpContactId']}";
            print $_SESSION['cpContactId'];
            if ($tv['categoryType'] == 'Task'){
                $this->sqlSearchVar[] = "t.type != 'Info'";
            }
        } else if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_parent') {
            if ($tv['categoryType'] == 'Task'){
                $this->sqlSearchVar[] = "t.type != 'Info'";
            }
            $this->sqlSearchVar[] = "ts.student_id  = {$_SESSION['student_id']}";
        } else if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_staff') {
            $this->sqlSearchVar[] = "t.staff_id = {$_SESSION['cpContactId']}";
        }

    }

    /**
     *
     */
    function getNewTaskSubmit() {
        checkLoggedIn();
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewTaskSubmitValidate()){
            return $validate->getErrorMessageXML();
        }
        //-----------------------------------------------------------------------//
        $fa = array();

        $task_id        = $fn->getReqParam('task_id');
        
        $fa['title']       = $fn->getPostParam('title');
        $fa['subject_id']  = $fn->getPostParam('subject_id');
        //$fa['status']      = $cpCfg['defaultTaskStatus'];
        $fa['type']        = $fn->getPostParam('type');
        $fa['due_date']    = $fn->getPostParam('due_date');
        $fa['launch_date'] = $fn->getPostParam('launch_date');
        $fa['expiry_date'] = $fn->getPostParam('expiry_date');
        $fa['description'] = $fn->getPostParam('description');
        $fa['staff_id']    = $fn->getPostParam('staff_id');
        $fa['published']   = 1;
        $fa['creation_date'] = date('Y-m-d H:i:s');
        
        $SQL        = $dbUtil->getInsertSQLStringFromArray($fa, 'task');
        $result     = $db->sql_query($SQL);
        $task_id    = $db->sql_nextid();
        
        $editUrl = $cpUrl->getUrlByCatType('Task') . '?_action=editTask&task_id=' . $task_id;
        $xmlText = $validate->getSuccessMessageXML($editUrl);

        return $xmlText;
    }

    /**
     *
     */
    function getNewTaskSubmitValidate() {
        checkLoggedIn();
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('title', $ln->gd('titleError'));
        $validate->validateData('description', $ln->gd('descriptionError'));
        $validate->validateData('subject_id', $ln->gd('subjectError'));
        $validate->validateData('type', $ln->gd('typeError'));
        
        $current_date = date('Y-m-d');
        $due_date     = $fn->getPostParam('due_date');
        $launch_date  = $fn->getPostParam('launch_date');
        $expiry_date  = $fn->getPostParam('expiry_date');
        
        if ($due_date < $current_date) {
            $validate->errorArray['due_date']['name'] = 'due_date';
            $validate->errorArray['due_date']['msg']  = $ln->gd('dueDateWrongError');
        }    

        if ($launch_date < $current_date) {
            $validate->errorArray['launch_date']['name'] = 'launch_date';
            $validate->errorArray['launch_date']['msg']  = $ln->gd('launchDateWrongError');
        }    
        
        if ($expiry_date < $current_date) {
            $validate->errorArray['expiry_date']['name'] = 'expiry_date';
            $validate->errorArray['expiry_date']['msg']  = $ln->gd('expiryDateWrongError');
        }    
        
        $validate->validateData('due_date', $ln->gd('dueDateError'));

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditTaskSubmit() {
        checkLoggedIn();
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditTaskValidate()){
            return $validate->getErrorMessageXML();
        }
        //-----------------------------------------------------------------------//

        $task_id     = $fn->getReqParam('task_id');
        $title       = $fn->getReqParam('title');
        $type        = $fn->getReqParam('type');
        $description = $fn->getReqParam('description');
        $due_date    = $fn->getReqParam('due_date');
        $launch_date = $fn->getReqParam('launch_date');
        $expiry_date = $fn->getReqParam('expiry_date');
        //-----------------------------------------------------------------------//

        $fa = array();
        $fa['task_id']           = $task_id;
        $fa['title']             = $title;
        $fa['type']              = $type;
        $fa['subject_id']        = isset($_POST['subject_id'])  ? $_POST['subject_id']  : '';
        $fa['due_date']          = $due_date;
        $fa['launch_date']       = $launch_date;
        $fa['expiry_date']       = $expiry_date;
        $fa['description']       = $description;
        $fa['modification_date'] = date('Y-m-d H:i:s');


        $whereCondition = "WHERE task_id = {$task_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'task', $whereCondition);
        $result = $db->sql_query($SQL);

        $editUrl = $cpUrl->getUrlByCatType('Task');
        $xmlText = $validate->getSuccessMessageXML($editUrl);

        return $xmlText;
    }

    /**
     *
     */
    function getEditTaskValidate() {
        checkLoggedIn();
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('title', $ln->gd('titleError'));
        $validate->validateData('description',  $ln->gd('descriptionError'));

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEdukloudTaskEdukloudStudentLinkSQL($id) {

        $SQL = "
        SELECT ts.task_student_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS name
              ,c.title
              ,ts.star_rating
        FROM task_student ts
        LEFT JOIN student s ON (s.student_id = ts.student_id)
        LEFT JOIN class c ON (c.class_id = s.class_id)
        WHERE ts.task_id = {$id}
        ";

        return $SQL;
    }

}
