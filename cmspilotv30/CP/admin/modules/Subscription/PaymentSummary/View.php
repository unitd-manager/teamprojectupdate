<?
class CP_Admin_Modules_Subscription_PaymentSummary_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $fn = Zend_Registry::get('fn');
        
        unset($_SESSION['selectedInvoicesForSummary']);
        $month = $fn->getReqParam('month');
        
        if ($month) {
            return $this->getListForMonth($dataArray, $month);
        }

        $rows = '';
        $repeat_name = '';

        $current_month = date('m');

        foreach ($dataArray as $row){
            $contact_name = $row['first_name'] . ' ' . $row['last_name'];

            $rows .= "
            <tr>
                <td class='mb5'>{$row['dda']}</td>
                <td class='mb5'>{$row['parent_name']}</td>
                <td class='mb5'><strong>{$contact_name}</strong></td>
                <td>{$row['branch_name']}</td>
                <td>{$row['mode_of_payment']}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 1, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 2, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 3, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 4, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 5, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 6, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 7, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 8, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 9, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 10, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 11, 0)}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], 12, 0)}</td>
                <td>
                    <a href='index.php?_topRm=finance&module=subscription_order&_action=edit&order_id={$row['order_id']}'  target=''><u>Goto Finance</u></a>
                </td>
            </tr>
            ";
        }

        $formAction = "index.php?module=subscription_giroPayment&_spAction=giroPaymentSubmit&showHTML=0";        
        $url = 'index.php?_topRm=finance&module=subscription_giroPayment&_spAction=generateDBSTxtFile&showHTML=0';

        $text = "
        {$this->getSearch()}
        <form id='frmGiro' class='yform columnar giroForm' method='post' action='{$formAction}'>
            <table class='thinlist room-giroPayment-table'>
                <tr>
                    <thead>
                        <!--<div class='actBtns'>
                            <a id='giroSubmit' class='button giroSubmit' href='index.php?module=subscription_giroPayment&_spAction=giroPaymentSubmit&showHTML=0'> 
                            <h3>Submit</h3>
                            </a>
                        </div>-->
                    </thead>
                    
                    <tbody>
                        <tr>
                            <td><strong>DDA</strong></td>
                            <td><strong>Parent Name</strong></td>
                            <td><strong>Student Name</strong></td>
                            <td><strong>Branch Name</strong></td>
                            <td><strong>Mode of Payment</strong></td>
                            <td class='txtCenter'><strong>Jan</strong></td>
                            <td class='txtCenter'><strong>Feb</strong></td>
                            <td class='txtCenter'><strong>Mar</strong></td>
                            <td class='txtCenter'><strong>Apr</strong></td>
                            <td class='txtCenter'><strong>May</strong></td>
                            <td class='txtCenter'><strong>Jun</strong></td>
                            <td class='txtCenter'><strong>Jul</strong></td>
                            <td class='txtCenter'><strong>Aug</strong></td>
                            <td class='txtCenter'><strong>Sep</strong></td>
                            <td class='txtCenter'><strong>Oct</strong></td>
                            <td class='txtCenter'><strong>Nov</strong></td>
                            <td class='txtCenter'><strong>Dec</strong></td>
                            <td></td>
                        </tr>
                        
                        {$rows}
                        
                    </tbody>
                </tr>
            </table>
            
        </form>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getListForMonth($dataArray, $month){
        $fn = Zend_Registry::get('fn');
        
        unset($_SESSION['selectedInvoicesForSummary']);
        $month = $fn->getReqParam('month');
        
        $rows = '';
        $repeat_name = '';

        switch ($month) {
            case 1: $prefix_month = 'Jan';
            break;
            case 2: $prefix_month = 'Feb';
            break;
            case 3: $prefix_month = 'Mar';
            break;
            case 4: $prefix_month = 'Apr';
            break;
            case 5: $prefix_month = 'May';
            break;
            case 6: $prefix_month = 'Jun';
            break;
            case 7: $prefix_month = 'Jul';
            break;
            case 8: $prefix_month = 'Aug';
            break;
            case 9: $prefix_month = 'Sep';
            break;
            case 10: $prefix_month = 'Oct';
            break;
            case 11: $prefix_month = 'Nov';
            break;
            case 12: $prefix_month = 'Dec';
            break;
        }
        foreach ($dataArray as $row){
            $contact_name = $row['first_name'] . ' ' . $row['last_name'];
            
            $rows .= "
            <tr>
                <td class='mb5'>{$row['dda']}</td>
                <td class='mb5'>{$row['parent_name']}</td>
                <td class='mb5'><strong>{$contact_name}</strong></td>
                <td>{$row['branch_name']}</td>
                <td>{$row['mode_of_payment']}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], $month, 0)}</td>
                <td>
                    <a href='index.php?_topRm=finance&module=subscription_order&_action=edit&order_id={$row['order_id']}'  target=''><u>Goto Finance</u></a>
                </td>
            </tr>
            ";
        }

        $formAction = "index.php?module=subscription_giroPayment&_spAction=giroPaymentSubmit&showHTML=0";
        
        $text = "
        {$this->getSearch()}
        <form id='frmGiro' class='yform columnar giroForm' method='post' action='{$formAction}'>
            <table class='thinlist room-giroPayment-table'>
                <tr>
                    <thead>
                        <!--<div class='actBtns'>
                            <a id='giroSubmit' class='button giroSubmit' href='index.php?module=subscription_giroPayment&_spAction=giroPaymentSubmit&showHTML=0'> 
                            <h3>Submit</h3>
                            </a>
                        </div>-->
                    </thead>
                    
                    <tbody>
                        <tr>
                            <td><strong>DDA</strong></td>
                            <td><strong>Parent Name</strong></td>
                            <td><strong>Student Name</strong></td>
                            <td><strong>Branch Name</strong></td>
                            <td><strong>Mode of Payment</strong></td>
                            <td class='txtCenter'><strong>{$prefix_month}</strong></td>
                            <td></td>
                        </tr>
                        
                        {$rows}
                        
                    </tbody>
                </tr>
            </table>
            
        </form>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getCheckUncheckBtn() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = "
        <a href='#' class='check-all'>
        <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'></a>
        <a href='#' class='uncheck-all'><img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'></a>
        ";
        
        return $text;
    }

    /**
     *
     */
    function getInvoiceCheckBox($contact_id, $order_id, $month, $checked_val) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $invoiceCodes = isset($_SESSION['selectedInvoicesForSummary']) ? $_SESSION['selectedInvoicesForSummary'] : 0;
        
        $SQL = "
        SELECT i.status
              ,i.invoice_code
              ,i.invoice_id
        FROM invoice i
        WHERE i.contact_id = {$contact_id}
          AND i.invoice_month = {$month}
          AND i.order_id = {$order_id}
          AND i.add_registration_fee IS NULL
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        $text = '';
        $checked = '';
        
        if ($checked_val == 1) {
            $checked = "checked='checked'";
        }
        
        if ($row['status'] == 'Due' || $row['status'] == 'Partial Payment') {
            $text = "
            <div class='float_left'>
                <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode' {$checked}'>
            </div>
            ";  
        } else if ($row['status'] == 'Paid') {
            $SQLRec = "
            SELECT r.receipt_code 
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN (invoice i) ON (i.invoice_id = irh.invoice_id)
            WHERE i.invoice_code = '{$row['invoice_code']}'
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($SQLRec);
            $rowRec = $db->sql_fetchrow($resultRec);
        
            $text = "
            <div>{$row['status']}</div> 
            ";
            
        }
        
        return $text;
    }

    /**
     *
     */
    function getSearch() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id         = $fn->getReqParam('site_id');
        $mode_of_payment = $fn->getReqParam('mode_of_payment');
        $invoice_status  = $fn->getReqParam('invoice_status');
        $userGroupType   = $fn->getSessionParam('userGroupType');
        $year            = $fn->getReqParam('year');
        $month           = $fn->getReqParam('month');
        $student_status  = $fn->getReqParam('student_status');
        
        if ($year == '') {
            $year = date('Y');
        }

        if ($student_status == '') {
            $student_status = 'Active';
        }

        $sqlYear = "
        SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
        FROM invoice i
        ";
        
        $branch = "";        
        if ($userGroupType == 'Super Administrator') {
            $modObj   = getCPModuleObj('common_site');
            $sqlSites = $modObj->model->getSiteSQL();

            $branch = "
            <td>
                <select name='site_id' class='mr10'>
                    <option value=''>{$ln->gd('m.webBasic.content.lbl.site', 'Branch')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSites, $site_id)}
                </select>
            </td>
            ";
        } else {
            $branch = "<td></td>";        
        }
        
        $sqlModeOfPayment = $fn->getValueListSQL('paymentType');
        $sqlStudentStatus = $fn->getValueListSQL('studentStatus');

        $arr = array (
                '0'  => 'Select Month'
               ,'01' => 'January'
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

        $rows = "
        {$branch}

        <td>
            <select name='mode_of_payment' class='mr10'>
                <option value=''>Mode of Payment</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlModeOfPayment, $mode_of_payment)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='invoice_status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.ecommerce.order.statusArr'], $invoice_status)}
            </select>
        </td>

        <td class='pl10'>
            Search by DDA or Parent / Student Name: <input class='w200' name='keyword' value='' />
        </td>
        ";
        
        $action = "index.php?_topRm=finance&module=pms_paymentSummary&_action=list";
        $reportLink = "index.php?module=pms_order&_spAction=printSelectedInvoices&showHTML=0";
        $receiptLink = "index.php?_topRm=finance&module=pms_paymentSummary&_spAction=makePaymentForParentForm&showHTML=0";
        
        $text = "
        <form id='paymentSearch' class='yform columnar' method='post' action='{$action}'>
        <table class='search'>
            <tr>
                {$rows}
                <td>
                    <input type='submit' value='Search' class='button ml10'>
                </td>

                <td>
                    <a href='{$reportLink}' class='button mt10 ml10' target='_blank'>Print Group Invoices</a>
                </td>
            </tr>
            
            <tr>

                <td>
                    <select name='year' class='yearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
        
                <td>
                    <select name='month' class='ml10 mr10'>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>

                <td>
                    <select name='student_status'>
                        <option value=''>Status</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlStudentStatus, $student_status)}
                    </select>
                </td>

                <td colspan='2'></td>
                <td>
                    <button href='{$receiptLink}' id='generateReceiptForParent' class='button mt10 ml10'>Print Group Receipts</button>
                </td>

            </tr>
            
        </table>
        </form>
        <script>
        </script>
        ";

        return $text;
    }

    /**
     *
     */
    function getSetInvoiceCodeForSession(){
        $fn = Zend_Registry::get('fn');

        $invoice_code   = $fn->getReqParam('invoice_code'); 
        $is_checked     = $fn->getReqParam('is_checked');
        
        $s = &$_SESSION['selectedInvoicesForSummary'];
        
        $arryKey = array_search($invoice_code, $s);
        
        if ($is_checked == 'false'){
            unset($s[$arryKey]);
        } else {
            if (!in_array($invoice_code, $s)){
                $_SESSION['selectedInvoicesForSummary'][] = $invoice_code;
            }
        }

        print_r($_SESSION['selectedInvoicesForSummary']);
    }

    /**
     *
     */
     function getMakePaymentForParentForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
               
        $invoiceCodes = isset($_SESSION['selectedInvoicesForSummary']) ? $_SESSION['selectedInvoicesForSummary'] : 0;
        $count = count($invoiceCodes);

        if ($invoiceCodes == 0) {
			return "Please select the invoice";
        }

        $invoiceCodes   = join(',', $invoiceCodes);
        $sessionExplode = explode(',', $invoiceCodes);
        
        $counter = 1;
        $count   = count($sessionExplode);
        
        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }

        $SQL = "
        SELECT DISTINCT p.parent_id
        FROM parent p
        LEFT JOIN (parent_contact pc) ON (p.parent_id   = pc.parent_id)
        JOIN (invoice i)              ON (pc.contact_id = i.contact_id)
        WHERE i.invoice_code IN ($invoice_code)
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);        
        if ($numRows > 1) {
			return "Please select one parent";
        }
        
        $SQLInv = "
        SELECT SUM(i.invoice_amount) AS total_amount
        FROM invoice i
        WHERE i.invoice_code IN ($invoice_code)
        ";
        $resultInv = $db->sql_query($SQLInv);
        $rowInv    = $db->sql_fetchrow($resultInv);

        $SQLDiscount = "
        SELECT SUM(discount_amount) AS discount_selected_sum
        FROM invoice
        WHERE invoice_code IN ({$invoice_code})
        ";
        $resultDiscount = $db->sql_query($SQLDiscount);
        $rowDiscount    = $db->sql_fetchrow($resultDiscount);
        
        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        WHERE i.invoice_code IN ({$invoice_code})
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

        $amount_to_be_paid = $rowInv['total_amount']- $rowDiscount['discount_selected_sum'] - $rowPartialPayment['invoice_partial_payment'];

        /* Finding max discount amount for selected invoices */
        $SQLDiscAmt = "
        SELECT MAX(i.discount_amount) AS max_discount_amount
        FROM invoice i
        WHERE i.invoice_code IN ({$invoice_code})
        ";
        $resultDiscAmt = $db->sql_query($SQLDiscAmt);
        $rowDiscAmt    = $db->sql_fetchrow($resultDiscAmt);

        $current_date = date('Y-m-d');
        $formAction = "index.php?_topRm=finance&module=subscription_paymentSummary&_spAction=generateReceiptForParentFormSubmit&showHTML=0";
        $expEdit = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar receiptFormForParent' name='receiptFormForParent' method='post' action='{$formAction}'>
            <div class='float_box clearfix'>
                <div class='float_left'>
                    <a href='#' class='populateAmountPayable'><u>Check amount Payable</u></a>
                </div>
                <div id='totalAmountPayable' class='ml10 mt10'></div>
            </div>
            {$formObj->getTBRow('Amount paying now', 'amount', $amount_to_be_paid)}
            <div>NOTE: Discount amount populated is for one invoice</div>
            {$formObj->getTBRow('Discount Amount', 'discount_amount', $rowDiscAmt['max_discount_amount'])}
            <div class='floatbox'>
                <div class='float_left'>Update discount for all future months</div>
                <div class='ml10'>
                    <input type='checkbox' name='discount_for_all_months' value='1' checked='checked'>
                </div>
            </div>
            {$formObj->getDateRow('Receipt date', 'date', $current_date)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTextAreaRow('Remarks', 'remarks')}
        </form>
        ";

        return $text;
    }
}