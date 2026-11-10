<?
class CPL_Admin_Widgets_EnggCrm_TaskFromAdmin_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $assing_to  = $fn->getReqParam('assing_to');
        $showAllRecords     = $fn->getReqParam('showAllRecords');

        $add = "
        <div class='float_left ml20'>
            <a class='addNotification'><u>Add</u></a>
        </div>";

        if($showAllRecords == 1){
            $showAll = "
            <div class='float_left ml20'>
                <a class='showIncompleteTask'><u>Show Incomplete</u></a>
            </div>";
        } else {
            $showAll = "
            <div class='float_left ml20'>
                <a class='showAllNotification'><u>Show Completed</u></a>
            </div>";            
        }

        $assignToArray = array(
            "Assign to You"  => "Assign to You"
           ,"Assign to Others" => "Assign to Others"
        );

        $assingToStaff = '';
        if ($_SESSION['userGroupName'] != 'SATHISH' || $_SESSION['userGroupName'] != 'SHANKAR') {
            $assingToStaff = "
            <div class='float_left mb5 ml20'>
                <select name='assing_to'>
                    {$cpUtil->getDropDownFromArr($assignToArray, $assing_to)}
                </select>
            </div>";
        }

        $text = "
        <h2 class='ui-widget-header ui-corner-top'>
            <div class='floatbox invoiceSummaryfilter'>
                <div class='float_left'>
                    Tasks
                </div>
                {$add}
                {$showAll}
                {$assingToStaff}
            </div>
        </h2>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr bgcolor='#90ee90'>
                    <th>Date</th>
                    <th>Link</th>
                    <th>Task</th>
                    <th>Due Date</th>
                    <th>Assigned To</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>
                {$this->getRowsHTML()}
            </tbody>
        </table>
        </div>
        ";

        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
		$creation_date = '';
        $showAllRecords     = $fn->getReqParam('showAllRecords');
        $assing_to  = $fn->getReqParam('assing_to');

        $rows = '';

        $SQLStaff = "
        SELECT e.employee_id
        FROM staff s
        LEFT JOIN employee e ON (e.employee_id = s.employee_id)
        WHERE s.staff_id = {$_SESSION['staff_id']}
        ";
        
        $resultStaff  = $db->sql_query($SQLStaff);
        $rowStaff = $db->sql_fetchrow($resultStaff);

        $sqlAppendStatus = '';
        $sqlAppendAssignTo = '';
        if($showAllRecords == 1){
            $sqlAppendStatus = "AND o.alert_status = 1";
            if ($_SESSION['userGroupName'] != 'Super Administrator' AND $_SESSION['userGroupName'] != 'SATHISH') {
                $sqlAppendAssignTo = "AND {$rowStaff['employee_id']} IN (o.staff_ids, o.staff_id_created)";
            }
        } else {
            $sqlAppendStatus = "AND (o.alert_status IS NULL OR o.alert_status = '' OR o.alert_status = 0)";        
            if ($_SESSION['userGroupName'] != 'Super Administrator' AND $_SESSION['userGroupName'] != 'SATHISH' AND $_SESSION['userGroupName'] != 'SHANKAR') {
                //$sqlAppendStaff = "AND {$rowStaff['employee_id']} IN (o.staff_ids, o.staff_id_created)";
                if($assing_to == 'Assign to Others'){
                    $sqlAppendAssignTo = "AND {$rowStaff['employee_id']} IN (o.staff_id_created)";
                } else {
                    $sqlAppendAssignTo = "AND {$rowStaff['employee_id']} IN (o.staff_ids)";
                }
            }
        }

        $SQL = "
        SELECT o.*
        FROM opportunity_project_history o
        WHERE o.date != ''
        AND o.type = 'Task'
        {$sqlAppendAssignTo}
        {$sqlAppendStatus}
        ORDER BY o.opportunity_project_history_id DESC
        ";
        $result  = $db->sql_query($SQL);
		while ($row = $db->sql_fetchrow($result)) {
	        $creation_date  = $dateUtil->formatDate($row['date'] , "DD-MM-YYYY");
            $due_date  = $dateUtil->formatDate($row['due_date'] , "DD-MM-YYYY");
            $employeeRec = $fn->getRecordRowByID('employee', 'employee_id', $row['staff_ids']);
            $employeeCreatedRec = $fn->getRecordRowByID('employee', 'employee_id', $row['staff_id_created']);
            $editLink = '';
            if($row['project_id'] != ''){
                $editLink = "<a href='index.php?_topRm=project&module=enggCrm_project&_action=edit&project_id={$row['project_id']}' target='_blank'><u>link</u></a>";
            } else if($row['link'] != ''){
                $editLink = "<a href='{$row['link']}' target='_blank'><u>link</u></a>";
            }

            if($row['alert_status'] == 1){
                $updateBtn = "
                <a class='btn btn-danger updateNotRead' opportunity_project_history_id='{$row['opportunity_project_history_id']}' href='#'>Incomplete</a>
                ";
            } else {
                $updateBtn = "
                <a class='btn btn-success updateIsRead' opportunity_project_history_id='{$row['opportunity_project_history_id']}' href='#'>Completed</a>
                ";
            }

            $SQLStaff = "
            SELECT e.employee_id
            FROM staff s
            LEFT JOIN employee e ON (e.employee_id = s.employee_id)
            WHERE s.staff_id = {$_SESSION['staff_id']}
            ";
            
            $resultStaff  = $db->sql_query($SQLStaff);
            $rowStaff = $db->sql_fetchrow($resultStaff);

            $updateCompleteBtn = '';
            if($row['staff_id_created'] == $rowStaff['employee_id'] OR $_SESSION['userGroupName'] == 'Super Administrator' OR $_SESSION['userGroupName'] == 'SATHISH' OR $_SESSION['userGroupName'] == 'SHANKAR'){
                $updateCompleteBtn = "{$updateBtn}";
            }

            $updateStatusBtn = '';
            if($row['emp_task_status'] == 1 && $row['alert_status'] != 1){
                $updateStatusBtn = "<span class='highlightCompleted'></span>";
            } else if($row['emp_task_status'] != 1){
                if($row['staff_ids'] == $rowStaff['employee_id'] OR $_SESSION['userGroupName'] == 'Super Administrator' OR $_SESSION['userGroupName'] == 'SATHISH' OR $_SESSION['userGroupName'] == 'SHANKAR'){
                    $updateStatusBtn = "
                    <span class='highlightIncomplete'></span><a class='btn btn-info updateStatusIsRead' opportunity_project_history_id='{$row['opportunity_project_history_id']}' href='#'>Completed</a>
                    ";
                }
            }

			$rows .= "
			<tr>
				<td>{$creation_date}</td>
                <td>{$editLink}</td>
				<td>{$row['title']}</td>
                <td>{$due_date}</td>
                <td>{$updateStatusBtn} {$employeeRec['first_name']}</td>
                <td>{$updateCompleteBtn} {$employeeCreatedRec['first_name']}</td>
			</tr>
			";
		}

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getAddNotification() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $today = date("Y-m-d");
        
        $formAction = "index.php?widget=enggCrm_taskFromAdmin&_spAction=notificationSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        WHERE e.status != 'Archive'
        ORDER BY employee_name
        ";
        
        $text = "
        <form id='notificationsForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTARow('Task', 'title')}
            {$formObj->getDateRow('Due Date', 'due_date')}
            {$formObj->getDDRowBySQL('Assign To', 'staff_id', $sqlEmployeeName)}
            {$formObj->getTBRow('Link', 'link')}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getNotificationMessageCount() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        
        $text = "";
        $jsonArray = array();

        $SQLStaff = "
        SELECT e.employee_id
        FROM staff s
        LEFT JOIN employee e ON (e.employee_id = s.employee_id)
        WHERE s.staff_id = {$_SESSION['staff_id']}
        ";        
        $resultStaff  = $db->sql_query($SQLStaff);
        $rowStaff = $db->sql_fetchrow($resultStaff);

        $sqlAppend = '';
        if ($_SESSION['userGroupName'] != 'Super Administrator' AND $_SESSION['userGroupName'] != 'SATHISH' AND $_SESSION['userGroupName'] == 'SHANKAR') {
            $sqlAppend = "AND {$rowStaff['employee_id']} IN (o.staff_ids)";
        }

        $SQLNotification = "
        SELECT o.*
        FROM opportunity_project_history o
        WHERE o.date != ''
        AND (o.alert_status IS NULL OR o.alert_status = '' OR o.alert_status = 0)
        {$sqlAppend}
        ";
        $resultNotification  = $db->sql_query($SQLNotification);
        $numRowsNotification = $db->sql_numrows($resultNotification);
        if($_SESSION['notification_count'] != $numRowsNotification){

            $messageCount = "";
            if($numRowsNotification > 0) {
                $messageCount = "
                <span class='badge btn-danger blinkingBackground'>{$numRowsNotification}</span>
                ";
            }
            
            $text = "
            <a href='/admin/index.php?_topRm=project&module=common_dashboard' class='messageBell'><i class='fa fa-bell' aria-hidden='true'></i>
            {$messageCount}
            <a>
            ";

            if($numRowsNotification > $_SESSION['notification_count']){
                $jsonArray['countNote']  = 'Yes';
            }
            else{
                $jsonArray['countNote']  = 'No';
            }

            $jsonArray['countHtml']  = $text;
            $_SESSION['notification_count'] = $numRowsNotification;
        }
        else{
            $jsonArray['countNote']  = '';
            $jsonArray['countHtml']  = '';
        }

        return $cpUtil->getJsonFromArray($jsonArray);
    }
}