<?
class CP_Admin_Modules_Payroll_Leave_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            $from_date = $dateUtil->formatDate($row['from_date'], 'DD-MM-YYYY');
            $to_date   = $dateUtil->formatDate($row['to_date'], 'DD-MM-YYYY');

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['employee_name'])}
            {$listObj->getListDataCell($row['designation'])}
            {$listObj->getListDataCell($row['status'], 'center')}
            {$listObj->getListDataCell($from_date, 'center')}
            {$listObj->getListDataCell($to_date, 'center')}
            {$listObj->getListDataCell($row['no_of_days'], 'center')}
            {$listObj->getListDataCell($row['no_of_days_next_month'], 'center')}
            {$listObj->getListDataCell($row['leave_type'])}
            {$listObj->getListRowEnd($row['leave_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Employee Name', 's.employee_name')}
        {$listObj->getListHeaderCell('Designation', 'j.designation')}
        {$listObj->getListHeaderCell('Status', 'l.status', 'headerCenter')}
        {$listObj->getListHeaderCell('From date', 'l.from_date', 'headerCenter')}
        {$listObj->getListHeaderCell('To date', 'l.to_date', 'headerCenter')}
        {$listObj->getListHeaderCell('No Of Days (Current Month)', 'l.no_of_days', 'headerCenter')}
        {$listObj->getListHeaderCell('No Of Days (Next Month)', 'l.no_of_days_next_month', 'headerCenter')}
        {$listObj->getListHeaderCell('Leave Type', 'l.leave_type')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn      = Zend_Registry::get('fn');
        
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "WHERE e.site_id = {$cpSiteIdSession}";
        }

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        {$appendSqlSite}
        ORDER BY employee_name
        ";

        $fieldset = "
        {$formObj->getDDRowBySQL('Employee Name *', 'employee_id', $sqlEmployeeName)}
        {$formObj->getDateRow('From date (YYYY-MM-DD) *', 'from_date')}
        {$formObj->getDateRow('To date (YYYY-MM-DD) *', 'to_date')}
        {$formObj->getDDRowByArr('Type of Leave *', 'leave_type', $cpCfg['m.payroll.leave.leaveType'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite   = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "WHERE e.site_id = {$cpSiteIdSession}";
        }

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        {$appendSqlSite}
        ORDER BY employee_name
        ";

        $expStf     = array('detailValue' => $row['employee_name']);
        $expNoEdit  = array('isEditable' => 0);
        $expVl      = array('sqlType' => 'OneField');
        $status     = $fn->getReqParam('status');
        $leave_type = $fn->getReqParam('leave_type');

        $date1 = $fn->getCPDate($row['from_date'], 'm');
        $date2 = $fn->getCPDate($row['to_date'], 'm');
       
        $daysNextMonth ='';
        if($date1 != $date2){
            $daysNextMonth = "<td>{$formObj->getTARow('No Of Days (Next Month)', 'no_of_days_next_month', $row['no_of_days_next_month'])}</td>";
        }

        if ($row['leave_type'] == 'Annual Leave') {
            $expOverseas = array('rowCls' => '');
        } else {
            $expOverseas = array('rowCls' => 'hideme');
        }

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Main Details)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <div><strong>NOTE: Leave status should be APPROVED to get applied in Payroll</strong></div>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Employee Name', 'employee_id', $sqlEmployeeName, $row['employee_name'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Designation', 'designation', $row['designation'], $expNoEdit)}</td>
                                <td>{$formObj->getDateRow('Date (YYYY-MM-DD)', 'date', $row['date'])}</td>
                                <td>{$formObj->getDDRowByArr('Status *', 'status', $cpCfg['m.payroll.leave.leaveStatusArr'], $row['status'])}</td>
                                <td>{$formObj->getDDRowByArr('Type of Leave *', 'leave_type', $cpCfg['m.payroll.leave.leaveType'], $row['leave_type'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getYesNoRRow('Went Overseas', 'went_overseas', $row['went_overseas'], $expOverseas)}</td>
                            </tr>
                            <tr>
                                <th colspan='5'>More Details</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getDateRow('From date (YYYY-MM-DD) *', 'from_date', $row['from_date'])}</td>
                                <td>{$formObj->getDateRow('To date (YYYY-MM-DD) *', 'to_date', $row['to_date'])}</td>
                                <td>{$formObj->getTARow('No Of Days (Current Month) *', 'no_of_days', $row['no_of_days'])}</td>
                                {$daysNextMonth}
                                <td>{$formObj->getTARow('Reason', 'reason', $row['reason'])}</td>
                            </tr>
                        </tbody>
                    </table>
                    <input type='hidden' name='emp_id' value='{$row['employee_id']}'>
                    <input type='hidden' name='emp_type' value='{$row['citizen']}'>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
    *
    */    

    function getEditold($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $sqlEmployeeName = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS employee_name
        FROM staff s
        ORDER BY employee_name
        ";

        $expStf = array('detailValue' => $row['employee_name']);
        $expNoEdit  = array('isEditable' => 0);

        $expVl = array('sqlType' => 'OneField');

        $status = $fn->getReqParam('status');

        $StatusArray = array(
              "Applied"
             ,"Waiting for Approval"
             ,"Denied"
             ,"Approved"
             ,"Hold"
        );

        $leave_type = $fn->getReqParam('leave_type');

        $leavetypeArray = array(
              "Annual Leave"
             ,"Personal Leave"
             ,"Sick Leave"
             ,"Maternity Leave"
        );

        $fieldset1 = "
        {$formObj->getDDRowBySQL('Employee Name', 'employee_id', $sqlEmployeeName, $row['employee_name'], $expNoEdit)}
        {$formObj->getTBRow('Designation', 'designation', $row['designation'])}
        {$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getDDRowByArr('Status', 'status', $StatusArray, $row['status'])}
        {$formObj->getDDRowByArr('Type of Leave', 'leave_type', $leavetypeArray, $row['leave_type'])}
        ";

        $fieldset2 = "
        {$formObj->getDateRow('From date', 'from_date', $row['from_date'])}
        {$formObj->getDateRow('To date', 'to_date', $row['to_date'])}
        {$formObj->getTARow('No Of Days', 'no_of_days', $row['no_of_days'])}
        {$formObj->getTARow('Reason', 'reason', $row['reason'])}
        ";


        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('More Details', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;

    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expValuelist = array('globalForAllSites' => true);
        $sqlCategory  = $fn->getValueListSQL('companyCategory', '', $expValuelist);
        $sqlStatus    = $fn->getValueListSQL('companyStatus', '', $expValuelist);
        
        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn              = Zend_Registry::get('fn');
        $db              = Zend_Registry::get('db');
        $cpCfg           = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media           = Zend_Registry::get('media');
        $comment         = getCPPluginObj('common_comment');

        $record_id   = $fn->getIssetParam($row, 'leave_id');
        $leave_id    = $fn->getReqParam('leave_id');
        $employee_id = $fn->getReqParam('employee_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_leave', 'attachment', $row)}
        ";

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite   = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND l.site_id = {$cpSiteIdSession}";
        }

        $sqlLeave = "
        SELECT l.*
        FROM `leave` l
        WHERE l.leave_id = {$row['leave_id']}
        AND l.employee_id = {$row['employee_id']}
        {$appendSqlSite}
        ";

        $resultLeave = $db->sql_query($sqlLeave);
        $rowLeave    = $db->sql_fetchrow($resultLeave);

        $printText = "";
        if ($rowLeave['leave_id'] != '') {
            $printText .="
            <div id='renewalLinkPortal'>{$this->getAddLeave($row['leave_id'], $row['employee_id'])}</div>
            ";
        }

        $text = $text.$printText;

        return $text;
    }

   /**
     *
     */
    function getAddLeave($leave_id = '', $employee_id = ''){
        $cpCfg   = Zend_Registry::get('cpCfg');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil  = Zend_Registry::get('dbUtil');

        if($leave_id == ''){
            $leave_id = $fn->getReqParam('leave_id');
        }

        //$employee_id = $fn->getReqParam('employee_id');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite   = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
        }

        $Leave    = $this->getAddLeaveDetail($leave_id, $employee_id);
        $recCount = $fn->getRecordCount('leave', "employee_id = '{$employee_id}' AND leave_id < {$leave_id} {$appendSqlSite}");

        $header ="
        <thead>
            <tr>
            <th>Type of Leave</th>
            <th>From Date</th>
            <th>To Date</th>
            <th>No of Days</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        /*$formActionLeave = "index.php?module=payroll_leave&_spAction=Leave&leave_id={$leave_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddLeave' href='{$formActionLeave}' leave_id={$leave_id}>Add</a>
                </div>";*/

        $text = "
        <div class='linkPortalWrapper payroll_leave__payroll_leaveLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Past Leave HIstory</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody>
                            {$Leave}
                        </tbody>
                    </table>
                    <input type='hidden' name='leave_id' value='{$leave_id}' />
                    <input type='hidden' name='employee_id' value='{$employee_id}' />
                </form> 
            </div>
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddLeaveDetail($leave_id = '', $employee_id = ''){
        $cpCfg    = Zend_Registry::get('cpCfg');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $formObj  = Zend_Registry::get('formObj');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($leave_id == ''){
            $leave_id = $fn->getReqParam('leave_id');
        }

        if($employee_id == ''){
            $employee_id = $fn->getReqParam('employee_id');
        }

        $rows  = "";

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite   = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT * FROM `leave` 
        WHERE employee_id = {$employee_id}
        AND leave_id < {$leave_id}
        {$appendSqlSite}
        ";

        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $from_date = $dateUtil->formatDate($row['from_date'], 'DD-MM-YYYY');
            $to_date   = $dateUtil->formatDate($row['to_date'], 'DD-MM-YYYY');

            $rows .= "
                <tr>
                    <td>{$row['leave_type']}</td>
                    <td>{$from_date}</td>
                    <td>{$to_date}</td>
                    <td>{$row['no_of_days']}</td>
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db     = Zend_Registry::get('db');
        $tv     = Zend_Registry::get('tv');
        $fn     = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $employee_id = $fn->getReqParam('employee_id');
        $status      = $fn->getReqParam('status');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $appendSqlSite   = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "WHERE e.site_id = {$cpSiteIdSession}";
        }

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        {$appendSqlSite}
        ORDER BY employee_name
        ";

        $spArray = array(
            ""
           ,"Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='employee_id'>
                <option value=''>Employee Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeName, $employee_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.payroll.leave.leaveStatusArr'], $tv['status'])}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getViewLeaveRecords() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $leave_type  = $fn->getReqParam('leave_type');
        $employee_id = $fn->getReqParam('employee_id');
        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite   = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND l.site_id = {$cpSiteIdSession}";
        }

        $sqlLeave  = "
        SELECT l.* FROM `leave` l
        WHERE l.employee_id = '{$employee_id}'
          AND l.status = 'Approved'
          AND l.leave_type = '{$leave_type}'
          AND (l.from_date BETWEEN '{$start_date}' AND '{$end_date}'
            OR l.to_date BETWEEN '{$start_date}' AND '{$end_date}')
          {$appendSqlSite}
        ";
        $resultLeave = $db->sql_query($sqlLeave);  
        $numRows = $db->sql_numrows($resultLeave);

        if ($numRows == "0") {
            return "<div class='txtCenter'><b>Sorry no records found.</b></div>";
        }

        $rows = '';
        $counter = 1;
        while ($rowLeave = $db->sql_fetchrow($resultLeave)) {
            $from_date = $dateUtil->formatDate($rowLeave['from_date'], 'DD-MM-YYYY');
            $to_date   = $dateUtil->formatDate($rowLeave['to_date'], 'DD-MM-YYYY');
            
            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$from_date}</td>
                <td>{$to_date}</td>
                <td>{$rowLeave['no_of_days']}</td>
                <td>{$rowLeave['no_of_days_next_month']}</td>
                <td>{$rowLeave['reason']}</td>
            </tr>
            ";
            $counter++;
        }

        $text = "
        <table border='1' width='100%' class='thinlist'>
            <thead>
                <th width='5%'>S.No</th>
                <th width='10%'>From Date</th>
                <th width='10%'>To Date</th>
                <th width='27%'>No of Days (Current Month)</th>
                <th width='22%'>No of Days (Next Month)</th>
                <th width='26%'>Reason</th>
            </thead>
            <tbody>
                {$rows}
            </tbody
        </table>
        ";

        return $text;
    }
}