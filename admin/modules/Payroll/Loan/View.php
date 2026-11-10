<?
class CPL_Admin_Modules_Payroll_Loan_View extends CP_Admin_Modules_Payroll_Loan_View
{
    /**
     *
     */
    function getList($dataArray){
        $db = Zend_Registry::get('db');
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $date = $dateUtil->formatDate($row['date'], 'DD-MM-YYYY');
            $amount = number_format($row['amount'], 2);

            $sqlLoanPrev = "
            SELECT SUM(loan_repayment_amount_per_month) AS total_repaid_amount
            FROM loan_repayment_history
            WHERE loan_id = {$row['loan_id']}
            ";
            $resultLoanPrev = $db->sql_query($sqlLoanPrev);
            $rowLoanPrev = $db->sql_fetchrow($resultLoanPrev);
            $total_repaid_amt = number_format($rowLoanPrev['total_repaid_amount'], 2);

            $total_amt_payable = number_format($row['amount'] - $rowLoanPrev['total_repaid_amount'], 2);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['employee_name'])}
            {$listObj->getListDataCell($date)}
            {$listObj->getListDataCell($amount, 'right')}
            {$listObj->getListDataCell($row['month_amount'], 'right')}
            {$listObj->getListDataCell($total_repaid_amt, 'right')}
            {$listObj->getListDataCell($total_amt_payable, 'right')}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['loan_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Employee Name', 's.employee_name')}
        {$listObj->getListHeaderCell('Loan Application Date', 'l.date')}
        {$listObj->getListHeaderCell('Total Loan Amount', 'l.amount', 'txtRight')}
        {$listObj->getListHeaderCell('Amount payable(per month)', 'l.month_amount', 'txtRight')}
        {$listObj->getListHeaderCell('Total Amount Paid', '', 'txtRight')}
        {$listObj->getListHeaderCell('Amount Payable', '', 'txtRight')}
        {$listObj->getListHeaderCell('Status', 'l.status')}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";

        $expEdit   = array('isEditable' => 0);

        $fieldset = "
        {$formObj->getTBRow('', "error_box", '', $expEdit)}
        {$formObj->getDDRowBySQL('Employee Name *', 'employee_id', $sqlEmployeeName)}
        {$formObj->getTBRow('Total Loan Amount *', 'amount')}
        {$formObj->getTBRow('Amount payable(per month) *', 'month_amount')}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";
        $expStf = array('detailValue' => $row['employee_name']);
        $expNoEdit  = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');

        if ($row['status'] == 'Active' || $row['status'] == 'Closed') {
        	$status = "
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.payroll.loan.loanStatusArr'], $row['status'], $expNoEdit)}
            <input type='hidden' name='status' value='{$row['status']}'>
            ";
        } else {
        	$status = $formObj->getDDRowByArr('Status', 'status', $cpCfg['m.payroll.loan.loanStatusArr'], $row['status']);
        }

        if ($row['status'] == 'Approved' || $row['status'] == 'Active' || $row['status'] == 'Closed') {
            $total_loan_amount = "
            {$formObj->getTBRow('Total Loan Amount', 'amount', $row['amount'], $expNoEdit)}
            <input type='hidden' name='amount' value='{$row['amount']}'>
            ";
        } else {
            $total_loan_amount = $formObj->getTBRow('Total Loan Amount*', 'amount', $row['amount']);
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
                    <table class='thinlist'>
                        <tbody>
                        	<tr>
                        		<td colspan='4' align='center'>{$formObj->getTBRow('', "error_box", '', $expNoEdit)}</td>
                        	</tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Employee Name', 'employee_id', $sqlEmployeeName, $row['employee_name'], $expNoEdit)}</td>
                                <td>{$status}</td>
                                <td>{$formObj->getDDRowByArr('Type of Loan*', 'type', $cpCfg['m.payroll.loan.loanTypeArr'], $row['type'])}</td>
                                <td>{$formObj->getDateRow('Loan Application Date', 'date', $row['date'])}</td>
                            </tr>
                            <tr>
                                <td>{$total_loan_amount}</td>
                                <td>{$formObj->getTBRow('Amount payable(per month)*', 'month_amount', $row['month_amount'])}</td>
                                <td>{$formObj->getDateRow('Loan start date', 'loan_start_date', $row['loan_start_date'], $expNoEdit)}</td>
                                <td>{$formObj->getDateRow('Actual Loan closing date', 'loan_closing_date', $row['loan_closing_date'], $expNoEdit)}</td>
                            </tr>
                            <tr>
                                <td colspan='2'>{$formObj->getTARow('Notes', 'notes', $row['notes'])}</td>
                                <td></td>
                                <td></td>
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

        $record_id = $fn->getIssetParam($row, 'loan_id');
        $loan_id  = $fn->getReqParam('loan_id');
        $employee_id = $fn->getReqParam('employee_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_loan', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('payroll_loan', 'payroll_loanRepaymentLink', 'Payment History', $row)}
        ";

        $sqlLoan = "
        SELECT l.*
        FROM `loan` l
        WHERE l.loan_id = {$row['loan_id']}
        AND employee_id = {$row['employee_id']}
        ";

        $resultLoan = $db->sql_query($sqlLoan);
        $rowLoan = $db->sql_fetchrow($resultLoan);

        $printText ="";
        if ($rowLoan['loan_id'] != '') {
            $printText .="
            <div id='renewalLinkPortal'>{$this->getAddLoan($row['loan_id'], $row['employee_id'])}</div>
            ";
        }
        $text=$text.$printText;

        return $text;
    }

    /**
     *
     */
    function getAddLoan($loan_id='',$employee_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($loan_id == ''){
            $loan_id = $fn->getReqParam('loan_id');
        }


        $Loan = $this->getAddLoanDetail($loan_id, $employee_id);

        $recCount = $fn->getRecordCount('loan', "employee_id = '{$employee_id}' AND loan_id < {$loan_id}");

        $header ="
        <thead>
            <tr>
                <th>Type of Loan</th>
                <th>Status</th>
                <th>Loan Application Date</th>
                <th>Loan start date</th>
                <th class='txtRight'>Total Loan Amount</th>
                <th class='txtRight'>Amount payable(per month)</th>
                <th>Actual Loan closing date</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $text = "
        <div class='linkPortalWrapper payroll_loan__payroll_leaveLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Previous/Earlier Loans</div>
                    <div class='toggle'></div>
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
                            {$Loan}
                        </tbody>
                    </table>
                    <input type='hidden' name='loan_id' value='{$loan_id}' />
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
    function getAddLoanDetail($loan_id = '',$employee_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($loan_id == ''){
            $loan_id = $fn->getReqParam('loan_id');
        }

        if($employee_id == ''){
            $employee_id = $fn->getReqParam('employee_id');
        }

        $loanRec = $fn->getRecordRowById('loan', 'loan_id', $loan_id);

        $SQL="
        SELECT * FROM `loan` 
        WHERE employee_id = {$employee_id}
        AND date < '{$loanRec['date']}';
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        $rows  = "";
        while ($row = $db->sql_fetchrow($result)) {
            $application_date = $dateUtil->formatDate($row['date'], 'DD-MM-YYYY');
            $loan_start_date = $dateUtil->formatDate($row['loan_start_date'], 'DD-MM-YYYY');
            $loan_closing_date = $dateUtil->formatDate($row['loan_closing_date'], 'DD-MM-YYYY');
            $amount = number_format($row['amount'], 2);
            $month_amount = number_format($row['month_amount'], 2);

            $rows .= "
                <tr>
                    <td>{$row['type']}</td>
                    <td>{$row['status']}</td>
                    <td>{$application_date}</td>
                    <td>{$loan_start_date}</td>
                    <td class='txtRight'>{$amount}</td>
                    <td class='txtRight'>{$month_amount}</td>
                    <td>{$loan_closing_date}</td>
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
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $employee_id = $fn->getReqParam('employee_id');
        $status   = $fn->getReqParam('status');

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";
        //$sqlStatus = $fn->getValueListSQL('companyStatus');
        $status = $fn->getReqParam('status');

        $spArray = array(
            ""
           ,"Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='employee_id' >
                <option value=''>Employee Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeName, $employee_id)}
            </select>
        </td>
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.payroll.loan.loanStatusArr'], $tv['special_search'])}
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
}