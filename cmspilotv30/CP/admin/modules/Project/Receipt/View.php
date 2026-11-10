<?
class CP_Admin_Modules_Project_Receipt_View extends CP_Common_Lib_ModuleViewAbstract
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
        {$media->getRightPanelMediaDisplay('Attachments', 'project_receipt', 'attachment', $row)}
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
        $formAction = "index.php?_topRm=project&module=project_receipt&_spAction=generateReceiptFormFromInvoiceSubmit&showHTML=0";

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
        $formAction = "index.php?_topRm=project&module=project_receipt&_spAction=editReceiptFormFromInvoiceSubmit&showHTML=0";
        
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

}