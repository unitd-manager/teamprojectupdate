<?
class CP_Admin_Modules_EnggCrm_Task_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        //**** SET STATUS UPDATE ***//
        $today = date("Y-m-d");

        $SQL1 = "
        UPDATE task SET status = 'Late'
        WHERE due_date < '{$today}'
          AND due_date != '0000-00-00'
          AND status   != 'Complete'
          AND status   != 'Completed'
          AND status   != 'Cancelled'
        ";
        $result1 = $db->sql_query($SQL1);
        //********************************************************//
        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $due_date = $dateUtil->formatDate($row['due_date'], 'DD MMM YYYY');
            $due_date1 = $dateUtil->formatDate($row['due_date'], 'YYYY-MM-DD');

            if ($cpCfg['m.enggCrm.task.hightlightDue'] == 1) {

                if ($due_date1 == $today) {
                    $hightlightDueTasks = $listObj->getListRowHeader($row, $rowCounter, 'projectList3');
                } else if ($row['status'] == 'Late') {
                    $hightlightDueTasks = $listObj->getListRowHeader($row, $rowCounter, 'projectList2');
                } else {
                    $hightlightDueTasks = $listObj->getListRowHeader($row, $rowCounter);
                }

            } else {
                $hightlightDueTasks = $listObj->getListRowHeader($row, $rowCounter);
            }

            if ($cpCfg['m.enggCrm.task.taskListFieldsOrderGroup'] == 2) {
                $taskListFieldsOrderGroupRow = "
                {$listObj->getGoToDetailText($rowCounter, $row['title'])}
                {$listObj->getGoToDetailText($rowCounter, $row['project_opp_code'])}
                {$listObj->getListDataCell($row['project_opp_title'])}
                {$listObj->getListDataCell($row['project_or_opp'])}
                {$listObj->getListDataCell($row['project_opp_company'])}
                ";
            } else {
                $taskListFieldsOrderGroupRow = "
                {$listObj->getGoToDetailText($rowCounter, $row['project_opp_code'])}
                {$listObj->getListDataCell($row['project_opp_company'])}
                {$listObj->getListDataCell($row['project_opp_title'])}
                {$listObj->getListDataCell($row['project_or_opp'])}
                {$listObj->getListDataCell($row['title'])}
                ";
            }

            $editText = "
            <a class='editFromList' dialogTitle=\"Edit - {$row['title']}\" href='javascript:void(0);' link='{$fn->getEditFromListUrl($row)}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
            </a>
            ";

            $timeUrl = "index.php?module=enggCrm_timesheet&_spAction=newRecordFromTask&task_id={$row['task_id']}&showHTML=0";

            $addTime = "
            <a class='addToTime' dialogTitle=\"Add time for: {$row['title']}\" href='javascript:void(0);' link='{$timeUrl}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/plus.png' border='0'>
            </a>
            ";

            $goToTime = "
            <div align='left' style='float:left'>
                <a href='index.php?_topRm=project&module=enggCrm_timesheet&_action=list&task_id={$row['task_id']}'>
                    <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/link_edit.png'>
                </a>
            </div>
            ";

            $rows .= "
            {$hightlightDueTasks}
            {$taskListFieldsOrderGroupRow}
            {$listObj->getListDataCell($row['staff_names'])}
            {$listObj->getListDataCell($row['staff_team'], 'left', '', 120)}
            {$listObj->getListDataCell($due_date, 'left', '', 75)}
            {$listObj->getListDataCell($editText . '&nbsp;' . ($row['status']), 'left', '', 85)}
            {$listObj->getListDataCell($row['estimated_hours'])}
            {$listObj->getListDataCell($addTime . '&nbsp;' . $row['total_ts_hours'] , 'left', '', 75)}
            {$listObj->getListDataCell($goToTime)}
            {$listObj->getListRowEnd($row['task_id'])}
            ";

            $rowCounter++;
        }

        if ($cpCfg['m.enggCrm.task.taskListFieldsOrderGroup'] == 2) {
            $taskListFieldsOrderGroupHeader = "
            {$listObj->getListHeaderCell('Task Title', 't.title')}
            {$listObj->getListHeaderCell('Code', 'project_opp_code', 'w50')}
            {$listObj->getListHeaderCell('Proj / Opp Title', 'project_opp_title')}
            {$listObj->getListHeaderCell('Type', 'project_or_opp')}
            {$listObj->getListHeaderCell('Company Name', 'project_opp_company')}
            ";
        } else {
            $taskListFieldsOrderGroupHeader = "
            {$listObj->getListHeaderCell('Code', 'project_opp_code', 'w50')}
            {$listObj->getListHeaderCell('Company Name', 'project_opp_company')}
            {$listObj->getListHeaderCell('Project / Opp. Title', 'project_opp_title')}
            {$listObj->getListHeaderCell('Type', 'project_or_opp')}
            {$listObj->getListHeaderCell('Task Title', 't.title')}
            ";
        }

        $listHeaderCellTime = '';
        if ($tv['action'] != 'print') {
            $listHeaderCellTime = $listObj->getListHeaderCell('Time');
        }

        if ($cpCfg['m.enggCrm.task.daysLbl'] == 1){
            $est = $listObj->getListHeaderCell('Est. Days', 't.estimated_hours', 'w65');
            $total = 'Total Days';
        } else {
            $est = $listObj->getListHeaderCell('Est. Hours', 't.estimated_hours', 'w65');
            $total = 'Total Hours';
        }
        
        $text = "
        {$listObj->getListHeader()}
        {$taskListFieldsOrderGroupHeader}
        {$listObj->getListHeaderCell($cpCfg['m.enggCrm.staffFieldLabel'], 'staff_names')}
        {$listObj->getListHeaderCell($cpCfg['m.enggCrm.staffTeamLabel'], 'staff_team')}
        {$listObj->getListHeaderCell('Due Date', 't.due_date')}
        {$listObj->getListHeaderCell('Status', 't.status')}
        {$est}
        {$listObj->getListHeaderCell($total)}
        {$listHeaderCellTime}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintList($result) {

        $text = $this->getList($result);

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset = "
        {$formObj->getTBRow('Task Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }
    
    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $releaseStatus = '';

        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));

        if ($row['project_or_opp'] == 'Project'){
            $projUrl = "index.php?_topRm={$tv['topRm']}&module=enggCrm_project&record_id={$row['project_id']}&_action=detail";
            $projUrl = "<a href='{$projUrl}'>{$row['project_opp_code']}</a>";
        } else {
            $oppUrl = "index.php?_topRm={$tv['topRm']}&module=enggCrm_opportunity&record_id={$row['opportunity_id']}&_action=detail";
            $oppUrl = "<a href='{$oppUrl}'>{$row['project_opp_code']}</a>";
        }
        
        $sqlCat    = $fn->getValueListSQL('taskCategory');
        $sqlStatus = $fn->getValueListSQL('taskStatus');

        $expVl     = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);
        $expProj   = array('sqlType' => 'hasSeperator', 'detailValue' => $row['project_opp_title']);
        $expPM     = array('detailValue' => $row['project_manager_name']);

        $sqlProj = "
        SELECT a.project_id
              ,CONCAT_WS(' ', a.project_code, a.title)
              ,b.company_name
        FROM project a
            ,company b
        WHERE a.company_id = b.company_id
        ORDER BY b.company_name
        ";
        
        $sqlOpp = "
        SELECT a.opportunity_id
              ,CONCAT_WS(' ', a.opportunity_code, a.title)
              ,b.company_name
        FROM opportunity a
            ,company b
        WHERE a.company_id = b.company_id
        ORDER BY b.company_name
        ";
        
        if ($row['project_id'] != ''){
            $oppProj = $formObj->getDDRowBySQL('Project', 'project_id', $sqlProj, $row['project_id'], $expProj);
        } else if ($row['opportunity_id'] != ''){
            $oppProj = $formObj->getDDRowBySQL('Opportunity', 'opportunity_id', $sqlOpp, $row['opportunity_id'], $expProj);
        } else {
            $oppProj = "
            {$formObj->getDDRowBySQL('Project', 'project_id', $sqlProj, $row['project_id'], $expProj)}
            {$formObj->getDDRowBySQL('Opportunity', 'opportunity_id', $sqlOpp, $row['opportunity_id'], $expProj)}
            ";
        }

        if ($row['opportunity_id'] != ''){
            $oppUrl = "index.php?_topRm={$tv['topRm']}&module=enggCrm_opportunity&record_id={$row['opportunity_id']}&_action=detail";
            $code = "<a href='{$oppUrl}'>{$row['project_opp_code']}</a>";
        } else if ($row['project_id'] != ''){
            $projUrl = "index.php?_topRm={$tv['topRm']}&module=enggCrm_project&record_id={$row['project_id']}&_action=detail";
            $code    = "<a href='{$projUrl}'>{$row['project_opp_code']}</a>";
        } else {
            $code = '';
        }

        if($cpCfg['m.enggCrm.task.showReleaseStatus'] == 1){
            $releaseStatus = "
            {$formObj->getYesNoRRow('Release Full Status', 'release_task_status', $row['release_task_status'])}
            ";
        }
        
        if($cpCfg['m.enggCrm.task.daysLbl'] == 1){
            $est = 'Estimated Days';
        } else {
            $est = 'Estimated Hours';
        }

        $fielset1 = "
        {$formObj->getTBRow($row['project_or_opp']. ' Code', 'project_code', $code, $expNoEdit)}
        {$formObj->getTBRow('Task Title', 'title', $row['title'])}
        {$oppProj}
        {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM, $row['project_manager_id'], $expPM)}
        {$formObj->getYesNoRRow('Alert Staff by email', 'staff_alert', $row['staff_alert'])}
        {$formObj->getYesNoRRow('Alert Project Manager when complete?', 'project_manager_alert', $row['project_manager_alert'])}
        {$formObj->getDateRow('Due Date', 'due_date', $row['due_date'])}
        {$formObj->getTBRow($est, 'estimated_hours', $row['estimated_hours'])}
        {$formObj->getDDRowBySQL('Category', 'category', $sqlCat, $row['category'], $expVl)}
        {$formObj->getYesNoRRow('Chargeable', 'chargeable', $row['chargeable'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Task Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getSearch() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlComp   = $fn->getDDSql('project_company');
        $sqlOpp    = $fn->getDDSql('project_opportunity');
        $sqlProj   = $fn->getDDSql('project_project');
        $sqlStatus = $fn->getValueListSQL('taskStatus');

        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));
        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $expVl     = array('sqlType' => 'OneField');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        {$formObj->getDDRowBySQL('Client Name', 'company_id', $sqlComp)}
        {$formObj->getDDRowBySQL('Opportunity', 'opportunity_id', $sqlOpp)}
        {$formObj->getDDRowBySQL('Project', 'project_id', $sqlOpp)}
        {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM)}
        {$formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlStaff)}
        {$formObj->getTBRow('Team', 'staff_team')}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $expVl)}
        {$formObj->getDateRangeRow('Date', 'due_date')}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        {$formObj->getTARow('Description', 'description')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Invoice Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        
        $record_id = $fn->getIssetParam($row, 'task_id');
        
        $links = '';
        if ($cpCfg['m.enggCrm.task.hasTaskHistory'] == 1){
            $links = $displayLinkData->getLinkPortalMain('enggCrm_task', 'enggCrm_taskHistoryLink', 'Task History', $row);
        }
        
        $text = "
        {$media->getRightPanelMediaDisplay('Attachment', 'enggCrm_task', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('enggCrm_task', 'enggCrm_timesheetLink', 'Timesheet', $row)}
        {$displayLinkData->getLinkPortalMain('enggCrm_task', 'core_staffLink', $cpCfg['m.enggCrm.staffFieldLabel'], $row)}
        {$links}
        {$comment->getView(array(
             'roomName' => 'enggCrm_task'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getEditFromList() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('task', 'task_id', $id);

        $sqlStatus  = $fn->getValueListSQL('taskStatus');

        $formAction = "index.php?_spAction=saveFromList&module={$tv['module']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDateRow('Due Date', 'due_date', $row['due_date'])}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
            </fieldset>
            <input type='hidden' name='task_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getTasksXML() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = '';
        
        $project_id     = $fn->getReqParam('project_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');

        $text .= $fn->getAjaxXMLHeader();
        $text .= "<data>";

        if ($opportunity_id != "") {
            $SQL = "
            SELECT *
            FROM task a
            WHERE a.opportunity_id = {$opportunity_id}
            ORDER BY a.title
            ";
        } else {
            $SQL = "
            SELECT *
            FROM task a
            WHERE a.project_id = {$project_id}
            ORDER BY a.title
            ";
        }
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $text .= "<row>";
            $text .= "<record_id>" . $row['task_id'] . "</record_id>";
            $text .= "<title><![CDATA[" . $row['title'] . "]]></title>";
            $text .= "</row>";
        }
        $text .= "</data>";

        return $text;
    }

    /**
     *
     */
    function getReportsMenu() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($tv['action'] == "detail") {
            $text = '';
        } else {
            $qstr = $fn->getQueryStringForJasper();
            $printJasperUrl = "index.php?_spAction=printReport&showHTML=0&{$qstr}&roomName={$tv['module']}&report=";

            $text = "
            <h2>Reports:</h2>
            <ul class='printOptions'>
                <li><a href='{$printJasperUrl}taskSummaryList'>Task Summary List</a></li>
            </ul>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getTasksByProjectJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $appendSQL = '';

        $project_id     = $fn->getReqParam('project_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');

        $json  = array();
        
        if ($project_id == '' && $opportunity_id == ''){
            return json_encode($json);
        }
        
        if ($project_id != ''){
            $appendSQL .= "project_id = '{$project_id}'";
        }
        
        if ($opportunity_id != ''){
            $appendSQL .= "opportunity_id = '{$opportunity_id}'";
        }
        
        if ($appendSQL != ''){
            $appendSQL = "WHERE {$appendSQL}";
        }
        
        $SQL = "
        SELECT task_id
              ,title
        FROM task 
        {$appendSQL}
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['task_id'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getSendTaskUpdatesToPM() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $today     = date('Y-m-d');        
        $from_date = date('Y') . '-01-01';
        
        $SQLUpdate = "
        UPDATE task_history SET task_history_now = ''
        ";
        $resultUpdate  = $db->sql_query($SQLUpdate);

        $SQL = "
        SELECT t.title
              ,p.title AS project_title
              ,tm.hours
              ,th.title AS task_history_title
              ,th.task_history_id
              ,t.task_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,s.staff_id
              ,a.time_in
              ,a.leave_time
              ,(SELECT SUM(hours)
                FROM timesheet tm
                WHERE tm.staff_id = s.staff_id
                  AND tm.entry_date = '{$today}'
              ) AS work_hrs_today
			  ,(SELECT SUM( on_leave )
				FROM attendance
				WHERE staff_id = s.staff_id
				AND type_of_leave = 'Personal Leave'
				AND record_date BETWEEN '{$from_date}' AND '{$today}'
              ) AS personal_leave_taken
			  ,(SELECT SUM( on_leave )
				FROM attendance
				WHERE staff_id = s.staff_id
				AND type_of_leave = 'Sick Leave'
				AND record_date BETWEEN '{$from_date}' AND '{$today}'
              ) AS sick_leave_taken
			  ,(SELECT SUM( on_leave )
				FROM attendance
				WHERE staff_id = s.staff_id
				AND type_of_leave = 'Pay Leave'
				AND record_date BETWEEN '{$from_date}' AND '{$today}'
              ) AS pay_leave_taken
			  ,(SELECT SUM( on_leave )
				FROM attendance
				WHERE staff_id = s.staff_id
				AND type_of_leave = 'Holiday'
				AND record_date BETWEEN '{$from_date}' AND '{$today}'
              ) AS holiday
        FROM timesheet tm
        LEFT JOIN staff s ON (tm.staff_id = s.staff_id)
        LEFT JOIN task t ON (t.task_id = tm.task_id)
        LEFT JOIN task_history th ON (t.task_id = th.task_id)
        LEFT JOIN project p ON (p.project_id = t.project_id)
        LEFT JOIN attendance a ON (a.staff_id = s.staff_id)
        WHERE (t.status =  'Due' || t.status  =  'Late' || t.status  =  'Complete')
          AND tm.entry_date = '{$today}'
          AND a.record_date = '{$today}'
          AND th.task_history_id = tm.task_history_id
        ORDER BY staff_name
        ";
        $result  = $db->sql_query($SQL);

        $staff_name1 = '';
        $rows = '';
        $headerText = '';
        
        $data = array();
        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
            
            $staff_name = $row['staff_name'];

            if ($staff_name != $staff_name1){
                $data[$staff_name] = array();
                $staff_name1 = $staff_name;
            }

            $data[$staff_name][] = $row;
        }

        foreach ($data as $staff_name => $rows){
            $rowText = "";
            $staff_name_disp = "";
            
            foreach ($rows as $key => $row ){
                $leave_time = strtotime($row['leave_time']);
                $time_in = strtotime($row['time_in']);
                $break = strtotime('02:30:00');

                $rowText .= "
                <tr>
                    <td width='200'>{$row['project_title']}</td>
                    <td colspan='2' width='200'>{$row['title']}</td>
                    <td>{$row['task_history_title']}</td>
                    <td width='75'>{$row['hours']}</td>
                </tr>
                ";
                
                define("SECONDS_PER_HOUR", 60*60);            
                $difference = $leave_time - $time_in;
                $hours = round($difference / SECONDS_PER_HOUR, 0);
                $minutes = ($difference % SECONDS_PER_HOUR) / 60;
                $diff_time = strtotime($hours. ":" .$minutes);
                $total = $diff_time - $break;
                $totalHours = round($total / SECONDS_PER_HOUR, 0);
                $totalMinutes = ($total % SECONDS_PER_HOUR) / 60;
                
                if($row['leave_time'] != '00:00:00'){
                    $total_time = $totalHours. ":" .$totalMinutes;
                } else {
                    $total_time = '';
                }
                
                $staff_name_disp   = $row['staff_name'];
                $personal_leave_taken  = $row['personal_leave_taken'];
                $sick_leave_taken  = $row['sick_leave_taken'];
                $pay_leave_taken  = $row['pay_leave_taken'];
                $holiday  = $row['holiday'];
            }
        
            $headerText .= "
            <tr>
                <td colspan='5'>.</td>
            </tr>
            <tr>
                <td><b>{$staff_name_disp}</b></td>
                <td colspan='2'></td>
                <td colspan='2'><b>Work Hours: {$row['work_hrs_today']}</b></td>
            </tr>
            <tr>
                <td><b>Personal Leave:</b> {$personal_leave_taken}</td>
                <td colspan='2'><b>Sick Leave:</b> {$sick_leave_taken}</td>
                <td><b>Pay Leave:</b> {$pay_leave_taken}</td>
                <td width='75'><b>Holidays:</b> {$holiday}</td>
            </tr>
            <tr>
                <td><b>Time In:</b> {$row['time_in']}</td>
                <td colspan='2'><b>Time Out:</b> {$row['leave_time']}</td>
                <td colspan='2'><b>Total Hours:</b> {$total_time}</td>
            </tr>
            {$rowText}
            ";
        }

        $text = "
        <table border='1'>
            <tbody>
                {$headerText}
            </tbody>
        </table>
        ";

        $message     = $text;
        $subject     = "USS Hours" ." - " . strftime('%c');
        $fromName    = "Universal Software Solutions";
        $fromEmail   = "usstech@usoftsolutions.com";
        $toName      = "USS Tech";
        $toEmail     = "usstech@usoftsolutions.com";

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $status         = $fn->getReqParam('status');
        $project_id     = $fn->getReqParam('project_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');
        $staff_team     = $fn->getReqParam('staff_team');
        $company_id     = $fn->getReqParam('company_id');
        $userGroupID    = $fn->getSessionParam('userGroupID');

        if ($status == "" && $tv['searchDone'] == 0) {
            $status = "Due";
        }
                
        $stfCommon = "
        SELECT staff_id
             ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
        FROM staff a 
        WHERE 
        ";
        
        if ($userGroupID == 1) {
            $SQLStf = "
            {$stfCommon} a.status = 'Current' 
            ORDER BY staff_name
            ";
        } else if ($userGroupID == 2) {
            $SQLStf = "
            {$stfCommon} a.team ='{$_SESSION['staff_team']}' 
                     AND a.status = 'Current' 
            ORDER BY staff_name
            ";
        } else if ( $userGroupID == 3) {
            $SQLStf = "
            {$stfCommon} a.team ='{$_SESSION['staff_team']}' 
                     AND a.staff_id = '{$_SESSION['staff_id']}' 
                     AND a.status = 'Current' 
            ORDER BY staff_name
            ";
        }

        $SQLStatus = "
        SELECT value 
        FROM valuelist 
        WHERE key_text = 'taskStatus' 
        ORDER BY sort_order
        ";

        $SQLComp = "
        (
            SELECT DISTINCT c.company_id
                  ,c.company_name 
            FROM company c
            JOIN project p ON (p.company_id = c.company_id)
            JOIN task t    ON (t.project_id = p.project_id)
            WHERE c.category = 'Client' 
        ) 
        UNION 
        (
            SELECT DISTINCT c.company_id
                  ,c.company_name 
            FROM company c
            JOIN opportunity o ON (o.company_id     = c.company_id    )
            JOIN task t        ON (t.opportunity_id = o.opportunity_id)
            WHERE c.category = 'Client' 
        )
        ORDER BY company_name
        ";
        
        if ($tv['staff_id'] != "") {
            $SQLOpp = "
            SELECT a.opportunity_id
                  ,a.title
                  ,c.company_name 
            FROM opportunity a
                ,opportunity_staff b
                ,company c 
            WHERE a.opportunity_id = b.opportunity_id 
              AND b.staff_id = {$tv['staff_id']} 
              AND a.company_id = c.company_id
            ORDER BY c.company_name
            ";
        } else {
            $SQLOpp = "
            SELECT a.opportunity_id
                  ,a.title
                  ,c.company_name 
            FROM opportunity a
                ,company c 
            WHERE 
            a.company_id = c.company_id
            ORDER BY c.company_name
            ";
        }
        
        if ($tv['staff_id'] != "") {
            $SQLProj = "
            SELECT a.project_id
                  ,a.title
                  ,c.company_name 
            FROM project a
                ,project_staff b
                ,company c 
            WHERE a.project_id = b.project_id 
              AND b.staff_id = {$tv['staff_id']} 
              AND a.company_id = c.company_id
            ORDER BY c.company_name
            ";
        } else {
            $SQLProj = "
            SELECT a.project_id
                  ,a.title
                  ,c.company_name 
            FROM project a
                ,company c 
            WHERE a.company_id = c.company_id
            ORDER BY c.company_name
            ";
        }
        
        if ($userGroupID == 1) {
            $SQLTeam = "
            SELECT value 
            FROM valuelist 
            WHERE key_text = 'staffTeam' 
            ORDER BY sort_order
            ";
        } else if ($userGroupID == 2 || $userGroupID == 3) {
            $SQLTeam = "
            SELECT value 
            FROM valuelist 
            WHERE key_text = 'staffTeam' 
              AND value ='{$_SESSION['staff_team']}' 
            ORDER BY sort_order
            ";
        }
        
        $spArray = array("Flagged", "Not-Flagged");

        $text = "
        <td>
            <select name='staff_id' >
                <option value=''>{$cpCfg['m.enggCrm.staffFieldLabel']}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLStf, $tv['staff_id'])}
            </select>
        </td>

        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
            </select>
        </td>

        <td>
            <select name='company_id'>
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
            </select>
        </td>

        <td>
            <select name='opportunity_id'>
                <option value=''>Opportunity Name</option>
                {$dbUtil->getDropDownWithSeperator($db, $SQLOpp, $opportunity_id)}
            </select>
        </td>

        <td>
            <select name='project_id'>
                <option value=''>Project Name</option>
                {$dbUtil->getDropDownWithSeperator($db, $SQLProj, $project_id)}
            </select>
        </td>

        <td>
            <select name='staff_team' >
                <option value=''>{$cpCfg['m.project.staffTeamLabel']}</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLTeam, $staff_team)}
            </select>
        </td>

        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";
        
        return $text;
    }
}