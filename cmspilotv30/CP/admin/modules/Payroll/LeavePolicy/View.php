<?
class CP_Admin_Modules_Payroll_LeavePolicy_View extends CP_Common_Lib_ModuleViewAbstract
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

        $created_by  = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
        $modified_by = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['type_of_leave'])}
            {$listObj->getListDataCell($created_by)}
            {$listObj->getListDataCell($modified_by)}
            {$listObj->getListRowEnd($row['leave_policy_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Type of Leave', 'lp.type_of_leave')}
        {$listObj->getListHeaderCell('Created By', 'lp.created_by')}
        {$listObj->getListHeaderCell('Modified By', 'lp.modified_by')}
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
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $type_of_leave  = $fn->getReqParam('type_of_leave');

        $leavetypeArray = array(
              "Annual Leave"
             ,"Personal Leave"
             ,"Sick Leave"
             ,"Maternity Leave"
        );
        $fieldset = "
        {$formObj->getDDRowByArr('Type Of Leave', 'type_of_leave', $leavetypeArray)}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $type_of_leave  = $fn->getReqParam('type_of_leave');

        $leavetypeArray = array(
              "Annual Leave"
             ,"Personal Leave"
             ,"Sick Leave"
             ,"Maternity Leave"
        );

        $expNoEdit  = array('isEditable' => 0);

        $expVl = array('sqlType' => 'OneField');
        
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
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Type Of Leave', 'type_of_leave', $leavetypeArray, $row['type_of_leave'])}</td>
                                <td>{$formObj->getTARow('Notes', 'notes', $row['notes'])}</td>
                            </tr>
                        </tbody>
                    </table>
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $type_of_leave  = $fn->getReqParam('type_of_leave');

        $leavetypeArray = array(
              "Annual Leave"
             ,"Personal Leave"
             ,"Sick Leave"
             ,"Maternity Leave"
        );

        $expNoEdit  = array('isEditable' => 0);

        $expVl = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getDDRowByArr('Type Of Leave', 'type_of_leave', $leavetypeArray, $row['type_of_leave'])}
        {$formObj->getTARow('Notes', 'notes', $row['notes'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'leave_policy_id');
        $leave_policy_id  = $fn->getReqParam('leave_policy_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_leavePolicy', 'attachment', $row)}
        ";

        $sqlLeavepolicy = "
        SELECT lp.*
        FROM leave_policy lp
        WHERE lp.leave_policy_id = {$row['leave_policy_id']}
        ";

        $resultLeavepolicy = $db->sql_query($sqlLeavepolicy);
        $rowLeavepolicy = $db->sql_fetchrow($resultLeavepolicy);

        $printText ="";
        if ($rowLeavepolicy['leave_policy_id'] != '') {
            $printText .="
            <div id='renewalLinkPortal'>{$this->getAddLeavepolicy($row['leave_policy_id'])}</div>
            ";
        }
        $text=$text.$printText;

        return $text;
    }

    /**
     *
     */
    function getAddLeavepolicy($leave_policy_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($leave_policy_id == ''){
            $leave_policy_id = $fn->getReqParam('leave_policy_id');
        }

        $Leavepolicy = $this->getAddLeavepolicyDetail($leave_policy_id);

        $recCount = $fn->getRecordCount('leave_policy_employee_type', "leave_policy_id = '{$leave_policy_id}'");

        $header ="
        <thead>
            <tr>
            <th>Employee Group</th>
            <th>No of Days</th>
            <th>Created By</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $formActionLeavepolicy = "index.php?module=payroll_leavePolicy&_spAction=Leavepolicy&leave_policy_id={$leave_policy_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddLeavepolicy' href='{$formActionLeavepolicy}' leave_policy_id={$leave_policy_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper payroll_leavePolicy__payroll_leave_policy_employee_typeLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Leave Policy Linked to Group</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddLeavepolicyPortal'>
                            {$Leavepolicy}
                        </tbody>
                    </table>
                    <input type='hidden' name='leave_policy_id' value='{$leave_policy_id}' />
                </form>
            </div>
            {$add}
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddLeavepolicyDetail($leave_policy_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($leave_policy_id == ''){
            $leave_policy_id = $fn->getReqParam('leave_policy_id');
        }

        $leave_policy_employee_type_id = $fn->getReqParam('leave_policy_employee_type_id');

        $rows  = "";

        $SQL="
        SELECT lpe.*
        FROM leave_policy_employee_type lpe
        WHERE leave_policy_id = '{$leave_policy_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteLeavepolicy' href='#'  leave_policy_employee_type_id='{$row['leave_policy_employee_type_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";
            $created_by  = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');

            $rows .= "
                <tr>
                    <td>{$row['employee_group']}</td>
                    <td>{$row['no_of_days']}</td>
                    <td>{$created_by}</td>
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

    function getLeavepolicy() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $row = '';

        $leave_policy_id  = $fn->getReqParam('leave_policy_id');

        $formAction = "index.php?_topRm=order&module=payroll_leavePolicy&_spAction=LeavepolicyFormSubmit&showHTML=0";

        $expVl = array('sqlType' => 'OneField');
        $sqlEmployeeGroup = $fn->getValueListSQL('employeeGroup');

       /* $employeegroupArray = array(
              "Group 1"
             ,"Group 2"
        );*/


        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Employee Group', 'employee_group', $sqlEmployeeGroup, '', $expVl)}
            {$formObj->getTARow('No of Days', 'no_of_days')}
            <input type='hidden' name='leave_policy_id' value='{$leave_policy_id}' />
        </form>
        ";
        return $text;
    }


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $employee_id = $fn->getReqParam('employee_id');
        $status   = $fn->getReqParam('status');

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";

        $spArray = array(
            ""
           ,"Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <!--<td>
            <select name='employee_id' >
                <option value=''>Employee Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeName, $employee_id)}
            </select>
        </td>-->
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }
}