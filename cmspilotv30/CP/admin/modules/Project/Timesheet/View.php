<?
class CP_Admin_Modules_Project_Timesheet_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $fieldsArray = array();

    /**
     *
     */
    function Timesheet() {
    }

    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows2 = '';
            if ($cpCfg['m.project.task.taskListFieldsOrderGroup'] == 2) {
                $rows2 = "
                {$listObj->getGoToDetailText($rowCounter, $row['task_title'])}
                {$listObj->getGoToDetailText($rowCounter, $row['project_opp_code'])}
                {$listObj->getListDataCell($row['project_opp_title'])}
                {$listObj->getListDataCell($row['project_or_opp'])}
                {$listObj->getListDataCell($row['project_opp_company'])}
                ";
            } else {
                $rows2 = "
                {$listObj->getGoToDetailText($rowCounter, $row['project_opp_code'])}
                {$listObj->getListDataCell($row['project_opp_company'])}
                {$listObj->getListDataCell($row['project_opp_title'])}
                {$listObj->getListDataCell($row['project_or_opp'])}
                {$listObj->getListDataCell($row['task_title'])}
                {$listObj->getListDataCell($row['description'])}
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$rows2}
            {$listObj->getListDataCell($row['staff_name'], 'left', '', 85)}
            {$listObj->getListDataCell($row['staff_team'], 'left', '', 120)}
            {$listObj->getListDateCell($row['entry_date'], 'left', '', 75)}
            {$listObj->getListDataCell($row['hours'])}
            {$listObj->getListRowEnd($row['timesheet_id'])}
            ";
            $rowCounter++;
        }

        $text2 = '';
        if ($cpCfg['m.project.task.taskListFieldsOrderGroup'] == 2) {
            $text2 .= "
            {$listObj->getListHeaderCell('Task Title', 'task_title')}
            {$listObj->getListHeaderCell('Code', 'task_id')}
            {$listObj->getListHeaderCell('Project / Opportunity Name', 'project_opp_title')}
            {$listObj->getListHeaderCell('Type', 'project_or_opp')}
            {$listObj->getListHeaderCell('Company', 'project_opp_company')}
            ";
        } else {
            $text2 .= "
            {$listObj->getListHeaderCell('Code', 'project_opp_code')}
            {$listObj->getListHeaderCell('Company', 'project_opp_company')}
            {$listObj->getListHeaderCell('Project / Opportunity Name', 'project_opp_title')}
            {$listObj->getListHeaderCell('Type', 'project_or_opp')}
            {$listObj->getListHeaderCell('Task Title', 'task_title')}
            {$listObj->getListHeaderCell('Description', 'description')}
            ";
        }

        if ($cpCfg['m.project.timesheet.daysLbl'] == 1){
            $lbl = 'Days';
        } else {
            $lbl = 'Hours';
        }
        
        $text = "
        {$listObj->getListHeader()}
        {$text2}
        {$listObj->getListHeaderCell($cpCfg['m.project.staffFieldLabel'], 'staff_name')}
        {$listObj->getListHeaderCell($cpCfg['m.project.staffTeamLabel'], 'staff_team')}
        {$listObj->getListHeaderCell('Date', 't.entry_date')}
        {$listObj->getListHeaderCell($lbl, 't.hours')}
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

        return $this->getList($result);
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

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
        
        $exp = array('sqlType' => 'hasSeperator');

        $fieldset = "
        {$formObj->getDDRowBySQL('Project', 'project_id', $sqlProj, '', $exp)}
        {$formObj->getDDRowBySQL('Opportunity', 'opportunity_id', $sqlOpp, '', $exp)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $text  = '';
        $sqlTask = '';
                
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
            $sqlTask = $fn->getDDSql('project_task', array('condn' => "project_id = {$row['project_id']}"));
        } else if ($row['opportunity_id'] != ''){
            $sqlTask = $fn->getDDSql('project_task', array('condn' => "opportunity_id = {$row['opportunity_id']}"));
        }

        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));

        $exp = array('sqlType' => 'hasSeperator');

        $expProj   = array('detailValue' => $row['project_title'], 'sqlType' => 'hasSeperator');
        $expOpp    = array('detailValue' => $row['opportunity_title'], 'sqlType' => 'hasSeperator');
        $expTask   = array('detailValue' => $row['task_title']);
        $expStaff  = array('detailValue' => $row['staff_name']);
        $expNoEdit = array('isEditable' => 0);

        if ($row['opportunity_id'] != ''){
            $oppUrl = "index.php?_topRm={$tv['topRm']}&module=project_opportunity&record_id={$row['opportunity_id']}&_action=detail";
            $code = "<a href='{$oppUrl}'>{$row['project_opp_code']}</a>";
        } else if ($row['project_id'] != ''){
            $projUrl = "index.php?_topRm={$tv['topRm']}&module=project_project&record_id={$row['project_id']}&_action=detail";
            $code    = "<a href='{$projUrl}'>{$row['project_opp_code']}</a>";
        } else {
            $code = '';
        }

        if ($cpCfg['m.project.timesheet.daysLbl'] == 1){
            $lbl = 'Days';
        } else {
            $lbl = 'Hours';
        }

        $fieldset1 = "
        {$formObj->getTBRow($row['project_or_opp']. ' Code', 'project_code', $code, $expNoEdit)}
        {$formObj->getDDRowBySQL('Project', 'project_id', $sqlProj, $row['project_id'], $expProj)}
        {$formObj->getDDRowBySQL('Opportunity', 'opportunity_id', $sqlOpp, $row['opportunity_id'], $expOpp)}
        {$formObj->getDDRowBySQL('Task', 'task_id', $sqlTask,  $row['task_id'], $expTask)}
        {$formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlStaff, $row['staff_id'], $expStaff)}
        {$formObj->getDateRow('Date', 'entry_date', $row['entry_date'])}
        {$formObj->getTBRow($lbl, 'hours', $row['hours'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Timesheet Details', $fieldset1)}
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

        $sqlComp = $fn->getDDSql('project_company');
        $sqlOpp  = $fn->getDDSql('project_opportunity');
        $sqlProj = $fn->getDDSql('project_project');

        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));
        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));

        $sqlTeam = "
        SELECT DISTINCT team 
        FROM staff 
        ORDER BY team
        ";

        $expVl  = array('sqlType' => 'OneField');
        $sqlCat = $fn->getValueListSQL('taskCategory');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fieldset = "
        {$formObj->getDDRowBySQL('Client Name', 'company_id', $sqlComp)}
        {$formObj->getDDRowBySQL('Opportunity', 'opportunity_id', $sqlOpp)}
        {$formObj->getDDRowBySQL('Project', 'project_id', $sqlOpp)}
        {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM)}
        {$formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlStaff)}
        {$formObj->getDDRowBySQL('Staff Team', 'staff_team', $sqlTeam, '', $expVl)}
        {$formObj->getDDRowBySQL('Task Title', 'task_id')}
        {$formObj->getDDRowBySQL('Task Category', 'category', $sqlCat, '', $expVl)}
        {$formObj->getDateRangeRow('Date', 'entry_date')}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        {$formObj->getTARow('Description', 'description')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Timesheet Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'timesheet_id');

        $text = "
        {$comment->getView(array(
             'roomName' => 'project_timesheet'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getNewRecordFromTask() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $task_id = $fn->getReqParam('task_id');
        $task_history_id = $fn->getReqParam('task_history_id');
        $row = $fn->getRecordRowByID('task', 'task_id', $task_id);

        $project_id     = $row['project_id'];
        $opportunity_id = $row['opportunity_id'];
        
        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));
        
        $formAction = "index.php?_spAction=addRecordFromTask&module={$tv['module']}&showHTML=0";

        if ($cpCfg['m.project.timesheet.daysLbl'] == 1){
            $lbl = 'Days';
        } else {
            $lbl = 'Hours';
        }

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlStaff, $_SESSION['staff_id'])}
                {$formObj->getDateRow('Date', 'entry_date', date('Y-m-d'))}
                {$formObj->getTBRow($lbl, 'hours')}
                {$formObj->getTARow('Description (if any)', 'description')}
            </fieldset>
            <input type='hidden' name='task_id' value='{$task_id}' />
            <input type='hidden' name='task_history_id' value='{$task_history_id}' />
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getReportsMenu() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $entry_date_1 = $fn->getReqParam('entry_date_1');
        $entry_date_2 = $fn->getReqParam('entry_date_2');

        if ($tv['action'] == "detail") {

        } else {
            $qstr = $fn->getQueryStringForJasper();
            $printJasperUrl  = "index.php?_spAction=printReport&showHTML=0&{$qstr}&roomName={$tv['module']}&report=";
            $printMonthlySummaryUrl = "index.php?_spAction=timesheetSummaryByMonth&showHTML=0&module={$tv['module']}" .
                                      "&entry_date_1={$entry_date_1}&entry_date_2={$entry_date_2}";

            $text = "
            <h2>Reports:</h2>
            <ul class='printOptions'>
                <li><a href='{$printJasperUrl}timesheetSummaryList'>Timesheet Summary List</a></li>
                <li><a href='{$printJasperUrl}timesheetSummaryListByDay'>Timesheet Summary List / By Day</a></li>
                <li><a href='{$printMonthlySummaryUrl}'>Timesheet Summary / Month View</a></li>
            </ul>
            ";
        }

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

        $company_id     = $fn->getReqParam('company_id');
        $yearMonthStart = $fn->getReqParam('yearMonthStart');
        $project_id     = $fn->getReqParam('project_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');
        $task_id        = $fn->getReqParam('task_id');
        $category       = $fn->getReqParam('category');
        $staff_team     = $fn->getReqParam('staff_team');
        $userGroupID    = $fn->getSessionParam('userGroupID');
        $company_id     = $fn->getSessionParam('company_id');

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

        $SQLStatus  = $fn->getValueListSQL('taskStatus');

        $SQLComp = "
        (
            SELECT DISTINCT c.company_id
                  ,c.company_name 
            FROM company c
            JOIN project p ON (p.company_id = c.company_id)
            JOIN timesheet t ON (t.project_id = p.project_id)
            WHERE c.category = 'Client' 
        ) 
        UNION 
        (
            SELECT DISTINCT c.company_id
                  ,c.company_name 
            FROM company c
            JOIN opportunity o ON (o.company_id = c.company_id)
            JOIN timesheet t   ON (t.opportunity_id = o.opportunity_id)
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
            $SQLTeam  = $fn->getValueListSQL('staffTeam');
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
        
        $SQLCat    = $fn->getValueListSQL('taskCategory');

        $text = "
        <td>
            <select name='staff_id' >
                <option value=''>{$cpCfg['m.project.staffFieldLabel']}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLStf, $tv['staff_id'])}
            </select>
        </td>

        <td>
            <select name='staff_team' >
                <option value=''>{$cpCfg['m.project.staffTeamLabel']}</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLTeam, $staff_team)}
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
            <select name='category'>
                <option value=''>Task Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLCat, $category)}
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