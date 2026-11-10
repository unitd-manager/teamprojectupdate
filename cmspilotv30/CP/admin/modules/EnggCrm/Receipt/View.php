<?
class CP_Admin_Modules_EnggCrm_Receipt_View extends CP_Common_Lib_ModuleViewAbstract
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
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['receipt_code'])}
            {$listObj->getListDataCell($row['amount'], 'right')}
            {$listObj->getListDataCell($row['mode_of_payment'])}
            {$listObj->getListDataCell($row['receipt_status'])}
            {$listObj->getListDateCell($row['date'])}
            {$listObj->getListDataCell($row['invoice_id'], 'center')}
            {$listObj->getListDataCell($row['receipt_id'], 'center')}
            {$listObj->getListRowEnd($row['receipt_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Receipt Code', 'r.receipt_code')}
        {$listObj->getListHeaderCell('Amount', 'r.amount', 'headerRight')}
        {$listObj->getListHeaderCell('Mode of Payment', 'r.mode_of_payment')}
        {$listObj->getListHeaderCell('Status', 'r.receipt_status')}
        {$listObj->getListHeaderCell('Date', 'r.date')}
        {$listObj->getListHeaderCell('Invoice ID', 'invoice_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Receipt ID', 'receipt_id' , 'headerCenter')}
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
        
        /*if ($row['contact_name']){
            $contact = $formObj->getTBRow('Contact Name', 'contact_name', $row['contact_name']);
        } else {
            $contact = $formObj->getTBRow('Name', 'title', $row['title']);
        }*/

        $fielset1 = "
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
        {$media->getRightPanelMediaDisplay('Attachments', 'enggCrm_receipt', 'attachment', $row)}
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

        $receipt_date1 = $fn->getReqParam('receipt_date1');
        $receipt_date2 = $fn->getReqParam('receipt_date2');
        $mode_of_payment = $fn->getReqParam('mode_of_payment');

        $yearEnd = date('Y') + 10;

        $paymentType = $fn->getValueListSQL('paymentType');

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
        ";        
        
        return $text;
    }

    /**
     *
     */
     function getGenerateReceiptFormFromInvoice() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
               
        $invoice_id= $fn->getReqParam('invoice_id');

        $rows = '';
        $expEdit = array('isEditable' => 0);
        $today = date('Y-m-d');
        $formAction = "index.php?_topRm=project&module=enggCrm_receipt&_spAction=generateReceiptFormFromInvoiceSubmit&showHTML=0";

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
     function getEditReceiptFormFromInvoice() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $receipt_id = $fn->getReqParam('receipt_id');
        $invoice_id = $fn->getReqParam('invoice_id');

        $rowInvRecHist = $fn->getRecordRowByID('invoice_receipt_history', 'receipt_id', $receipt_id);

        $rowReceipt = $fn->getRecordRowByID('receipt', 'receipt_id', $receipt_id);
        $formAction = "index.php?_topRm=project&module=enggCrm_receipt&_spAction=editReceiptFormFromInvoiceSubmit&showHTML=0";
        
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
    function getGenerateReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $order_id = $fn->getReqParam('order_id');

        $rows = '';        
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
        WHERE i.order_id = {$order_id}
            AND (i.status = 'Due' || i.status = 'Partial Payment' || i.status = 'Late')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no invoice is available or all the invoices are paid" ;
        }

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            if ($row['gst_percentage']) {
                $total_invoice_amount = $fn->getAmountFractionFormattedForGst($row['invoice_amount'], $row['gst_percentage']);
            } else {
                $total_invoice_amount = round(($row['invoice_amount']), 2);
            }

            $prev_payment_amt = number_format($row['prev_inv_amount'],2);

            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' order_id='{$order_id}' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']}({$total_invoice_amount})</div>
                    <div class=''>Paid: {$prev_payment_amt}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=enggCrm_receipt&_spAction=generateReceiptFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDateRow('Date', 'receipt_date', date('Y-m-d'))}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getViewReceiptDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $text = '';
        
        $receipt_id = $fn->getReqParam('receipt_id');
        $row = $fn->getRecordByCondition('receipt', "receipt_id = '{$receipt_id}'");

        $exp = array('isEditable' => 0);        
        $receipt_date      = $dateUtil->formatDate($row['date'], 'DD-MMM-YYYY');
        $creation_date     = $dateUtil->formatDate($row['creation_date'], 'DD-MMM-YYYY HH:MIN:SS AM');
        $modification_date = $dateUtil->formatDate($row['modification_date'], 'DD-MMM-YYYY HH:MIN:SS AM');

        $cheque_details = "";
        if ($row['mode_of_payment'] == 'Cheque') {
            $cheque_date = $dateUtil->formatDate($row['cheque_date'], 'DD-MMM-YYYY');
            $cheque_details = "
            {$formObj->getTBRow('Cheque Date', 'cheque_date', $cheque_date, $exp)}
            {$formObj->getTBRow('Bank', 'bank_name', $row['bank_name'], $exp)}
            {$formObj->getTBRow('Cheque No', 'cheque_no', $row['cheque_no'], $exp)}
            ";
        }

        $text = "
        <form class='yform columnar' method='post' action=''>
            <strong>Receipt Details</strong>
            {$formObj->getTBRow('Code', 'receipt_code', $row['receipt_code'], $exp)}
            {$formObj->getTBRow('Status', 'receipt_status', $row['receipt_status'], $exp)}
            {$formObj->getTBRow('Date', 'date', $receipt_date, $exp)}
            {$formObj->getTBRow('Amount', 'amount', $row['amount'], $exp)}
            {$formObj->getTBRow('Mode of payment', 'mode_of_payment', $row['mode_of_payment'], $exp)}
            {$cheque_details}
            {$formObj->getTBRow('Remarks', 'remarks', $row['remarks'], $exp)}
            {$formObj->getTBRow('Generated Date', 'creation_date', $creation_date, $exp)}
            {$formObj->getTBRow('Generated By', 'created_by', $row['created_by'], $exp)}
            {$formObj->getTBRow('Updated Date', 'modification_date', $modification_date, $exp)}
            {$formObj->getTBRow('Updated By', 'modified_by', $row['modified_by'], $exp)}
        </form>
        ";
        
        return $text;
    }
}