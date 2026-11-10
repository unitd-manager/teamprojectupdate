<?
class CP_Admin_Modules_ManPower_Task_View extends CP_Common_Lib_ModuleViewAbstract
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

            if ($cpCfg['m.manPower.task.hightlightDue'] == 1) {

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

            if ($cpCfg['m.manPower.task.taskListFieldsOrderGroup'] == 2) {
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

            $timeUrl = "index.php?module=manPower_timesheet&_spAction=newRecordFromTask&task_id={$row['task_id']}&showHTML=0";

            $addTime = "
            <a class='addToTime' dialogTitle=\"Add time for: {$row['title']}\" href='javascript:void(0);' link='{$timeUrl}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/plus.png' border='0'>
            </a>
            ";

            $goToTime = "
            <div align='left' style='float:left'>
                <a href='index.php?_topRm=project&module=manPower_timesheet&_action=list&task_id={$row['task_id']}'>
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

        if ($cpCfg['m.manPower.task.taskListFieldsOrderGroup'] == 2) {
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

        if ($cpCfg['m.manPower.task.daysLbl'] == 1){
            $est = $listObj->getListHeaderCell('Est. Days', 't.estimated_hours', 'w65');
            $total = 'Total Days';
        } else {
            $est = $listObj->getListHeaderCell('Est. Hours', 't.estimated_hours', 'w65');
            $total = 'Total Hours';
        }
        
        $text = "
        {$listObj->getListHeader()}
        {$taskListFieldsOrderGroupHeader}
        {$listObj->getListHeaderCell('Staff Name', 'staff_names')}
        {$listObj->getListHeaderCell('Staff Team', 'staff_team')}
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

        $sqlPM = $fn->getDDSql('manPower_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));

        if ($row['project_or_opp'] == 'Project'){
            $projUrl = "index.php?_topRm={$tv['topRm']}&module=manPower_project&record_id={$row['project_id']}&_action=detail";
            $projUrl = "<a href='{$projUrl}'>{$row['project_opp_code']}</a>";
        } else {
            $oppUrl = "index.php?_topRm={$tv['topRm']}&module=manPower_opportunity&record_id={$row['opportunity_id']}&_action=detail";
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
            $oppUrl = "index.php?_topRm={$tv['topRm']}&module=manPower_opportunity&record_id={$row['opportunity_id']}&_action=detail";
            $code = "<a href='{$oppUrl}'>{$row['project_opp_code']}</a>";
        } else if ($row['project_id'] != ''){
            $projUrl = "index.php?_topRm=project&module=manPower_project&record_id={$row['project_id']}&_action=detail";
            $code    = "<a href='{$projUrl}'>{$row['project_opp_code']}</a>";
        } else {
            $code = '';
        }

        if($cpCfg['m.manPower.task.showReleaseStatus'] == 1){
            $releaseStatus = "
            {$formObj->getYesNoRRow('Release Full Status', 'release_task_status', $row['release_task_status'])}
            ";
        }
        
        if($cpCfg['m.manPower.task.daysLbl'] == 1){
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

        $sqlPM = $fn->getDDSql('manPower_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));
        $sqlStaff = $fn->getDDSql('manPower_staff', array('condn' => "status = 'Current'"));

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
        if ($cpCfg['m.manPower.task.hasTaskHistory'] == 1){
            $links = $displayLinkData->getLinkPortalMain('manPower_task', 'project_taskHistoryLink', 'Task History', $row);
        }
        
        $text = "
        {$media->getRightPanelMediaDisplay('Attachment', 'manPower_task', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('manPower_task', 'manPower_timesheetLink', 'Timesheet', $row)}
        {$displayLinkData->getLinkPortalMain('manPower_task', 'core_staffLink', 'Staff Linked', $row)}
        {$links}
        {$comment->getView(array(
             'roomName' => 'manPower_task'
            ,'recordId' => $record_id
            ,'contactModule' => 'manPower_staff'
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

        $today = date('Y-m-d');

        $SQL = "
        SELECT t.title
              ,p.title AS project_title
              ,tm.hours
              ,t.task_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,s.staff_id
              ,(SELECT SUM(hours)
                FROM timesheet tm
                WHERE tm.staff_id = s.staff_id
                  AND tm.entry_date = '{$today}'
              ) AS hrs_today
        FROM timesheet tm
        LEFT JOIN staff s ON (tm.staff_id = s.staff_id)
        LEFT JOIN task t ON (t.task_id = tm.task_id)
        LEFT JOIN project p ON (p.project_id = t.project_id)
        WHERE (t.status =  'Due' || t.status  =  'Late' || t.status  =  'Complete')
          AND tm.entry_date = '{$today}'
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
                $rowText .= "
                <tr>
                    <td width='200'>{$row['project_title']}</td>
                    <td width='200'>{$row['title']}</td>
                    <td>{$row['hours']}</td>
                </tr>
                ";
                $staff_name_disp   = $row['staff_name'];
            }
        
            $headerText .= "
            <tr>
            <td colspan='3'> &nbsp;</td>
            </tr>
            <tr>
                <td colspan='2'><b>{$staff_name_disp}</b></td>
                <td><b>Total Hours: {$row['hrs_today']}</b></td>
            </tr>
            <tr>
                {$rowText}
            </tr>
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
        //print date("d/m/y : H:i:s", time());

        $subject     = "USS Hours" ." - " . strftime('%c');
        //$fromName    = $cpCfg['cp.companyName'];
        $fromName    = "Universal Software Solutions";
        //$fromEmail   = $cpCfg['companyEmail'];
        $fromEmail   = "usstech@usoftsolutions.com";
        
        $toName      = "USS Tech";
        $toEmail     = "usstech@usoftsolutions.com";

        $smtp  = includeCPClass('Lib', 'smtp', 'CPSMTP');
        $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);

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
        $userGroupType    = $fn->getSessionParam('userGroupType');

        if ($status == "" && $tv['searchDone'] == 0) {
            $status = "Due";
        }
                
        $appendStaffSql = '';
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $appendStaffSql = "AND a.site_id = '{$_SESSION['cp_site_id']}'"; 
        }

        $stfCommon = "
        SELECT staff_id
             ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
        FROM staff a 
        WHERE 
        ";
        
        $SQLStf = '';
        if ($userGroupType == 'Super Administrator') {
            $SQLStf = "
            {$stfCommon} a.status = 'Current'
            AND staff_login_type = 'Staff'
            {$appendStaffSql}
            ORDER BY staff_name
            ";
        } else if ($userGroupType == 'Administrator') {
            $SQLStf = "
            {$stfCommon} a.team ='{$_SESSION['staff_team']}' 
                     AND a.status = 'Current' 
            {$appendStaffSql}
            ORDER BY staff_name
            ";
        } else if ( $userGroupType == 'User') {
            $SQLStf = "
            {$stfCommon} a.team ='{$_SESSION['staff_team']}' 
                     AND a.staff_id = '{$_SESSION['staff_id']}' 
                     AND a.status = 'Current' 
            {$appendStaffSql}
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
        
        $SQLTeam = '';
        if ($userGroupType == 'Super Administrator') {
            $SQLTeam = "
            SELECT value 
            FROM valuelist 
            WHERE key_text = 'staffTeam' 
            ORDER BY sort_order
            ";
        } else if ($userGroupType == 'Administrator' || $userGroupType == 'User') {
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
                <option value=''>Staff Name</option>
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
                <option value=''>{$cpCfg['m.manPower.staffTeamLabel']}</option>
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