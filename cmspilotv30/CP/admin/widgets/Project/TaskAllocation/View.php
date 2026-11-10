<?
class CP_Admin_Widgets_Project_TaskAllocation_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
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

            $rows .= "
            <div class='c50l' staff_id='{$row['staff_id']}'>
                <div class='subcl'>
                    <h2 class='floatbox'>
                        <div class='float_left'>{$row['staff_name']}</div>
                        <div class='float_right'>
                            {$timein} <span>| Hours Entered: {$row['hrs_today']}</span>
                            <select name='staff_current_status' staff_id='{$row['staff_id']}'>
                                {$dbUtil->getDropDownFromSQLCols1($db, $fn->getValueListSQL('staffCurrentStatus'), $row['current_status'])}
                            </select>
                        </div>
                    </h2>
                    <div class='inner'>
                        {$this->getTasksByStaff($row['staff_id'])}
                    </div>
                </div>
            </div>
            ";
        }

        $text = "
        <h2>Task Allocation</h2>
        <div class='subcolumns'>
            {$rows}
        </div>
        ";

        return $text;
    }

    //==================================================================//
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

    //==================================================================//
    function getUpdateCurrentStatus() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $fldName    = $fn->getReqParam('fldName');
        $task_id    = $fn->getReqParam('task_id');
        $staff_id   = $fn->getReqParam('staff_id');
        $currentVal = $fn->getReqParam('currentVal');

        if ($task_id == '' || $staff_id == ''){
            return;
        }

        $SQL = "
        UPDATE task_staff
        SET {$fldName} = {$currentVal}
        WHERE task_id = {$task_id}
          AND staff_id = {$staff_id}
        ";
        $result  = $db->sql_query($SQL);

        return $this->getTasksByStaff($staff_id);
    }

    //==================================================================//
    function getUpdateStaffCurrentStatus() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $staff_id = $fn->getReqParam('staff_id');
        $status   = $fn->getReqParam('status');

        if ($staff_id == ''){
            return;
        }

        $SQL = "
        UPDATE staff
        SET current_status = '{$status}'
        WHERE staff_id = {$staff_id}
        ";
        $result  = $db->sql_query($SQL);
    }

    //==================================================================//
    function getTaskHistoryGrid() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');
        $linksArray = Zend_Registry::get('linksArray');

        $task_id = $fn->getReqParam('task_id');
        $row = $fn->getRecordRowByID('task', 'task_id', $task_id);

        $fnMod = includeCPClass('ModuleFns', 'project_task');
        $fnMod->setLinksArray($arrayMasterLink);
        $linksArray = $arrayMasterLink->linksArray;
        $text = $displayLinkData->getLinkPortalMain('task', 'taskHistory', 'Task History', $row);
        return $text;
    }
}