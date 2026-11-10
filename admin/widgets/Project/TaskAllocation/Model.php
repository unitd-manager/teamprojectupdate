<?
class CPL_Admin_Widgets_Project_TaskAllocation_Model extends CP_Admin_Widgets_Project_TaskAllocation_Model
{
    /**
     * Adding new task from dashboard form submit
     */
    function getAddTaskFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getAddTaskValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'project_id');
        $fa = $fn->addToFieldsArray($fa, 'task_id');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'estd_hrs');
        $fa = $fn->addToFieldsArray($fa, 'comments');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'start_date');
        $fa = $fn->addToFieldsArray($fa, 'end_date');
        $fa = $fn->addToFieldsArray($fa, 'send_mail');
        $fa['progress_percent'] = '0%';

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'task_history');
        $result = $db->sql_query($SQL);
        $task_history_id    = $db->sql_nextid();
        
        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");
        //-----------------------------------------------------------------//
        $project_id  = $fn->getReqParam('project_id');
        $task_id     = $fn->getReqParam('task_id');
        $staff_id    = $fn->getReqParam('staff_id');

        $projectTitle = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $taskTitle    = $fn->getRecordRowByID('task', 'task_id', $task_id);
        $staffEmail   = $fn->getRecordRowByID('staff', 'staff_id', $staff_id);        
                
        $headerText = "
        <p>Dear {$staffEmail['first_name']} {$staffEmail['last_name']}</p>
        <pre>{$fa['comments']}</pre>        
        <p>Thanks <br> {$_SESSION['userFullNameWWW']}</p>
        ";

        $text = "
        <table border='0'>
            <tbody>
                {$headerText}
            </tbody>
        </table>
        ";

        $message = $text;
        $subject = $projectTitle['title'] . ' - ' . $taskTitle['title'];
        
        $fromName    = $_SESSION['userFullNameWWW'];
        $fromEmail   = $_SESSION['email'];
        
        $toName = "{$staffEmail['first_name']} {$staffEmail['last_name']}";
        $toEmail = $staffEmail['email'];

        if ($fa['send_mail'] == 1) {
            $smtp  = includeCPClass('Lib', 'smtp', 'CPSMTP');
            $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);
        }               

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddTaskValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('comments', 'Please enter the date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Task email submit
     */
    function getTaskMailSubmit(){
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $today = date('M d Y');

        $SQL = "
        SELECT th.task_history_id
              ,th.title
              ,th.task_id
              ,th.project_id
              ,th.status
              ,th.priority
              ,th.progress_percent
              ,th.task_history_now
              ,th.comments
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,th.start_date
              ,th.end_date
              ,th.comments
              ,th.staff_id
              ,s.email
              ,t.title AS task_title 
              ,t.status AS task_status
              ,p.title AS project_title             
              ,p.status AS project_status
        FROM task_history th
        LEFT JOIN staff s ON (s.staff_id = th.staff_id)
        LEFT JOIN task t ON (t.task_id = th.task_id)
        LEFT JOIN project p ON (p.project_id = th.project_id)
        WHERE th.status != 'Completed'
            AND s.staff_id = '{$_SESSION['staff_id']}' 
            AND s.status = 'Current' 
            AND s.team = 'In-house'
        ORDER BY th.priority ASC
        ";
        $result  = $db->sql_query($SQL);

        $headerText = '';

        while ($row = $db->sql_fetchrow($result)) {
            $headerText .= "
            <tr>
                <td>{$row['project_title']}</td>
                <td>{$row['title']}</td>
                <td>{$row['comments']}</td>
                <td>{$row['status']}</td>
                <td>{$row['priority']}</td>
                <td>{$row['progress_percent']}</td>
                <td>{$row['end_date']}</td>
                <td>{$row['start_date']}</td>
            </tr>
            ";
           $staffName = $row['staff_name'];
           $staffEmail = $row['email'];

        }
        

        $text = "
        <p>
          Dear Bro Syed, <br><br>
          I have below tasks to do:
        </p>
        <table border='1'>
            <tbody>
              <tr>
                  <td><b>Project Name</b></td>
                  <td><b>Title</b></td>
                  <td><b>Description</b></td>
                  <td><b>Status</b></td>
                  <td><b>Priority</b></td>
                  <td><b>Progress %</b></td>
                  <td><b>End Date</b></td>
                  <td><b>Start Date</b></td>
              </tr>
              {$headerText}
            </tbody>
        </table>
        <p>
          Thanks,<br>
          $staffName
        </p>
        ";


        $message     = $text;
        //print date("d/m/y : H:i:s", time());

        $subject     = "USS Task" ." - " .$staffName . " " .$today;
        $fromName    = $staffName;
        $fromEmail   = $staffEmail;
        
        $toName      = "USS Tech";
        $toEmail     = "syed@usoftsolutions.com";

        $smtp  = includeCPClass('Lib', 'smtp', 'CPSMTP');
        $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);
        return $validate->getSuccessMessageXML();
    }

    /**
     * Changing staff in task history from dashboard list
     */
    function getUpdateTaskHistoryStaffIdByStaff() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $staff_id        = $fn->getReqParam('staff_id');
        $task_history_id = $fn->getReqParam('task_history_id');

        $SQL = "
        UPDATE task_history
        SET staff_id = '{$staff_id}'
        WHERE task_history_id = {$task_history_id}
        ";
        $result  = $db->sql_query($SQL);
    }

    /**
     * Changing priority in task history from dashboard list
     */
    function getUpdateTaskHistoryPriorityByStaff() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $priority           = $fn->getReqParam('priority');
        $task_history_id    = $fn->getReqParam('task_history_id');

        $SQL = "
        UPDATE task_history
        SET priority = '{$priority}'
        WHERE task_history_id = {$task_history_id}
        ";
        $result  = $db->sql_query($SQL);
    }

    /**
     * Changing progress percent in task history from dashboard list
     */
    function getUpdateTaskHistoryProgressPercentByStaff() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $progress_percent   = $fn->getReqParam('progress_percent');
        $task_history_id    = $fn->getReqParam('task_history_id');

        $SQL = "
        UPDATE task_history
        SET progress_percent = '{$progress_percent}'
        WHERE task_history_id = {$task_history_id}
        ";
        $result  = $db->sql_query($SQL);
    }

    /**
     * Changing task status in task history from dashboard list
     */
    function getUpdateTaskHistoryStatusByStaff() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $today              = date('Y-m-d');
        $status             = $fn->getReqParam('status');
        $task_history_id    = $fn->getReqParam('task_history_id');

        if ($status == 'Resolved') {
          $SQL1 = "
          UPDATE task_history
          SET end_date = '{$today}'
          WHERE task_history_id = {$task_history_id}
          ";
        }

        $SQL = "
        UPDATE task_history
        SET status = '{$status}'
        WHERE task_history_id = {$task_history_id}
        ";
        $result  = $db->sql_query($SQL);
        $result1 = $db->sql_query($SQL1);
    }

    /**
     * Updating Estimated hours in task history from dashboard list
     */
    function getUpdateTaskHistoryEstimatedHoursByStaff() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $estd_hrs           = $fn->getReqParam('estd_hrs');
        $task_history_id    = $fn->getReqParam('task_history_id');

        $SQL = "
        UPDATE task_history
        SET estd_hrs = '{$estd_hrs}'
        WHERE task_history_id = {$task_history_id}
        ";
        $result  = $db->sql_query($SQL);
    }

    /**
     * Task History edit form submit
     */
    function getTimeSheetEditSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getTimeSheetEditValidate()){
            return $validate->getTimeSheetEditValidate();
        }

        $task_history_id = $fn->getReqParam('task_history_id');
        $project_id      = $fn->getReqParam('project_id');
        $task_id         = $fn->getReqParam('task_id');
        $staff_id        = $fn->getReqParam('staff_id');
                         
        $sqlStaffEmail = "
        SELECT * 
        FROM staff 
        WHERE staff_id = '{$staff_id}'   
        ";        

		$resultEmail = $db->sql_query($sqlStaffEmail);
		$staffEmail = $db->sql_fetchrow($resultEmail);

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");
        //-----------------------------------------------------------------//

        $projectTitle = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $taskTitle    = $fn->getRecordRowByID('task', 'task_id', $task_id);

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'comments');
        $fa = $fn->addToFieldsArray($fa, 'send_mail');

        $whereCondition = "WHERE task_history_id = {$task_history_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "task_history", $whereCondition);
        $db->sql_query($SQL);

        $headerText = "
        <p>Dear {$staffEmail['first_name']} {$staffEmail['last_name']}</p>
        <pre>{$fa['comments']}</pre>        
        <p>Thanks <br> {$_SESSION['userFullNameWWW']}</p>
        ";


        $text = "
        <table border='0'>
            <tbody>
                {$headerText}
            </tbody>
        </table>
        ";

        $message = $text;
        $subject = $projectTitle['title'] . ' - ' . $taskTitle['title'];
        
        $fromName    = $_SESSION['userFullNameWWW'];
        $fromEmail   = $_SESSION['email'];       

        $toName = "{$staffEmail['first_name']} {$staffEmail['last_name']}";
        $toEmail = $staffEmail['email'];

        if ($fa['send_mail'] == 1) {
            $smtp  = includeCPClass('Lib', 'smtp', 'CPSMTP');
            $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);
        }                      

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getTimeSheetEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('comments', 'Please update the comments');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updating of task history status through email form submit
     */
    function getSendEmailSubmit(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if (!$this->getSendEmailValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'email_to');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'comments');
        $fa = $fn->addToFieldsArray($fa, 'status');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y");
        //-----------------------------------------------------------------//

        $sqlStaff = "
        SELECT CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
              ,a.staff_type
        FROM staff a
        WHERE a.email = '{$fa['email_to']}'
        ";        
        $result  = $db->sql_query($sqlStaff);
        $row     = $db->sql_fetchrow($result);
        
        if ($fa['status'] == 'Update') {
            $headerText = "
            <p>Dear {$row['staff_name']}</p>
            <p>I would like to have an update for the below task details.</p>
            <pre style='font-size:12px'>{$fa['comments']}</pre>
            <p>Thanks <br> {$_SESSION['userFullNameWWW']}</p>
            ";
        } else {
            $headerText = "
            <p>Dear {$row['staff_name']}</p>
            <pre>{$fa['comments']}</pre>
            <p>Thanks <br> {$_SESSION['userFullNameWWW']}</p>
        ";
        }

        if ($row['staff_type'] == 'Project Manager'){
    	} 	

        $text = "
        <table border='0'>
            <tbody>
                {$headerText}
            </tbody>
        </table>
        ";
  
        $message = $text;
        $subject = $fa['title'] . ' - ' . $fa['status'] . ' - ' . $currentDate;
        
        $fromName    = $_SESSION['userFullNameWWW'];
        $fromEmail   = $_SESSION['email'];
        
        $toName = "{$row['staff_name']}";
        $toEmail = $fa['email_to'];

        $smtp  = includeCPClass('Lib', 'smtp', 'CPSMTP');
        $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);

        return $validate->getSuccessMessageXML();
     
    }

    /**
     *
     */
    function getSendEmailValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('status', 'Please select the status');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checking the checkbox for updating working status
     */
    function getUpdateTaskHistoryNowByStaff() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $task_history_now = $fn->getReqParam('task_history_now');
        $task_history_id  = $fn->getReqParam('task_history_id');

        if ($task_history_now == 0) {
            $task_history_now = "NULL";
        }

        $SQL = "
        UPDATE task_history
        SET task_history_now = {$task_history_now}
        WHERE task_history_id = {$task_history_id}
        ";
        $result  = $db->sql_query($SQL);
    }


}