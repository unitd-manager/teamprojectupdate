<?
class CPL_Admin_Modules_Payroll_Expense_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $searchDone = $fn->getReqParam('searchDone');
        $page = $tv['page'];

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $month           = date('m');
        $year            = date('Y');
        $expense_id      = $fn->getReqParam('expense_id');
        $date1           = $fn->getReqParam('date_1');
        $date2           = $fn->getReqParam('date_2');
        $group           = $fn->getReqParam('group');
        $sub_group       = $fn->getReqParam('sub_group');
        $current_month   = $fn->getReqParam('current_month');
        $yearVal         = $fn->getReqParam('year');
        $site_id         = $fn->getReqParam('site_id');
        $source          = $fn->getReqParam('source');
        $type            = $fn->getReqParam('type');

        $text = '';
        $rows = '';
        $readonly = '';
        $OrderItems = '';
        $grandTotal    = 0;
        $appendExpTotal = '';

        $rowCounter = 0;

        if ($group != "") {
            $appendExpTotal .= "AND e.group = '{$group}'";
        }

        if ($sub_group != "") {
            $appendExpTotal .= "AND e.sub_group = '{$sub_group}'";
        }

        if ($source != "") {
            $appendExpTotal .= "AND e.source = '{$source}'";
        }

        if ($type != "") {
            $appendExpTotal .= "AND e.type = '{$type}'";
        }


        if ($date1 != "" && $date2 != "") {
            $appendExpTotal .= "AND (e.date BETWEEN '{$date1}' AND '{$date2}')";
        }
        else{
            if ($current_month == '' && $yearVal == ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $appendExpTotal .= "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}' ";
            }

            if ($current_month != '') {
                $appendExpTotal .= "AND DATE_FORMAT(e.date, '%m') = '{$current_month}' " ;
            }
            if ($yearVal != '') {
                $appendExpTotal .= " AND DATE_FORMAT(e.date, '%Y') = '{$yearVal}'" ;
            }
        }

        $SQLExpTotal = "
        SELECT SUM(e.amount) AS total_amount
        FROM expense e 
        WHERE e.amount != ''
        {$appendExpTotal}
        ";
        $resultCollection = $db->sql_query($SQLExpTotal);
        $recCollection = $db->sql_fetchrow($resultCollection);
        $totalCollection = $recCollection['total_amount'];
        $grandTotal += $totalCollection;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $date = $fn->getCPDate($row['date'],"d-m-Y");

            $groupName = "";
            if($row['group'] != "" && $row['type'] == 'Expense'){
                $SQLEG = "
                SELECT title
                FROM expense_group
                WHERE expense_group_id = '{$row['group']}'
                ";
                $resultEG   = $db->sql_query($SQLEG);
                $rowEG = $db->sql_fetchrow($resultEG);

                $groupName = $rowEG['title'];
            }

            if($row['group'] != "" && $row['type'] == 'Income'){
                $SQLIG = "
                SELECT title
                FROM income_group
                WHERE income_group_id = '{$row['group']}'
                ";
                $resultIG   = $db->sql_query($SQLIG);
                $rowIG = $db->sql_fetchrow($resultIG);

                $groupName = $rowIG['title'];
            }

            $subGroupName = "";
            if($row['sub_group'] != "" && $row['type'] == 'Expense'){
                $appendSql = '';
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSql = "AND site_id = {$cpSiteIdSession}";
                }

                $SQLESG = "
                SELECT  title
                FROM expense_sub_group
                WHERE expense_sub_group_id = '{$row['sub_group']}'
                {$appendSql}
                ";
                $resultESG = $db->sql_query($SQLESG);
                $rowESG    = $db->sql_fetchrow($resultESG);

                $subGroupName = $rowESG['title'];
            }

            if($row['sub_group'] != "" && $row['type'] == 'Income'){
                $appendSql = '';
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSql = "AND site_id = {$cpSiteIdSession}";
                }

                $SQLISG = "
                SELECT  title
                FROM income_sub_group
                WHERE income_sub_group_id = '{$row['sub_group']}'
                {$appendSql}
                ";
                $resultISG = $db->sql_query($SQLISG);
                $rowISG    = $db->sql_fetchrow($resultISG);

                $subGroupName = $rowISG['title'];
            }

            /*if($row['amount'] != ""){
                $row['amount'] = number_format($row['amount'], 2);
            }*/

            $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');
            $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y H:i:s');

            if($row['modified_by'] != "" && $row['modification_date'] != ""){
                $createdModifiedBy = "<i>{$row['modified_by']} {$modification_date}</i>";
            }else{
                $createdModifiedBy = "<i>{$row['created_by']} {$creation_date}</i>";
            }

            if($row['amount'] == ''){
                $row['amount'] = 0;
            }

            if($row['gst'] == 1){
                if ($row['type'] == 'Expense' && $row['group'] == '2' && $row['sub_group'] == '12') {
                    $gst_amount = round(($row['service_charge']*$cpCfg['cp.gstPercentage'])/100, 2);
                    /*
                    $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1));
                    if ($fraction_length > 2) {
                        list($integer, $fraction) = explode(".", (string) $gst_amount);
                        $fraction = substr($fraction, 0, 2);
                        $gst_amount = $integer . "." . $fraction;
                    }
                    */
                    
                    $totalAmt = number_format($row['amount'] + $row['service_charge'] + $gst_amount, 2);
                } else {
                    $gst_amount = round(($row['amount']*$cpCfg['cp.gstPercentage'])/100, 2);
                    /*
                    $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1));
                    if ($fraction_length > 2) {
                        list($integer, $fraction) = explode(".", (string) $gst_amount);
                        $fraction = substr($fraction, 0, 2);
                        $gst_amount = $integer . "." . $fraction;
                    }
                    */

                    $totalAmt = number_format($row['amount'] + $gst_amount, 2);
                }
            } else {
                $totalAmt = number_format($row['amount'], 2);
            }

            $receiptBtn = '';
            if ($row['payment_status'] != 'Paid') {
                $formActionReceipt = "index.php?module=payroll_expense&_spAction=generatePaymentForm&expense_id={$row['expense_id']}&showHTML=0";

                $receiptBtn = "
                <div class='createPayment'>
                    <a href='{$formActionReceipt}' class='btn btn-warning generatePaymentList' id='generatePayment'>Create Payment</a>
                </div>
                ";
            }

            $recCount = $fn->getRecordCount('payment', "record_id = '{$row['expense_id']}'");

            $viewBtn = '';
            if ($recCount > 0) {
                $formActionView = "index.php?module=payroll_expense&_spAction=paymentPortalDisplay&expense_id={$row['expense_id']}&showHTML=0";

                $viewBtn = "
                <div class='createPayment'>
                    <a href='{$formActionView}' class='mt5 btn btn-info viewPayment' id='viewPayment'>View Payment</a>
                </div>
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($date)}
            {$listObj->getListDataCell($row['description'])}
            {$listObj->getListDataCell($totalAmt, 'right')}
            {$listObj->getListDataCell($row['type'])}
            {$listObj->getListDataCell($groupName)}
            {$listObj->getListDataCell($subGroupName)}
            {$listObj->getListDataCell($row['payment_status'])}
            {$listObj->getListDataCell($createdModifiedBy)}
            {$listObj->getListDataCell($receiptBtn.$viewBtn)}
            {$listObj->getListRowEnd($row['expense_id'])}
            ";
            $rowCounter++ ;
        }
        $grandTotal = number_format($grandTotal);
        
        $new_List    = "index.php?_topRm=accounts&module=payroll_expense";
        $cpSearch="
        <script>
            $('.cpSearch').css('display', 'block');
        </script>
        ";

        $_SESSION['expense_id'] = '';

        $text = "
        <div class='newListDisplay'>
            <div class='floatbox goToNewExpense'>
                <div class='float_right displayVisitRecords'>
                    <a href='#' class='btn btn-info'>New</a>
                </div>
            </div>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Date', 'e.date')}
            {$listObj->getListHeaderCell('Description', 'e.description')}
            {$listObj->getListHeaderCell('Amount', 'e.amount', 'txtRight')}
            {$listObj->getListHeaderCell('Type', 'e.type')}
            {$listObj->getListHeaderCell('Head', 'e.group')}
            {$listObj->getListHeaderCell('Sub Head', 'e.sub_group')}
            {$listObj->getListHeaderCell('Status', 'e.payment_status')}
            {$listObj->getListHeaderCell('Updated By', 'e.creation_date')}
            {$listObj->getListHeaderCell('')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        {$cpSearch}
        <div class='defaultListDisplay'>{$this->getnewList()}</div>
        ";

        return $text;
    }

    /**
     *
     */
    function getNewList(){
        $tv         = Zend_Registry::get('tv');
        $fn         = Zend_Registry::get('fn');
        $db         = Zend_Registry::get('db');
        $cpCfg      = Zend_Registry::get('cpCfg');
        $listObj    = Zend_Registry::get('listObj');
        $formObj    = Zend_Registry::get('formObj');
        $dateUtil   = Zend_Registry::get('dateUtil');

        $typeArr         = array('Income', 'Expense');
        $bankArr         = array('DBS', 'OCBC', 'UOB');
        $statusArr       = array('Unpaid', 'Paid', 'Partial Payment', 'Cancelled');
        $expHideFirstOpt = array('hideFirstOption' => 1);
        $current_date    = date('Y-m-d');
        $hidemeArr       = array('rowCls' => 'hideme');
        $expDateHideArr  = array('rowCls' => 'hideme', 'maxDate' => date('Y-m-d'), 'yearStart' => '2019');
        $rows            = '';
        
        $new_List             = "index.php?_topRm=accounts&module=payroll_expense";
        $formActionAddexpense = "index.php?module=payroll_expense&_spAction=newAdd&showHTML=0";

        $sqlCompany="
        SELECT company_id
              ,company_name
        FROM company
        WHERE status = 'Current'
        ORDER BY company_name ASC
        ";

        $sqlSupplier="
        SELECT supplier_id
              ,company_name
        FROM supplier
        WHERE status = 'Current'
        ORDER BY company_name ASC
        ";

        /*
        $sqlType = "
        (
        SELECT eg.expense_group_id AS expense_group_id
              ,0 AS income_group_id
              ,eg.title AS head
        FROM expense_group eg
        ) UNION (
        SELECT 0 AS expense_group_id
              ,ig.income_group_id AS income_group_id
              ,ig.title AS head
        FROM income_group ig
        )
        ";
        */
        $sqlType = "
        SELECT eg.expense_group_id AS expense_group_id
              ,eg.title AS head
        FROM expense_group eg
        ";
        $resultType = $db->sql_query($sqlType);
        $main_group = '';
        $main_group_in_loop = '';
        $rowsTypeDropDown = '';
        while ($rowType = $db->sql_fetchrow($resultType)) {
            if ($rowType['expense_group_id']) {
                $main_group = 'Expense';
            } else {
                $main_group = 'Income';
            }

            if ($main_group != $main_group_in_loop) {
                $label = strtoupper($main_group);
                $rowsTypeDropDown .= "<optgroup label='{$label}'></optgroup>";
            }

            if ($main_group == 'Expense') {
                $rowsTypeDropDown .= "<option value='{$rowType['expense_group_id']}_{$main_group}'>{$rowType['head']}</option>";
            } else {
                $rowsTypeDropDown .= "<option value='{$rowType['income_group_id']}_{$main_group}'>{$rowType['head']}</option>";
            }

            $main_group_in_loop = $main_group;
        }

        $SQLEG="
        SELECT expense_group_id
                ,title
        FROM expense_group
        ORDER BY expense_group_id
        ";
        $resultEG   = $db->sql_query($SQLEG);
        $rowEG = $db->sql_fetchrow($resultEG);

        $SQLESG = "
        SELECT  esg.expense_sub_group_id
               ,esg.title
        FROM expense_sub_group esg
        LEFT JOIN (expense_group eg) ON (eg.expense_group_id = esg.expense_group_id)
        ORDER BY title
        ";

        $SQLEG = "";
        $SQLESG = "";
        $session_expense_id_chk = isset($_SESSION['expense_id']) ? $_SESSION['expense_id']  : '';

        if($session_expense_id_chk == ''){
            $SQLId ="
            SELECT MAX(expense_id) AS expenseId
            FROM expense
            ";
            $resultId  = $db->sql_query($SQLId);
            $rowId = $db->sql_fetchrow($resultId);
            $_SESSION['expense_id'] = $rowId['expenseId'];
        }

        $session_expense_id = isset($_SESSION['expense_id']) ? $_SESSION['expense_id']  : '';
        $gst_val = '';

        if ($session_expense_id) {
            $SQL ="
            SELECT e.* FROM expense e
            WHERE expense_id > {$session_expense_id}
            ";
            $result  = $db->sql_query($SQL);
            while ($row = $db->sql_fetchrow($result)){
                if($row['type'] == 'Income'){
                    $incomeGroup = $fn->getRecordRowByID('income_group', 'income_group_id', $row['group']);
                    $incomeSubGroup = $fn->getRecordRowByID('income_sub_group', 'income_sub_group_id', $row['sub_group']);
                    $group = $incomeGroup['title'];
                    $subGroup = $incomeSubGroup['title'];
                } else {
                    $expenseGroup = $fn->getRecordRowByID('expense_group', 'expense_group_id', $row['group']);
                    $expenseSubGroup = $fn->getRecordRowByID('expense_sub_group', 'expense_sub_group_id', $row['sub_group']);
                    $group = $expenseGroup['title'];
                    $subGroup = $expenseSubGroup['title'];
                }

                if($row['gst'] == 1) {
                    $gst_amount = round(($row['amount']*$cpCfg['cp.gstPercentage'])/100, 2);
                    $total = number_format($row['amount'] + $row['service_charge'] + $gst_amount, 2);
                } else {
                    $total = number_format($row['amount'] + $row['service_charge'], 2);
                }

                $amount = number_format($row['amount'], 2);
                $service_charge = number_format($row['service_charge'], 2);
                $gst = ($row['gst'] == 1 ? 'Yes' : 'No');
                $date = $dateUtil->formatDate($row['date'], 'DD-MM-YYYY');

                $receiptBtn = '';
                if ($row['payment_status'] != 'Paid') {
                    $formActionReceipt = "index.php?module=payroll_expense&_spAction=generatePaymentForm&expense_id={$row['expense_id']}&showHTML=0";
                    $receiptBtn = "
                    <div class='createPayment'>
                        <a href='{$formActionReceipt}' class='btn btn-warning generatePaymentNew' id='generatePayment'>Create Payment</a>
                    </div>
                    ";
                }

                $recCount = $fn->getRecordCount('payment', "record_id = '{$row['expense_id']}'");
                $viewBtn = '';
                if ($recCount > 0) {
                    $formActionView = "index.php?module=payroll_expense&_spAction=paymentPortalDisplay&expense_id={$row['expense_id']}&showHTML=0";
                    $viewBtn = "
                    <div class='createPayment'>
                        <a href='{$formActionView}' class='mt5 btn btn-info viewPayment' id='viewPayment'>View Payment</a>
                    </div>
                    ";
                }

                $rows .="
                <tr>
                    <td>{$date}</td>
                    <td>{$group}</td>
                    <td>{$subGroup}</td>
                    <td align='right'>{$amount}</td>
                    <td align='right'>{$service_charge}</td>
                    <td align='right'>{$gst}</td>
                    <td align='right'>{$total}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['payment_status']}</td>
                    <td>{$receiptBtn} {$viewBtn}</td>
                </tr>
                ";
            }
            $gst_val = $row['gst'];
        }
        $expNoEdit  = array('isEditable' => 0);
        $expDate = array('maxDate' => date('Y-m-d'), 'yearStart' => '2019');
                    /*<div class='col-md-2 group'>{$formObj->getDDRowBySQL('Head', 'group', $SQLEG)}</div>*/

        $text = "
        <form id='frmNew' class='yform columnar cpJqForm' action='{$formActionAddexpense}' method='post'>
            <div class='floatbox'>
                <div class='float_right'>
                    <a href='{$new_List}' class='btn btn-warning'>Back to list</a>
                </div>
                <div class='float_right createPatientButtonPatientVisit'>
                <input class='btn btn-info createExpenseSaveButton' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='float_left expTitle'>New Accounts</div>
            </div>

            <div class='floatbox ml10 pr15'>
                <div class='row'>
                    <div class='col-md-2'>{$formObj->getDateRow('Date', 'date', $current_date, $expDate)}</div>
                    <!--<div class='col-md-2'>{$formObj->getDDRowByArr('Type', 'type', $typeArr)}</div>-->

                    <div class='col-md-2 group'>
                        <div class='type-select ym-fbox-select row_group'>
                            <label for='fld_group'>Head</label>
                            <select name='group' id='fld_group'>
                            <option value=''>Please Select</option>
                            {$rowsTypeDropDown}
                            </select>
                        </div>
                    </div>

                    <div class='col-md-2 subGroup'>{$formObj->getDDRowBySQL('Sub Head', 'sub_group', $SQLESG)}</div>
                    <div class='col-md-2'>{$formObj->getTBRow('Amount before GST', 'amount')}</div>                
                    <div class='col-md-2'>
                        {$formObj->getTBRow('Service Charge', 'service_charge')}
                    </div>
                    <div class='col-md-2'>{$formObj->getYesNoRRow('GST', 'gst', 1)}</div>
                </div>

                <div class='row'>
                    <div class='col-md-2'>{$formObj->getTBRow('Total Amount', 'total_amount','', $expNoEdit)}</div>
                    <div class='col-md-2'>{$formObj->getTARow('Description', 'description')}</div>
                    <div class='col-md-2'>{$formObj->getTBRow('Invoice No.', 'invoice_code')}</div>
                    <div class='col-md-2'>{$formObj->getYesNoRRow('New Company', 'new_company', 1)}</div>
                    <div class='col-md-2 newCompany'>{$formObj->getTBRow('Company Name', 'new_company_name')}</div>
                    <div class='col-md-2 existingCompany displayNone'>{$formObj->getTBRow('Company Name', 'existing_company_name')}</div>
                </div>
            </div>

            <input type='hidden' name='callbackAfterSuccess' value='cpm.payroll.expense.reloadNewListDisplay' />
        </form>

        <table class='thinlist expenseTable'>
            <tr>
                <th>Date</th>
                <th>Head</th>
                <th>Sub Head</th>
                <th class='txtRight'>Amount before GST</th>
                <th class='txtRight'>Service Charge</th>
                <th class='txtRight'>GST</th>
                <th class='txtRight'>Total Amount</th>
                <th>Description</th>
                <th>Status</th>
                <th></th>
            </tr>
            {$rows}
        </table>
        ";

                    /*<div class='col-md-2'>{$formObj->getDDRowByArr('Status', 'payment_status', $statusArr)}</div>
                    <div class='col-md-2'>{$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}</div>
                <div class='row'>
                    <div class='col-md-2'>{$formObj->getTBRow('Cheque No.', 'cheque_no', '', $hidemeArr)}</div>
                    <div class='col-md-2'>{$formObj->getDateRow('Cheque Issued Date', 'issued_date', '', $expDateHideArr)}</div>
                    <div class='col-md-2'>{$formObj->getDDRowByArr('Bank', 'bank', $bankArr, '', $hidemeArr)}</div>
                    <div class='col-md-2'>{$formObj->getDateRow('Payment Cleared Date', 'payment_cleared_date', '', $expDateHideArr)}</div>
                </div>*/

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $formObj->mode = $tv['action'];
        $expVL = array('sqlType' => 'OneField');
        $expenseSource   = $fn->getValueListSQL('expenseSource', 'sort_order');
        $typeArr = array('Income', 'Expense');
        $expNoEdit  = array('isEditable' => 0);
        $statusArr = array('Due', 'Partial Payment', 'Paid', 'On-hold', 'Cancelled');
        $bankArr = array('DBS', 'OCBC', 'UOB');

        $expFromPONoEdit = array();
        $expDate = array('maxDate' => date('Y-m-d'), 'yearStart' => '2019');
        $gstField = $formObj->getYesNoRRow('GST', 'gst', $row['gst']);
        if ($row['from_purchase_order'] == 1) {
            $expFromPONoEdit = array('isEditable' => 0);
            $expDate = array('isEditable' => 0);

            $gstValue = ($row['gst'] == 1 ? 'Yes' : 'No');
            $gstField = "
            <div class='type-text ym-fbox-text row_date'>
                <label for='fld_date'>GST</label>                
                <div class='txt ml0'>{$gstValue}</div>
            </div>
            ";
        }

        if ($row['type'] == 'Expense' && $row['group'] == '2' && $row['sub_group'] == '12') {
            $serviceChargeShowHide = array();
        } else {
            $serviceChargeShowHide = array('rowCls' => 'hideme');
        }

        $sqlCompany="
        SELECT company_id
              ,company_name
        FROM company
        ORDER BY company_name
        ";

        $sqlSupplier="
        SELECT supplier_id
              ,company_name
        FROM supplier
        ORDER BY company_name
        ";

        $appendSql  = "";
        $appendSql2 = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($row['group'] != ""){
                $appendSql2 = "AND esg.site_id = {$cpSiteIdSession}";
            }
            else{
                $appendSql2 = "WHERE esg.site_id = {$cpSiteIdSession}";
            }
        }

        $SQLEG="
        SELECT expense_group_id
                ,title
        FROM expense_group
        ORDER BY expense_group_id
        ";
        $resultEG   = $db->sql_query($SQLEG);
        $rowEG = $db->sql_fetchrow($resultEG);

        $expense_group_id = $fn->getReqParam('expense_group_id');

        $appendGroup = "";
        $SQLESG = "";
        if($row['group'] != ""){
            $appendGroup = "WHERE eg.expense_group_id = '{$row['group']}'";

            $SQLESG = "
            SELECT  esg.expense_sub_group_id
                   ,esg.title
            FROM expense_sub_group esg
            LEFT JOIN (expense_group eg) ON (eg.expense_group_id = esg.expense_group_id)
            {$appendGroup}
            {$appendSql2}
            ORDER BY title
            ";
        }

        $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y H:i:s');

        $siteNameHidden = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $sqlSite = "
            SELECT site_id
                  ,title
            FROM site
            WHERE site_id = '{$cpSiteIdSession}'
            ";
            $resultSite = $db->sql_query($sqlSite);
            $rowSite    = $db->sql_fetchrow($resultSite);

            $siteNameHidden = "
            <input name='site_name' value='{$rowSite['title']}' id='fld_site_name_hidden' />
            ";
        }

        if($row['gst'] == 1){
            $gst_amount = round(($row['amount']*$cpCfg['cp.gstPercentage'])/100, 2);
            $totalAmount = number_format($row['amount'] + $row['service_charge'] + $gst_amount, 2);
        } else {
            $totalAmount = number_format($row['amount'] + $row['service_charge'], 2);
        }

        $receiptBtn = '';
        $receiptDisplay = '';
        if ($row['payment_status'] != 'Paid') {
            $formActionReceipt = "index.php?module=payroll_expense&_spAction=generatePaymentForm&expense_id={$row['expense_id']}&showHTML=0";

            $receiptBtn = "
            <div class='createPayment'>
                <a href='{$formActionReceipt}' class='btn btn-warning generatePayment' id='generatePayment'>Create Payment</a>
            </div>
            ";
        }
        $receiptDisplay = $this->getPaymentPortalDisplay($row['expense_id']);

        if($row['type'] == 'Expense'){
            $expenseGroup = $fn->getRecordRowByID('expense_group', 'expense_group_id', $row['group']);
            $expenseSubGroup = $fn->getRecordRowByID('expense_sub_group', 'expense_sub_group_id', $row['sub_group']);
            $group = $expenseGroup['title'];
            $subGroup = $expenseSubGroup['title'];

            $IncomeExpense = "
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Expense Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                </div>
            </div>
            <div class='floatbox'>
                <div class='row'>
                    <div class='col-md-2'>{$formObj->getDateRow('Date', 'date', $row['date'], $expDate)}</div>
                    <div class='col-md-2 group'>{$formObj->getTBRow('Head', 'group', $group, $expNoEdit)}</div>
                    <div class='col-md-2'>{$formObj->getTBRow('Sub Head', 'sub_group', $subGroup, $expNoEdit)}</div>
                    <div class='col-md-2'>{$formObj->getTBRow('Amount before GST', 'amount', $row['amount'], $expFromPONoEdit)}</div>
                    <div class='col-md-2'>{$formObj->getTBRow('Service Charge', 'service_charge', $row['service_charge'], $expFromPONoEdit)}</div>
                    <div class='col-md-2'>{$gstField}</div>
                </div>

                <div class='row'>
                    <div class='col-md-2'>{$formObj->getTBRow('Total Amount', 'total_amount', $totalAmount, $expNoEdit)}</div>
                    <div class='col-md-2'>{$formObj->getTARow('Description', 'description', $row['description'])}</div>
                    <div class='col-md-2'>{$formObj->getTBRow('Invoice No.', 'invoice_code', $row['invoice_code'], $expFromPONoEdit)}</div>
                    <div class='col-md-2'>{$formObj->getTBRow('Company Name', 'company_id', $row['company_name'], $expNoEdit)}</div>
                    <div class='col-md-2'>{$formObj->getDDRowByArr('Status', 'payment_status', $statusArr, $row['payment_status'], $expNoEdit)}</div>
                </div>

                <input type='hidden' name='type' value='{$row['type']}' id='fld_type' />
                <input type='hidden' name='group' value='{$row['group']}' />
                <input type='hidden' name='sub_group' value='{$row['sub_group']}' id='fld_type' />
            </div>";
        }
               /* <div class='row'>
                    <div class='col-md-2'>{$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType', $row['mode_of_payment'])}</div>
                    <div class='col-md-2'>{$formObj->getTBRow('Cheque No.', 'cheque_no', $row['cheque_no'])}</div>
                    <div class='col-md-2'>{$formObj->getDateRow('Cheque Issued Date', 'issued_date', $row['issued_date'], $expDate)}</div>
                    <div class='col-md-2'>{$formObj->getDDRowByArr('Bank', 'bank', $bankArr, $row['bank'])}</div>
                    <div class='col-md-2'>{$formObj->getDateRow('Payment Cleared Date', 'payment_cleared_date', $row['payment_cleared_date'], $expDate)}</div>
                </div>*/

        $text = "
        <div class='linkPortalWrapper'>
            {$IncomeExpense}
        </div>
        {$receiptBtn}
        {$receiptDisplay}
        ";

        return $text;
    }   

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $db = Zend_Registry::get('db');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');
        $text = '';

        $record_id = $fn->getIssetParam($row, 'expense_id');

        $text .="
        {$comment->getView(array(
             'roomName' => 'tradingsg_expense'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln      = Zend_Registry::get('ln');

        $year            = $fn->getReqParam('year');
        $date1           = $fn->getReqParam('expense_date_1');
        $date2           = $fn->getReqParam('expense_date_2');
        $type            = $fn->getReqParam('type');
        $group           = $fn->getReqParam('group');
        $sub_group       = $fn->getReqParam('sub_group');
        $current_month   = $fn->getReqParam('current_month');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $source          = $fn->getReqParam('source');

        if ($year == '') {
            $year = date('Y');
        }

        //$sqlgroup = $fn->getValueListSQL('group');
        $typeArr = array('Expense');

        $sqlgroup = "";
        if($type == "Expense"){
            $sqlgroup = "
            SELECT expense_group_id 
                  ,title
            FROM expense_group
            ";
        }

        if($type == "Income"){
            $sqlgroup = "
            SELECT income_group_id 
                  ,title
            FROM income_group
            ";
        }

        $sqlsubgroup = "";
        if($group != "" && $type == "Expense"){
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND esg.site_id = {$cpSiteIdSession}";
            }

            $appendGroup = "WHERE eg.expense_group_id = '{$group}'";

            $sqlsubgroup = "
            SELECT  esg.expense_sub_group_id
                   ,esg.title
            FROM expense_sub_group esg
            LEFT JOIN (expense_group eg) ON (eg.expense_group_id = esg.expense_group_id)
            {$appendGroup}
            {$appendSql}
            ";
        }

        if($group != "" && $type == "Income"){
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND isg.site_id = {$cpSiteIdSession}";
            }

            $appendGroup1 = "WHERE ig.income_group_id = '{$group}'";

            $sqlsubgroup = "
            SELECT  isg.income_sub_group_id
                   ,isg.title
            FROM income_sub_group isg
            LEFT JOIN (income_group ig) ON (ig.income_group_id = isg.income_group_id)
            {$appendGroup1}
            {$appendSql}
            ";
        }

        $currentMonthArray = array (
            '01' => 'January'
           ,'02' => 'February'
           ,'03' => 'March'
           ,'04' => 'April'
           ,'05' => 'May'
           ,'06' => 'June'
           ,'07' => 'July'
           ,'08' => 'August'
           ,'09' => 'September'
           ,'10' => 'October'
           ,'11' => 'November'
           ,'12' => 'December'
        );

        if($current_month == ""){
            $current_month = date('m');
        }
        $sqlSource   = $fn->getValueListSQL('expenseSource', 'sort_order');

        $text = "
        <td>
            <select name='group'>
                <option value=''>Head</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlgroup, $group)}
           </select>
        </td>  
        <td>
            <select name='sub_group'>
                <option value=''>Sub Head</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlsubgroup, $sub_group)}
           </select>
        </td>       
        <td>
            {$formObj->getDateRangeRow('Date:', 'expense_date', $date1, $date2)}
        </td>
        ";

        /*
        <td>
            <select name='source'>
                <option value=''>Source</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlSource, $source)}
            </select>
        </td>
        */

        return $text;
    }
    /**
     *
     */
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name = $fn->getReqParam('valuelist_name');
        $expense_id    = $fn->getReqParam('expense_id');

        $formAction = "index.php?_topRm={$tv['topRm']}&module=tradingsg_expense&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='expense_id' value='{$expense_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getReceiptPortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $rows = "";
        $links= "";
        $sqlAppend = '';
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'record_id', $row['expense_id']);

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.record_id = {$row['expense_id']}
        ORDER BY r.receipt_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {

            $urlPrint = "index.php?_topRm=accounts&module=payroll_expense&_spAction=printReceipt&receipt_id={$rowReceipt['receipt_id']}&record_id={$row['expense_id']}&showHTML=0";

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' receipt_id='{$rowReceipt['receipt_id']}'>Cancel Receipt</a>";
            }
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $cancelReceiptLink = "Cancelled";
            }

            $receipt_amount = number_format($rowReceipt['amount'], 2);
            $editURL = "index.php?_topRm=main&module=payroll_expense&_spAction=editReceiptForm&showHTML=0&receipt_id={$rowReceipt['receipt_id']}";
            $editPaymentLink = "<a href='{$editURL}' class='editReceipt' id='editReceipt'><u>Edit</u></a>";

            $rows .= "
            <tr>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$receipt_amount}</td>
                <td>{$rowReceipt['remarks']}</td>
                <td class='txtCenter'>{$editPaymentLink}</td>
                <!--<td class='txtCenter'>{$cancelReceiptLink}</td>-->
            </tr>
            ";
            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Receipt Date</th>
            <th>Mode of Payment</th>
            <th class='txtRight'>Receipt Amount</th>
            <th>Notes</th>
            <th class='txtCenter'>Edit</th>
            <!--<th class='txtCenter'>Cancel</th>-->
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=pms_order&_spAction=generateRefundForm&showHTML=0&record_id={$row['expense_id']}&receipt_id={$receiptRec['receipt_id']}";

        $text = "
        <h2>Receipt(s)</h2>
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper pms_company__pms_orderLink'>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                </table>
                <input type='hidden' name='record_id' value='{$row['expense_id']}' />
                <input type='hidden' name='receipt_id' value='{$receiptRec['receipt_id']}' />
            </div>
        </td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getGenerateReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $expense_id = $fn->getReqParam('expense_id');
        $row = $fn->getRecordRowByID('expense', 'expense_id', $expense_id);

        $rows = '';
        $today = date('Y-m-d');

        if($row['gst'] == 1){
            $gst_amount = round(($row['amount']*$row['gst_percentage'])/100, 2);
            /*
            $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1));
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gst_amount);
                $fraction = substr($fraction, 0, 2);
                $gst_amount = $integer . "." . $fraction;
            }
            */

            $totalAmount = number_format($row['amount'] + $gst_amount, 2);
        } else {
            $totalAmount = number_format($row['amount'], 2);
        }

        $sqlReceipt = "
        SELECT SUM(amount) AS total_amount_paid FROM receipt
        WHERE record_id = '{$expense_id}'
          AND receipt_status = 'Paid'
        ";
        $resultReceipt = $db->sql_query($sqlReceipt);
        $rowReceipt = $db->sql_fetchrow($resultReceipt);
        $total_amount_paid = $rowReceipt['total_amount_paid'];

        $totalAmountUnFormatted = str_replace( ',', '', $totalAmount);
        $balance_amount_noformat = $totalAmountUnFormatted - $total_amount_paid;
        $balance_amount_payable = number_format($totalAmountUnFormatted - $total_amount_paid, 2);

        $formAction = "index.php?_topRm=accounts&module=payroll_expense&_spAction=generateReceiptFormSubmit&showHTML=0";

        $date = date("Y-m-d");
        $expNumber = array('fldType' => 'number');
        $expDate = array('minDate' => $row['invoice_date'], 'maxDate' => date('Y-m-d'));
        $bankArr = array('DBS', 'OCBC', 'UOB');

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount1', 'receipt_amount', $balance_amount_payable, $expNumber)}
            <div>{$formObj->getDateRow('Date', 'receipt_date', $date, $expDate)}</div>
            {$formObj->getTextAreaRow('Notes', 'remarks')}
            {$formObj->getDDRowByArr('Bank', 'bank_name', $bankArr)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque Date', 'cheque_date', '', array('rowCls' => 'hideme','minDate' => $row['invoice_date'], 'maxDate' => date('Y-m-d')))}
            <input type='hidden' name='expense_id' value='{$expense_id}' />
            <input type='hidden' name='max_receipt_amount' value='{$balance_amount_noformat}' />
            <input type='hidden' name='expense_amount' value='{$totalAmountUnFormatted}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPaymentPortalDisplay($expense_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        if($expense_id == ''){
            $expense_id = $fn->getReqParam('expense_id');
        }

        $rows = "";
        $links= "";
        $sqlAppend = '';
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('payment', 'record_id', $expense_id);

        $SQL = "
        SELECT r.*
        FROM payment r
        WHERE r.record_id = {$expense_id}
        ORDER BY r.payment_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {

            $urlPrint = "index.php?_topRm=accounts&module=payroll_expense&_spAction=printReceipt&payment_id={$rowReceipt['payment_id']}&record_id={$expense_id}&showHTML=0";

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $cancelReceiptLink = '';
            if ($rowReceipt['payment_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' payment_id='{$rowReceipt['payment_id']}'>Cancel Receipt</a>";
            }
            if ($rowReceipt['payment_status'] == 'Cancelled') {
                $cancelReceiptLink = "Cancelled";
            }

            $rows .= "
            <tr>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$rowReceipt['amount']}</td>
                <td>{$rowReceipt['remarks']}</td>
                <!--<td>{$cancelReceiptLink}</td>-->
            </tr>
            ";
            if($rowReceipt['payment_status'] == 'Paid'){
                $total += $rowReceipt['amount'];
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Payment Date</th>
            <th>Mode of Payment</th>
            <th class='txtRight'>Payment Amount</th>
            <th>Notes</th>
            <!--<th>Cancel</th>-->
        </tr>
        ";

        $formAction = "";

        $text = "
        <h2>Payment(s)</h2>
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper pms_company__pms_orderLink'>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                </table>
                <input type='hidden' name='record_id' value='{$expense_id}' />
                <input type='hidden' name='payment_id' value='{$receiptRec['payment_id']}' />
            </div>
        </td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getGeneratePaymentForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $expense_id = $fn->getReqParam('expense_id');
        $row = $fn->getRecordRowByID('expense', 'expense_id', $expense_id);

        $rows = '';
        $today = date('Y-m-d');

        if($row['gst'] == 1){
            $gst_amount = round(($row['amount']*$row['gst_percentage'])/100, 2);
            /*
            $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1));
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gst_amount);
                $fraction = substr($fraction, 0, 2);
                $gst_amount = $integer . "." . $fraction;
            }
            */

            $totalAmount = number_format($row['amount'] + $gst_amount + $row['service_charge'], 2);
        } else {
            $totalAmount = number_format($row['amount'] + $row['service_charge'], 2);
        }

        $sqlReceipt = "
        SELECT SUM(amount) AS total_amount_paid FROM payment
        WHERE record_id = '{$expense_id}'
          AND payment_status = 'Paid'
        ";
        $resultReceipt = $db->sql_query($sqlReceipt);
        $rowReceipt = $db->sql_fetchrow($resultReceipt);
        $total_amount_paid = $rowReceipt['total_amount_paid'];

        $totalAmountUnFormatted = str_replace( ',', '', $totalAmount);
        $balance_amount_noformat = $totalAmountUnFormatted - $total_amount_paid;
        $balance_amount_payable = number_format($totalAmountUnFormatted - $total_amount_paid, 2);

        $formAction = "index.php?_topRm=accounts&module=payroll_expense&_spAction=generatePaymentFormSubmit&showHTML=0";

        $date = date("Y-m-d");
        $expNumber = array('fldType' => 'number');
        $expDate = array('minDate' => $row['date'], 'maxDate' => date('Y-m-d'));
        $bankArr = array('DBS', 'OCBC', 'UOB');

        $text = "
        <form id='paymentPortalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'receipt_amount', $balance_amount_payable, $expNumber)}
            {$formObj->getDateRow('Date', 'receipt_date', $date, $expDate)}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment', 'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque Date', 'cheque_date', '', array('rowCls' => 'hideme','minDate' => $row['invoice_date'], 'maxDate' => date('Y-m-d')))}
            {$formObj->getDDRowByArr('Bank', 'bank_name', $bankArr, '', array('rowCls' => 'hideme'))}
            <input type='hidden' name='expense_id' value='{$expense_id}' />
            <input type='hidden' name='max_receipt_amount' value='{$balance_amount_noformat}' />
            <input type='hidden' name='expense_amount' value='{$totalAmountUnFormatted}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $receipt_id = $fn->getReqParam('receipt_id');
        $row = $fn->getRecordRowByID('receipt', 'receipt_id', $receipt_id);
        $rowExp = $fn->getRecordRowByID('expense', 'expense_id', $row['record_id']);

        $rows = '';
        $bankArr = array('DBS', 'OCBC', 'UOB');

        $formAction = "index.php?_topRm=accounts&module=payroll_expense&_spAction=editReceiptFormSubmit&showHTML=0";
        $expDate = array('minDate' => $rowExp['invoice_date'], 'maxDate' => date('Y-m-d'));

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Date', 'receipt_date', $row['date'], $expDate)}
            {$formObj->getTextAreaRow('Notes', 'remarks', $row['remarks'])}
            {$formObj->getDDRowByArr('Bank', 'bank_name', $bankArr, $row['bank_name'])}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType', $row['mode_of_payment'])}
            <input type='hidden' name='receipt_id' value='{$receipt_id}' />
        </form>
        ";

        return $text;
    }
}