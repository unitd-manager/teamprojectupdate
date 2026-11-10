<?
class CP_Admin_Modules_AgileIms_Receipt_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['mode_of_payment'])}
            {$listObj->getListDataCell($row['receipt_status'])}
            {$listObj->getListDateCell($row['date'])}
            {$listObj->getListDataCell($row['order_id'], 'center')}
            {$listObj->getListDataCell($row['receipt_id'], 'center')}
            {$listObj->getListRowEnd($row['receipt_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Receipt Code', 'r.receipt_code')}
        {$listObj->getListHeaderCell('Amount', 'r.amount', 'headerRight')}
        {$listObj->getListHeaderCell('Contact Name', 'contact_name')}
        {$listObj->getListHeaderCell('Mode of Payment', 'r.mode_of_payment')}
        {$listObj->getListHeaderCell('Status', 'r.receipt_status')}
        {$listObj->getListHeaderCell('Date', 'r.date')}
        {$listObj->getListHeaderCell('Order ID', 'order_id' , 'headerCenter')}
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
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $expNoEdit = array('isEditable' => 0);
        
        if ($row['contact_name']){
            $contact = $formObj->getTBRow('Contact Name', 'contact_name', $row['contact_name']);
        } else {
            $contact = $formObj->getTBRow('Name', 'title', $row['title']);
        }

        $receipt_date = $dateUtil->formatDate($row['date'], 'DD MMM YYYY');

        $fielset1 = "
        {$contact}
        {$formObj->getTBRow('Receipt Code', 'receipt_code', $row['receipt_code'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getTBRow('Mode of Payment', 'mode_of_payment', $row['mode_of_payment'])}
        {$formObj->getTBRow('Cheque No', 'cheque_no', $row['cheque_no'])}
        {$formObj->getDateRow('Cheque Date', 'cheque_date', $row['cheque_date'])}
        {$formObj->getTBRow('Bank', 'bank_name', $row['bank_name'])}
        {$formObj->getTBRow('Status', 'receipt_status', $row['receipt_status'])}
        {$formObj->getDateRow('Receipt Date', 'date', $receipt_date)}
        {$formObj->getTBRow('Order ID', 'order_id', $row['order_id'])}
        {$formObj->getTARow('Remarks', 'remarks', $row['remarks'])}
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
        {$media->getRightPanelMediaDisplay('Attachments', 'agileIms_receipt', 'attachment', $row)}
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

        $receipt_date1   = $fn->getReqParam('receipt_date1');
        $receipt_date2   = $fn->getReqParam('receipt_date2');
        $mode_of_payment = $fn->getReqParam('mode_of_payment');
        $receipt_status  = $fn->getReqParam('receipt_status');

        $yearEnd = date('Y') + 10;

        $paymentType = $fn->getValueListSQL('paymentType');
        
        $SQLStatus = "
        SELECT DISTINCT receipt_status
        FROM receipt
        ORDER BY receipt_status ASC
        ";

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
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $receipt_status)}
            </select>
        </td>
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
               
        $order_id = $fn->getReqParam('order_id');
        
        $_SESSION['selectedInvoiceIds'] = '';

        $rows = '';
         
        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(amount) AS prev_sum
            FROM invoice_receipt_history invHist
            WHERE invHist.invoice_id =  i.invoice_id 
            ) as prev_inv_amount
        FROM invoice i
        WHERE i.order_id = {$order_id}
          AND (i.status = 'Due' || i.status = 'Partial Payment')
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $prev_paid_inv_amount = $row['prev_inv_amount'];
            if ($row['prev_inv_amount'] == '') {
                $prev_paid_inv_amount = 0;
            }
            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']} ({$row['invoice_amount']} SGD)</div>
                    <div class=''>Paid: {$prev_paid_inv_amount} SGD</div>
                </div>
            </div>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=agileIms_receipt&_spAction=generateReceiptFormSubmit&showHTML=0";

        $expEdit   = array('isEditable' => 0);
        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <div class=''>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getDateRow('Date', 'date', date('Y-m-d'))}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            {$formObj->getTBRow('Issued By', 'issued_by', $fn->getSessionParam('userName'))}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $receipt_id = $fn->getReqParam('receipt_id');
        $row        = $fn->getRecordRowById('receipt', 'receipt_id', $receipt_id);

        $formAction = "index.php?module=agileIms_receipt&_spAction=editReceiptFormSubmit&showHTML=0";
        
        $hideme = '';
        if ($row['mode_of_payment'] != 'Cheque') {
            $hideme = 'hideme';
        }
        
        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Receipt Date', 'date', $row['date'])}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType', $row['mode_of_payment'])}
            {$formObj->getDateRow('Cheque date', 'cheque_date', $row['cheque_date'], array('rowCls' => $hideme))}
            {$formObj->getTBRow('Cheque No', 'cheque_no', $row['cheque_no'], array('rowCls' => $hideme))}
            {$formObj->getTBRow('Bank', 'bank_name', $row['bank_name'], array('rowCls' => $hideme))}
            {$formObj->getTARow('Note', 'remarks', $row['remarks'])}
            <input type='hidden' name='receipt_id' value='{$receipt_id}' />
        </form>
        ";

        return $text;
    }

    /**
     */
     function getGenerateMiscReceiptForm() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $order_id= $fn->getReqParam('order_id');
        $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $order_id);

        $today = date('Y-m-d');
         
        $formAction = "index.php?module=agileIms_receipt&_spAction=generateReceiptFormSubmitPvt&showHTML=0";

        /* Hiding Receipt Code and COI no for Short term courses */
        $receipt_code = $formObj->getTBRow('Receipt Code', 'receipt_code');
        $coi_no       = $formObj->getTBRow('COI NO', 'coi_no');
        
        $SQLCourse = "
        SELECT c.course_type
        FROM course c
        LEFT JOIN (course_contact cc) ON (c.course_id = cc.course_id)
        WHERE cc.order_id = {$order_id}
        ";
        $resultCourse = $db->sql_query($SQLCourse);
        $rowCourse = $db->sql_fetchrow($resultCourse);
        
        if ($rowCourse['course_type'] == 'Short Term') {
            $receipt_code = '';
            $coi_no = '';
        }
        
        $late_fee                   = $fn->getSettingsValueByKey('miscReceiptLateFeeCharge');
        $module_subject_change_fee  = $fn->getSettingsValueByKey('miscReceiptModuleSubjectChangeFee');
        $exam_result_review_fee     = $fn->getSettingsValueByKey('miscReceiptExamResultReviewFee');
        $ns_deferment_fees            = $fn->getSettingsValueByKey('miscReceiptNSDefermentFee');
        $credit_card_service_fees   = $fn->getSettingsValueByKey('miscReceiptCreditCardServiceCharge');
        $other_charges              = $fn->getSettingsValueByKey('miscReceiptOtherCharge');
        
        $misc_total = $late_fee + $module_subject_change_fee + $exam_result_review_fee + $ns_deferment_fees + $credit_card_service_fees + $other_charges;
        
        $text = "
        <form id='portalForm' class='yform columnar miscReceiptFormForPvt' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Late Fees Charge', 'late_fees', $late_fee)}
            {$formObj->getTBRow('Change of modules/subjects Fees', 'module_subject_change_fee', $module_subject_change_fee)}
            {$formObj->getTBRow('Review of exam results Fees', 'exam_result_review_fee', $exam_result_review_fee)}
            {$formObj->getTBRow('Administration Fees for NS Deferment', 'ns_deferment_fees', $ns_deferment_fees)}
            {$formObj->getTBRow('Credit Card Service charge', 'credit_card_service_fees', $credit_card_service_fees)}
            {$formObj->getTBRow('Other Charge', 'other_charges', $other_charges)}
            {$formObj->getTBRow('Amount', 'amount', $misc_total)}
            {$receipt_code}
            {$formObj->getDateRow('Receipt Date', 'date', $today)}
            {$coi_no}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Issued By', 'issued_by', $_SESSION['userFullName'])}
            {$formObj->getTBRow('Approval Code', 'approval_code')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='invoice_id' value='{$invoiceRec['invoice_id']}' />
            <input type='hidden' name='course_type' value='{$rowCourse['course_type']}' />
            <input type='hidden' name='receipt_type' value='misc receipt' />
        </form>
        ";

        return $text;
    }

}