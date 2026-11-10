<?
class CP_Admin_Modules_ManPower_Receipt_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $email = "";
        $rowCounter = 0;


        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $urlOrder = "index.php?_topRm=finance&module=manPower_order&_action=edit&record_id={$row['order_id']}";
            $orderId = "<a href='{$urlOrder}'><u>{$row['order_id']}</u></a>";

            $urlReceiptPrint = "index.php?_topRm=finance&module=manPower_order&_spAction=printReceipt&receipt_code={$row['receipt_code']}&order_id={$row['order_id']}&showHTML=0";
            $printReceiptRecord = "<a target ='_blank' href='{$urlReceiptPrint}'>Print PDF</a>";

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['receipt_code'])}
            {$listObj->getListDataCell($row['amount'], 'right')}
            {$listObj->getListDataCell($row['receipt_type'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['mode_of_payment'])}
            {$listObj->getListDataCell($row['receipt_status'])}
            {$listObj->getListDateCell($row['date'])}
            {$listObj->getListDataCell($orderId, 'center')}
            {$listObj->getListDataCell($row['receipt_id'], 'center')}
            {$listObj->getListDataCell($printReceiptRecord)}
            {$listObj->getListRowEnd($row['receipt_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Receipt Code', 'r.receipt_code')}
        {$listObj->getListHeaderCell('Amount', 'r.amount', 'headerRight')}
        {$listObj->getListHeaderCell('Type', 'receipt_type')}
        {$listObj->getListHeaderCell('Client Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Mode of Payment', 'r.mode_of_payment')}
        {$listObj->getListHeaderCell('Status', 'r.receipt_status')}
        {$listObj->getListHeaderCell('Date', 'r.date')}
        {$listObj->getListHeaderCell('Order ID', 'order_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Receipt ID', 'receipt_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Print', '')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');

        $expNoEdit = array('isEditable' => 0);

        if ($row['contact_name']){
            $contact = $formObj->getTBRow('Contact Name', 'contact_name', $row['contact_name']);
        } else {
            $contact = $formObj->getTBRow('Name', 'title', $row['title']);
        }

        $fielset1 = "
        {$contact}
        {$formObj->getTBRow('Receipt Code', 'receipt_code', $row['receipt_code'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getTBRow('Mode of Payment', 'mode_of_payment', $row['mode_of_payment'])}
        {$formObj->getTBRow('Status', 'receipt_status', $row['receipt_status'])}
        {$formObj->getDateRow('Receipt Date', 'date', $row['date'])}
        {$formObj->getTBRow('Order ID', 'order_id', $row['order_id'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Receipt Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'pms_receipt', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $receipt_date1 = $fn->getReqParam('receipt_date1');
        $receipt_date2 = $fn->getReqParam('receipt_date2');
        $mode_of_payment = $fn->getReqParam('mode_of_payment');
        $receipt_status  = $fn->getReqParam('receipt_status');

        $yearEnd = date('Y') + 10;

        $paymentType = $fn->getValueListSQL('paymentType');
        $receiptStatus = array('Paid'
                            ,'Cancelled');

        $text = "
        <td class='dateRange'>
            Receipt Date:
            <input type='text' allowEdit='1' name='receipt_date1' class='fld_date'
                   id='fld_receipt_date1' value='{$receipt_date1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='receipt_date2' class='fld_date'
                   id='fld_receipt_date2' value='{$receipt_date2}' yearEnd='{$yearEnd}' />
        </td>

        <td>
            <select name='mode_of_payment'>
                <option value=''>Mode of Payment</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $paymentType, $mode_of_payment)}
            </select>
        </td>
        <td>
            <select name='receipt_status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($receiptStatus, $receipt_status)}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
     function getGenerateReceiptFormFromInvoiceOLD() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $invoice_id= $fn->getReqParam('invoice_id');

        $rows = '';
        $expEdit = array('isEditable' => 0);
        $today = date('Y-m-d');
        $formAction = "index.php?_topRm=finance&module=manPower_receipt&_spAction=generateReceiptFormFromInvoiceSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <div class='generate_invoice'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDateRow('Receipt Date', 'receipt_date', $today)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
            <input type='hidden' name='issued_by' value='{$_SESSION['userFullName']}' />
        </form>
        ";

        return $text;
    }

     /**
     *
     */
     function getGenerateReceiptFormClient() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('order_id');

        $rows = '';
        $receipt_date  = $fn->getCurrentDate();
        $today = date('Y-m-d');
        $_SESSION['selectedInvoiceIds'] = array();

        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
            WHERE invHist.invoice_id =  i.invoice_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE i.order_id = {$order_id}
            AND i.invoice_type ='Client'
            AND (i.status = 'Due' || i.status = 'Partial Payment')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no invoice is available or all the invoices are paid" ;
        }

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']}({$row['invoice_amount']})</div>
                    <div class=''>Paid:{$row['prev_inv_amount']}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=manPower_order&_spAction=generateReceiptFormSubmitClient&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDateRow('Receipt Date', 'receipt_date', $receipt_date)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            <div class ='chequeNoDisplay'>
                {$formObj->getTBRow('Cheque No:', 'cheque_no', $row['cheque_no'])}
            </div>
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
     }

    /**
     *
     */
     function getGenerateReceiptFormCandidate() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('order_id');
        $_SESSION['selectedInvoiceIds'] = array();
        $rows = '';
        $receipt_date  = $fn->getCurrentDate();
        $today = date('Y-m-d');

        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
            WHERE invHist.invoice_id =  i.invoice_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE i.order_id = {$order_id}
            AND i.invoice_type ='Candidate'
            AND (i.status = 'Due' || i.status = 'Partial Payment')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no invoice is available or all the invoices are paid" ;
        }

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']}({$row['invoice_amount']})</div>
                    <div class=''>Paid:{$row['prev_inv_amount']}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=manPower_order&_spAction=generateReceiptFormSubmitCandidate&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDateRow('Receipt Date', 'candidate_receipt_date', $receipt_date)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            <div class ='chequeNoDisplay'>
                {$formObj->getTBRow('Cheque No:', 'cheque_no', $row['cheque_no'])}
            </div>
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
     }

    /**
     *
     */
     function getGenerateReceiptFormReferral() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('order_id');
        $_SESSION['selectedInvoiceIds'] = array();
        $rows = '';
        $receipt_date  = $fn->getCurrentDate();
        $today = date('Y-m-d');

        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
            WHERE invHist.invoice_id =  i.invoice_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE i.order_id = {$order_id}
            AND i.invoice_type ='Referral'
            AND (i.status = 'Due' || i.status = 'Partial Payment')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no invoice is available or all the invoices are paid" ;
        }

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']}({$row['invoice_amount']})</div>
                    <div class=''>Paid:{$row['prev_inv_amount']}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=manPower_order&_spAction=generateReceiptFormSubmitReferral&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDateRow('Receipt Date', 'referral_receipt_date', $receipt_date)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            <div class ='chequeNoDisplay'>
                {$formObj->getTBRow('Cheque No:', 'cheque_no', $row['cheque_no'])}
            </div>
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
     }

    /**
     *
     */
     function getEditReceiptFormFromInvoice() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $receipt_id = $fn->getReqParam('receipt_id');
        $invoice_id = $fn->getReqParam('invoice_id');

        $rowInvRecHist = $fn->getRecordRowByID('invoice_receipt_history', 'receipt_id', $receipt_id);

        $rowReceipt = $fn->getRecordRowByID('receipt', 'receipt_id', $receipt_id);
        $formAction = "index.php?_topRm=finance&module=manPower_receipt&_spAction=editReceiptFormFromInvoiceSubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);
        $receipt_code = $formObj->getTBRow('Receipt Code', 'receipt_code', $rowReceipt['receipt_code'], $expEdit);

        $receipt_date = $dateUtil->formatDate($rowReceipt['date'], 'YYYY-MM-DD');
        $cheque_date  = $dateUtil->formatDate($rowReceipt['cheque_date'], 'YYYY-MM-DD');

        if ($rowReceipt['mode_of_payment'] == 'Cheque') {
            $paymentMode = array('rowCls' => 'showme');
        } else {
            $paymentMode = array('rowCls' => 'hideme');
        }

        $text = "
        <form id='portalForm' class='yform columnar editReceiptFormForPvt receiptForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'amount', $rowReceipt['amount'])}
            {$receipt_code}
            {$formObj->getDateRow('Receipt Date', 'date', $receipt_date)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment', 'paymentType', $rowReceipt['mode_of_payment'])}
            {$formObj->getTBRow('Cheque No', 'cheque_no', $rowReceipt['cheque_no'], $paymentMode)}
            {$formObj->getDateRow('Cheque date', 'cheque_date', $cheque_date, $paymentMode)}
            {$formObj->getTBRow('Bank', 'bank_name', $rowReceipt['bank_name'], $paymentMode)}
            {$formObj->getTBRow('Issued By', 'issued_by', $_SESSION['userFullName'], $expEdit)}
            {$formObj->getTextAreaRow('Note', 'remarks', $rowReceipt['remarks'])}
            <input type='hidden' name='receipt_id' value='{$receipt_id}' />
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getGenerateReceiptFormEmployerTax() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $order_id   = $fn->getReqParam('order_id');
        $invoice_id = $fn->getReqParam('invoice_id');

        $receipt_date  = $fn->getCurrentDate();
        $today = date('Y-m-d');

        $expNoEdit = array('isEditable' => 0);

        $invoiceRow = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);

        $formAction = "index.php?_topRm=finance&module=manPower_order&_spAction=generateReceiptFormEmployerTaxSubmit&showHTML=0";

        $invoice_amount = number_format($invoiceRow['invoice_amount'],2);
        
        $text = "
        <form id='receiptFormEmployerTax' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'amount', $invoice_amount)}
            {$formObj->getDateRow('Receipt Date', 'tax_receipt_date', $receipt_date)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType' ,'Direct Deposit')}
            <div class ='chequeNoDisplay'>
                {$formObj->getTBRow('Cheque No:', 'cheque_no', '')}
            </div>
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id'   value='{$order_id}' />
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";

        return $text;
     }

    /**
     *
     */
     function getEmployerTaxFormDetail() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $invoice_id = $fn->getReqParam('invoice_id');

        $expNoEdit  = array('isEditable' => 0);

        $receiptSQL = "
        SELECT * 
        FROM receipt
        WHERE tax_invoice_id = {$invoice_id}
        AND receipt_status != 'Cancelled'
        ";
        $resultReceipt = $db->sql_query($receiptSQL);
        $receiptRow    = $db->sql_fetchrow($resultReceipt);
        
        //$receiptRow    = $fn->getRecordRowByID('receipt', 'tax_invoice_id', $invoice_id);
        
        $cheque_no = '';
        if($receiptRow['mode_of_payment'] == 'Cheque'){
            $cheque_no = "
                {$formObj->getTBRow('Cheque No', 'cheque_no', $receiptRow['cheque_no'], $expNoEdit)}
            ";
        }

        $receipt_date = $fn->getCPDate($receiptRow['date'],'m-d-Y');

        $text = "
        <form id='receiptFormEmployerTaxdetail' class='yform columnar receiptForm' method='post'>
            <div id='updateTotal' class='button'><a href='#' class='cancelTaxReceipt' type='EmployerTax' invoice_id ='{$receiptRow['tax_invoice_id']}' receipt_code='{$receiptRow['receipt_code']}' order_id = '{$receiptRow['order_id']}'>Cancel payment</a></div>
            {$formObj->getTBRow('Amount', 'amount', '$'.$receiptRow['amount'], $expNoEdit)}
            {$formObj->getTBRow('Receipt Date', 'tax_receipt_date', $receipt_date, $expNoEdit)}
            {$formObj->getTBRow('Mode of Payment', 'mode_of_payment', $receiptRow['mode_of_payment'], $expNoEdit)}
            {$cheque_no}
            {$formObj->getTBRow('Note', 'remarks', $receiptRow['remarks'], $expNoEdit)}
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";

        return $text;
     }

    /**
     *
     */
     function getEditReceiptFormCandidate() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $order_id   = $fn->getReqParam('order_id');
        $receipt_id = $fn->getReqParam('receipt_id');

        $expEdit = array('isEditable' => 0);
        $rows = '';
        $rowReceipt = $fn->getRecordRowByID('receipt', 'receipt_id', $receipt_id);

        $SQL = "
        SELECT i.invoice_code
              ,i.invoice_amount
        FROM invoice i
        LEFT JOIN (invoice_receipt_history irh) ON (irh.invoice_id = i.invoice_id)
        WHERE irh.receipt_id = {$receipt_id}
        AND irh.amount > 0
        ";
        $result = $db->sql_query($SQL);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $invoiceAmount = number_format($row['invoice_amount'], 2);
            
            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode' disabled = '1'>
                    </div>
                    <div class='float_left'>SAL - {$row['invoice_code']} ({$invoiceAmount})</div>
                </div>
            </div>
            ";
            $count++;
        }

        $chequeClass = 'chequeNoDisplay';
        if($rowReceipt['mode_of_payment'] == 'Cheque'){
            $chequeClass = '';
        }

        $formAction = "index.php?_topRm=finance&module=manPower_order&_spAction=editCandidateReceiptFormSubmit&showHTML=0";
        $receiptAmount = number_format($rowReceipt['amount'],2);
        
        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Paid Invoice(s)</h3>
            {$rows}
            {$formObj->getTBRow('Receipt Code', 'receipt_code', $rowReceipt['receipt_code'], $expEdit)}
            {$formObj->getTBRow('Amount', 'amount', $receiptAmount, $expEdit)}
            {$formObj->getDateRow('Receipt Date', 'candidate_receipt_date', $rowReceipt['date'])}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment_edit',  'paymentType', $rowReceipt['mode_of_payment'])}
            <div class ='{$chequeClass} editReceiptCheque'>
                {$formObj->getTBRow('Cheque No:', 'cheque_no', $rowReceipt['cheque_no'])}
            </div>
            {$formObj->getTextAreaRow('Note', 'remarks', $rowReceipt['remarks'])}
            <input type='hidden' name='receipt_id' value='{$receipt_id}' />
        </form>
        ";

        return $text;
     }

}