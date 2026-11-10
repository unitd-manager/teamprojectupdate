<?
class CPL_Admin_Widgets_Project_TaskAllocation_View extends CP_Admin_Widgets_Project_TaskAllocation_View
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $user_group = $fn->getSessionParam('userGroupID');
        $staff_id   = $fn->getSessionParam('staff_id');
        $today      = date('Y-m-d');

        $appendSQL = '';

        if ($user_group != $cpCfg['cp.superAdminUGId']){
            $appendSQL = "AND {$cpCfg['cp.modAccessStaffIdLabel']} = {$staff_id}";
        }

        $SQL = "
        SELECT DISTINCT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,s.current_status
              ,(SELECT time_in
                FROM attendance at
                WHERE at.staff_id = s.staff_id
                AND record_date = '{$today}'
              ) AS time_in
              ,(SELECT SUM(hours)
                FROM timesheet tm
                WHERE tm.staff_id = s.staff_id
                  AND tm.entry_date = '{$today}'
              ) AS hrs_today
        FROM {$cpCfg['cp.modAccessStaffTable']} s
        WHERE s.staff_id IN (
            SELECT DISTINCT ts.staff_id
            FROM task_staff ts
            JOIN task t ON (ts.task_id = t.task_id)
            WHERE t.status = 'Due'
            OR t.status = 'Late'
        )
        {$appendSQL}
        ORDER BY staff_name
        ";
        $result  = $db->sql_query($SQL);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $timein = '';

            if ($user_group == $cpCfg['cp.superAdminUGId']){
                $timein = "<span class=''>Started at: {$row['time_in']}</span>";
            }
        }

        $text = "
        <div id='taskHistory' class='inner'>
            {$this->getTasksUpdateByStaff()}
        </div>
        <div class='subcolumns'>
            {$rows}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getTasksByStaff($staff_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $today = date('Y-m-d');
        $SQL = "
        SELECT t.title
              ,date_format(t.due_date, '%d %b %Y') AS due_date
              ,t.status
              ,ts.current
              ,ts.task_now
              ,t.task_id
              ,IF (t.project_id IS NOT NULL AND t.project_id != '', p.title, o.title) AS project_opp_title
              ,IF (t.project_id IS NOT NULL AND t.project_id != '', p.project_id, o.opportunity_id) AS proj_opp_id
              ,(SELECT SUM(hours)
                FROM timesheet tm
                WHERE tm.task_id = t.task_id
              ) AS total_ts_hours
              ,(SELECT SUM(hours)
                FROM timesheet tm
                WHERE tm.task_id = t.task_id
                  AND tm.staff_id = {$staff_id}
                  AND tm.entry_date = '{$today}'
              ) AS total_ts_hours_today
              ,(SELECT date_format(tm.entry_date, '%d %b %Y')
                FROM timesheet tm
                WHERE tm.task_id = t.task_id
                  AND tm.staff_id = {$staff_id}
                ORDER BY entry_date 
                LIMIT 0,1
              ) AS start_date
        FROM task t
        JOIN task_staff ts ON (ts.task_id = t.task_id)
        LEFT JOIN (opportunity o) ON (t.opportunity_id = o.opportunity_id)
        LEFT JOIN (project p)     ON (t.project_id     = p.project_id)
        WHERE (t.status =  'Due' || t.status  =  'Late')
          AND ts.staff_id = {$staff_id}
        ORDER BY
        CASE
        WHEN (ts.task_now = 1) THEN 1
        WHEN (ts.current = 1) THEN 2
        WHEN (t.title = 'Project Management') THEN 6
        WHEN (t.status = 'Late') THEN 3
        WHEN (t.due_date != '' AND t.due_date IS NOT NULL AND t.due_date != '0000-00-00') THEN 4
        ELSE 5
        END, t.due_date
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $url = "index.php?_topRm=project&module=task&record_id={$row['task_id']}&_action=detail";
            $projUrl = "index.php?_topRm=project&module=project&record_id={$row['proj_opp_id']}&_action=detail";

            $chkStatus  = ($row['current']  == 1) ? " checked='checked'" : '';
            $chkTaskNow = ($row['task_now'] == 1) ? " checked='checked'" : '';
            $trClass = '';
            
            if ($row['task_now'] == 1){
                $trClass = " class='nowTask'";
            } else if ($row['current'] == 1){
                $trClass = " class='currentTask'";
            }

            $editUrl = "index.php?module=project_task&_spAction=editFromList&id={$row['task_id']}&showHTML=0";

            $editText = "
            <a class='editFromList' dialogTitle=\"Edit - {$row['title']}\" href='javascript:void(0);' link='{$editUrl}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
            </a>
            ";

            $timeUrl = "index.php?module=project_timesheet&_spAction=newRecordFromTask&task_id={$row['task_id']}&showHTML=0";
		    $taskHistUrl = "index.php?widget=project_taskAllocation&_action=edit&_spAction=taskHistoryGrid&task_id={$row['task_id']}";

            $rows .= "
            <tr{$trClass}>
                <td><a href='{$url}'>{$row['task_id']}</a></td>
                <td><a href='{$url}'>{$row['title']}</a></td>
                <td><a href='{$projUrl}'>{$row['project_opp_title']}</a></td>
                <td>{$row['start_date']}</td>
                <td>{$row['due_date']}</td>
                <td>{$editText} {$row['status']}</td>
                <td class='txtCenter'>{$row['total_ts_hours']}</td>
                <td class='txtCenter'>{$row['total_ts_hours_today']}</td>
                <td class='txtCenter'><input type='checkbox' name='current' task_id='{$row['task_id']}' {$chkStatus}/></td>
                <td class='txtCenter'><input type='checkbox' name='task_now' task_id='{$row['task_id']}' {$chkTaskNow}/></td>
                <td class='txtCenter'>
                    <a class='addToTime' dialogTitle=\"Add time for: {$row['title']}\"
                        href='javascript:void(0);' link='{$timeUrl}' title='Add time'>
                        <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/plus.png' border='0'>
                    </a>
                    <a class='editTaskHistory' dialogTitle=\"Task History for: {$row['title']}\" task_id='{$row['task_id']}'
                        href='javascript:void(0);' link='{$taskHistUrl}' title='Edit Task History'>
                        <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/grid.png' border='0'>
                    </a>
                </td>
            </tr>
            ";
        }

        $text = "
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Project</th>
                <th class='w65'>Start Date</th>
                <th class='w65'>Due Date</th>
                <th class='w50'>Status</th>
                <th class='w40 txtCenter'>Hrs</th>
                <th class='w40 txtCenter'>Hrs Today</th>
                <th class='w40 txtCenter'>Current</th>
                <th class='w40 txtCenter'>Now</th>
                <th class='w50 txtCenter'>Actions</th>
            </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";

        return $text;
    }

    /**
     * List layout of dashboard
     */
    function getTasksUpdateByStaff() { 
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $dateUtil = Zend_Registry::get('dateUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $today          = date('Y-m-d');
        $userGroupID    = $fn->getSessionParam('userGroupID');
        $status         = $fn->getReqParam('status');
        $staff_id       = $fn->getReqParam('staff_id');
        $priority       = $fn->getReqParam('priority');
        $estd_hrs       = $fn->getReqParam('estd_hrs');
        $sort_by        = $fn->getReqParam('sort_by');

        $whereSQL = '';
        if ($status) {
            $whereSQL .= "AND th.status = '{$status}'";
        } else {
            $whereSQL .= "AND(th.status != 'Completed')";            
        }

        if ($staff_id) {
            $whereSQL .= "AND th.staff_id = {$staff_id}";
        }
        
        $sortBy = '';
        if ($sort_by == "By Priority") {
            $sortBy .= "th.priority ASC";
        } else if($sort_by == "By Project") {
          $sortBy = 'project_title ASC';
        } else if($sort_by == "By Start Date") {
          $sortBy = 'th.start_date DESC';
        } else if($sort_by == "By Task") {
          $sortBy = 'th.title ASC';
        } else if($sort_by == "By Staff") {
          $sortBy = 'staff_name ASC';
        } else {
          $sortBy = 'th.task_history_now DESC, th.priority ASC, th.staff_id';
        }

        if ($userGroupID == 1 || $userGroupID == 2) {
            $SQL = "
            SELECT th.task_history_id
                  ,th.title
                  ,th.task_id
                  ,th.project_id
                  ,th.status
                  ,th.estd_hrs
                  ,th.priority
                  ,th.progress_percent
                  ,th.task_history_now
                  ,th.comments
                  ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
                  ,th.start_date
                  ,th.end_date
                  ,th.staff_id
                  ,t.title AS task_title 
                  ,t.status AS task_status
                  ,p.title AS project_title             
                  ,p.status AS project_status
                  ,(SELECT SUM(hours)
                    FROM timesheet tm
                    WHERE tm.project_id = p.project_id
                  ) AS total_ts_hours                 
            FROM task_history th
            LEFT JOIN staff s ON (s.staff_id = th.staff_id)
            LEFT JOIN task t ON (t.task_id = th.task_id)
            LEFT JOIN project p ON (p.project_id = th.project_id)
            WHERE th.task_id != ''
            {$whereSQL}
            ORDER BY {$sortBy}
            ";
        } else if ($userGroupID == 3) {
            $SQL = "
            SELECT th.task_history_id
                  ,th.title
                  ,th.task_id
                  ,th.project_id
                  ,th.status
                  ,th.estd_hrs
                  ,th.priority
                  ,th.progress_percent
                  ,th.task_history_now
                  ,th.comments
                  ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
                  ,th.start_date
                  ,th.end_date
                  ,th.staff_id
                  ,t.title AS task_title 
                  ,t.status AS task_status
                  ,p.title AS project_title             
                  ,p.status AS project_status
                  ,(SELECT SUM(hours)
                    FROM timesheet tm
                    WHERE tm.project_id = p.project_id
                  ) AS total_ts_hours
            FROM task_history th
            LEFT JOIN staff s ON (s.staff_id = th.staff_id)
            LEFT JOIN task t ON (t.task_id = th.task_id)
            LEFT JOIN project p ON (p.project_id = th.project_id)
            WHERE th.task_id != ''
                {$whereSQL}
                AND s.staff_id = '{$_SESSION['staff_id']}' 
                AND s.status = 'Current' 
                AND s.team = 'In-house'
            ORDER BY {$sortBy}
            ";
        }

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';
        $i = 1;
				
        while ($row = $db->sql_fetchrow($result)) {
            $url = "index.php?_topRm=project&module=project_task&record_id={$row['task_id']}&_action=detail";
            $proUrl = "index.php?_topRm=project&module=project_project&record_id={$row['project_id']}&_action=detail";

            $timeUrl = "index.php?module=project_timesheet&_spAction=newRecordFromTask&task_id={$row['task_id']}&task_history_id={$row['task_history_id']}&showHTML=0";
        	$taskHistUrl = "index.php?widget=project_taskAllocation&_action=edit&_spAction=taskHistoryGrid&task_id={$row['task_history_id']}";
        	$sendEmailLink = "index.php?widget=project_taskAllocation&_spAction=sendEmail&title={$row['title']}&staff_id={$row['staff_id']}&project_title={$row['project_title']}&comments={$row['comments']}";

            $editUrl = "index.php?_room=project_taskHistory&_spAction=editFromList&id={$row['task_history_id']}&showHTML=0";
            $editText = "
            <a class='taskHistoryEditFromList' dialogTitle=\"Edit - {$row['title']}\" href='javascript:void(0);' link='{$editUrl}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
            </a>
            ";
	

			$class = '';
			if ($row['task_history_now'] == 1) {		
					$class= "taskHistoryColorPinkCheckbox";
			} else{ 
			 		$class= "taskHistoryPlainCheckbox";
			}

            $chkTaskHistNow = ($row['task_history_now'] == 1) ? " checked='checked'" : '';
            
			$start_date   = $dateUtil->formatDate($row['start_date'], "DD MMM YYYY");
			$end_date   = $dateUtil->formatDate($row['end_date'], "DD MMM YYYY");
      
          $sqlPriority  = $fn->getValueListSQL('taskHistoryPriority');
          $sqlStatus  = $fn->getValueListSQL('taskHistoryStatus');
          $sqlProgressPercent  = $fn->getValueListSQL('taskHistoryprogressPercent');
          $exp = array('sqlType' => 'OneField');
          $expStaff = array('detailValue' => '$row[\'staff_name\']');

          $staffSql= "
          SELECT staff_id
               ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
          FROM staff a 
          WHERE a.status = 'Current' 
            AND a.team = 'In-house'
            ORDER BY staff_name
          ";

            $rows .= "
            <tr class='$class'>
            	<td>{$i}</td>
                <td><a href='{$proUrl}'>{$row['project_title']} ({$row['total_ts_hours']} Hrs)</td>
                <td><a href='{$url}'>{$row['title']}</a></td>
                <td class='txtCenter' id='{$row['task_history_id']}'>{$formObj->getDDRowBySQL('', 'staff_name', $staffSql, $row['staff_id'], $expStaff)}</td>
                <td>{$start_date}</td>
                <td>{$end_date}</td>
                <td class='txtCenter' id='{$row['task_history_id']}'>{$formObj->getDDRowBySQL('', 'priority', $sqlPriority, $row['priority'], $exp)}</td>
                <td class='txtCenter' id='{$row['task_history_id']}'>{$formObj->getDDRowBySQL('', 'progress_percent', $sqlProgressPercent, $row['progress_percent'], $exp)}</td>
                <td class='txtCenter' id='{$row['task_history_id']}'>{$formObj->getDDRowBySQL('', 'Status', $sqlStatus, $row['status'], $exp)}</td>
                <td class='textBoxHours' id='{$row['task_history_id']}'>{$formObj->getTBRow('', 'estd_hrs', $row['estd_hrs'])}</td>
                <td class='txtCenter'><input type='checkbox' name='task_history_now' task_history_id='{$row['task_history_id']}' {$chkTaskHistNow}/></td>
                <td class='txtCenter'>
                    <a task_history_id={$row['task_history_id']} class='timeSheetDetail' href='#'> 
                        <img src='{$cpCfg['cp.masterImagesPathAlias']}action/report.png' border='0'>
                    </a>

                    <a class='timeSheetEdit' href='index.php?widget=project_taskAllocation&_spAction=timeSheetEdit&task_history_id={$row['task_history_id']}&staff_id={$row['staff_id']}&project_id={$row['project_id']}&task_id={$row['task_id']}&comments={$row['comments']}&showHTML=0'> 
                        <img src='{$cpCfg['cp.masterImagesPathAlias']}action/edit.png' border='0'>
                    </a>

                    <a class='sendEmail' dialogTitle='Send Email' href='javascript:void(0);' link='{$sendEmailLink}'>
                        <img src='images/mail.png' border='0'>
                    </a>

                    <a class='addToTime' dialogTitle=\"Add time for: {$row['title']}\"
                        href='javascript:void(0);' link='{$timeUrl}' title='Add time'>
                        <!--<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/plus.png' border='0'>-->
                        <img src='images/add_icon.jpg' border='0'>
                    </a>
                </td>
            </tr>
            ";
        $i++;    
        }

        $addNew = "index.php?widget=project_taskAllocation&_spAction=addNewTask";
        $taskMailSubmit  = "index.php?widget=project_taskAllocation&_spAction=taskMail&showHTML=0";

        $stfCommon = "
        SELECT staff_id
             ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
        FROM staff a 
        WHERE 
        ";

        if ($userGroupID == 1 || $userGroupID == 2) {
            $SQLStf = "
            {$stfCommon} a.status = 'Current' 
            AND a.team = 'In-house'
            ORDER BY staff_name
            ";

        } else if ($userGroupID == 3) {
            $SQLStf = "
            {$stfCommon} a.team ='{$_SESSION['staff_team']}' 
                     AND a.staff_id = '{$_SESSION['staff_id']}' 
                     AND a.status = 'Current' 
                     AND a.team = 'In-house'
            ORDER BY staff_name
            ";
        }

        $status    = $fn->getReqParam('status');
        $SQLStatus = "
        SELECT value 
        FROM valuelist 
        WHERE key_text = 'taskHistoryStatus'         
        ORDER BY sort_order
        ";

        $sortByArray = array(
              "By Project"
             ,"By Task"
             ,"By Priority"
             ,"By Start Date"
             ,"By Staff"
        );
        
        $label          = '';
        $staffSearch    = '';
        $statusSearch   = '';
        $sortSearch     = '';
        $actionBtns     = '';
        if ($staff_id == '' && $status == '' && $sort_by == '') {
            
            $label = "
            <div class='float_left m10'>
                Task History
            </div>
            ";
            
            $staffSearch = "
            <div class='float_right  mt5 mb5' id='allTask'>
                <td>
                    <select name='staff_id'>
                        <option value=''>Staff</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $SQLStf, $tv['staff_id'])}
                    </select>
                </td>
            </div>
            ";

            $statusSearch = "
            <div class='float_right  mt5 mb5'>
                <td class='fieldValue'>
                    <select name='status'>
                        <option value=''>Status</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
                    </select>
                </td>
            </div>
            ";

            $sortSearch = "
            <div class='float_right  mt5 mb5'>
                <td class='fieldValue'>
                    <select name='sort_by'>
                        <option value=''>Sort By</option>
                    {$cpUtil->getDropDown1($sortByArray, $sort_by)}
                    </select>
                </td>
            </div>
            ";
            
            $actionBtns = "
            <div class='float_right mt5 mb5'>
                <div><a class='taskMail button' dialogTitle=\"Send Task Mail:\" href='javascript:void(0);' link='{$taskMailSubmit}' title='Send Task Mail'>Send Task Mail</a></div>
            </div>
            <div class='float_right mt5 mb5'>
                <a class='addNewTask button' dialogTitle='Add New Task' href='javascript:void(0);' link='{$addNew}'>
                    Add Task
                </a>
            </div>
            ";
        }

        $text = "
        <h2 class='floatbox'>
            {$label}
            {$staffSearch}
            {$statusSearch}
            {$sortSearch}
            {$actionBtns}
        </h2>

        <table style='height: 150px overflow: auto'>
            <thead>
            <tr>
                <th>No.</th>
                <th>Project</th>
                <th>Title</th>
                <th>Staff</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Priority</th>
                <th>Progress Percent</th>
                <th>Status</th>
                <th>Estimated Time</th>
                <th>Now</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";
 
        return $text;
    }

    /**
     * Adding new task from dashboard form
     */
    function getAddNewTask() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?widget=project_taskAllocation&_spAction=addTaskFormSubmit&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $sqlProj = "
        SELECT p.project_id
              ,p.title
        FROM project p
        WHERE p.status = 'WIP'
        ORDER BY p.title
        ";

        $sqlTask = "
        SELECT t.task_id
              ,t.title
        FROM task t
        LEFT JOIN (project p) ON (p.project_id = t.project_id)
        ORDER BY t.title
        ";
        
        $sqlStatus = $fn->getValueListSQL('taskHistoryStatus');

        $sqlStaff = "
        SELECT staff_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
        FROM staff a
        WHERE a.status = 'Current'
          AND a.team = 'In-house'
        ORDER BY staff_name
        ";
        $exp = array('sqlType' => 'OneField');
        $today      = date('Y-m-d');

        $text = "
        <form id='addTaskForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Project', 'project_id', $sqlProj)}
                {$formObj->getDDRowBySQL('Task', 'task_id')}
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getTARow('Description', 'comments')}
                {$formObj->getTBRow('Estimated Time', 'estd_hrs')}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'To be Started', $exp)}
                {$formObj->getDDRowBySQL('Staff', 'staff_id', $sqlStaff)}
                {$formObj->getDateRow('Start Date', 'start_date', $today)}
                {$formObj->getDateRow('End Date', 'end_date')}
                {$formObj->getYesNoRRow('Send Mail', 'send_mail', '1')}
            </fieldset>
        </form>
        ";
        return $text;
    }

    /**
     * Send task mail from dashboard form
     */
    function getTaskMail() {
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?widget=project_taskAllocation&_spAction=taskMailSubmit&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        
        $text = "
        <form id='sendTaskEmail' class='yform columnar' method='post' action='{$formAction}'>
          <p>Would you like to send task mail for today? </p>
        </form>
        ";
        return $text;
    }

    /**
     * Task history detail
     */
    function getTimeSheetDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $task_history_id = $fn->getReqParam('task_history_id');

        $SQL = "
        SELECT ts.*
              ,th.comments
              ,th.estd_hrs
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name               
        FROM timesheet ts
        LEFT JOIN (task_history th) ON (th.task_history_id = ts.task_history_id)
        LEFT JOIN (staff s) ON (s.staff_id = ts.staff_id)
        WHERE ts.task_history_id = {$task_history_id}
        ";
        $result  = $db->sql_query($SQL);

        $SQL1 = "
        SELECT th.*
              ,th.comments
              ,th.estd_hrs
        FROM task_history th  
        WHERE th.task_history_id = {$task_history_id}       
        ";
        $result1 = $db->sql_query($SQL1);
        $row1    = $db->sql_fetchrow($result1);
        
        $rows = '';
        $rows1 = '';
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['staff_name']}</td>
                <td>{$row['creation_date']}</td>
                <td>{$row['hours']}</td>
                <td>{$row['description']}</td>
            </tr>
            ";

            $rows1 .= "
            <tr>
                <td>{$row['comments']}</td>
            </tr>
            ";
        }
        
        $text = "
        <div class='widget'>
            <table>
                <thead>
                <tr>
                    <th>Task Description</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{$row1['comments']}</td>
                    </tr>
                </tbody>
            </table>
            
            <table>
                <thead>
                <tr>
                    <th>Staff</th>
                    <th>Date</th>
                    <th>Hrs</th>
                    <th>Timesheet Description</th>
                </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </div>
        ";

        return $text;
    }

    /**
     * Task history edit
     */
    function getTimeSheetEdit() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $comments        = $fn->getReqParam('comments');
        $task_history_id = $fn->getReqParam('task_history_id');
        $staff_id		 = $fn->getReqParam('staff_id');
        $project_id		 = $fn->getReqParam('project_id');
        $task_id		 = $fn->getReqParam('task_id');

        $exp = array('sqlType' => 'OneField');

        $formAction = "index.php?widget=project_taskAllocation&_spAction=timeSheetEditSubmit&lnkRoom={$tv['lnkRoom']}&task_history_id={$task_history_id}&staff_id={$staff_id}&showHTML=0";       

        $text = "
        <form id='timeSheetEdit' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTARow('Message', 'comments', $comments)}
                {$formObj->getYesNoRRow('Send Mail', 'send_mail', '1')}
                <input type='hidden' name='staff_id' value='{$staff_id}' />    
                <input type='hidden' name='project_id' value='{$project_id}' />    
                <input type='hidden' name='task_id' value='{$task_id}' />    
            </fieldset>
        </form>
        ";
        return $text;
    }

    /**
     * Updating of task history status through email form
     */
    function getSendEmail() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $title          = $fn->getReqParam('title');
        $comments       = $fn->getReqParam('comments');
        $project_title  = $fn->getReqParam('project_title');
        $staff_id       = $fn->getReqParam('staff_id');
        
        $formAction = "index.php?widget=project_taskAllocation&_spAction=sendEmailSubmit&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        
        $sqlStatus = $fn->getValueListSQL('mailStatus');
        $exp = array('sqlType' => 'OneField');

        $sqlStaff = "
        SELECT email
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
        FROM staff a
        WHERE a.status = 'Current'
          AND a.team = 'In-house'
        ORDER BY staff_name
        ";        
	
		$result = $db->sql_query($sqlStaff);
		$row = $db->sql_fetchrow($result);

		if ($_SESSION['email'] == 'syed@usoftsolutions.com') {

	        $status = "
            {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Update', $exp)}
	        ";

	        $sqlStaffEmail = "
	        SELECT email
	        FROM staff 
	        WHERE staff_id = '{$staff_id}'   
	        ";        

			$resultEmail = $db->sql_query($sqlStaffEmail);
			$rowEmail = $db->sql_fetchrow($resultEmail);
			$email = $rowEmail['email'];						
		} else {
			$email = 'syed@usoftsolutions.com';						

	        $status = "
            {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Resolved', $exp)}
	        ";
		}

 		$staffEmail  = $fn->getRecordRowByID('staff', 'staff_id', $staff_id);		        
        $title = $project_title . ' : ' . $title;
        
        //$replaced = explode("'", '"', $comments);                
        //$replaced = str_replace(''', '"', $comments);
                
        $text = "
        <form id='addSendEmail' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('To', 'email_to', $sqlStaff, $email)}
                {$formObj->getTBRow('Subject', 'title', $title)}              
				{$status}
                {$formObj->getTARow('Message', 'comments', $comments)}
            </fieldset>
        </form>
        ";
        return $text;
    }
}