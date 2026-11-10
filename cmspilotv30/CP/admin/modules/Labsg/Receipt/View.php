<?
class CP_Admin_Modules_Labsg_Receipt_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getGenerateReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $order_id         = $fn->getReqParam('order_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

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
            AND (i.status = 'Due' || i.status = 'Partial Payment')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no invoice is available or all the invoices are paid" ;
        }


        $invoice_amount = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $invoice_amount = $row['invoice_amount'] - $row['discount'];

            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']}({$invoice_amount})</div>
                    <div class=''>Paid:{$row['prev_inv_amount']}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=labsg_order&_spAction=generateReceiptFormSubmit&showHTML=0";

        $expArray = array('globalForAllSites' => true);
        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDDRowByVL('Mode of Payment*', 'mode_of_payment', 'paymentType', '', $expArray)}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;

    }

    /**
     *
     */
    function getCancelReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $rows = '';
        $receipt_id = $fn->getReqParam('receipt_id');

        $formAction = "index.php?_topRm=finance&module=labsg_receipt&_spAction=cancelReceiptFormSubmit&showHTML=0";
        $text = "
        <form id='portalForm' class='yform columnar cancelReceiptForm' method='post' action='{$formAction}'>
            {$formObj->getTARow('Notes', 'cancelling_notes', '')}
            <input type='hidden' name='receipt_id' value='{$receipt_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getReceiptDetails(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $text = '';
        
        $receipt_id = $fn->getReqParam('receipt_id');
        $sqlReceipt  = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = '{$receipt_id}'
        ";
        $result  = $db->sql_query($sqlReceipt);  
        $exp = array('isEditable' => 0);
        while ($row = $db->sql_fetchrow($result)) {
            $date = $dateUtil->formatDate($row['date'], 'DD MMM YYYY');
            $creation_date = $dateUtil->formatDate($row['creation_date'], 'DD MMM YYYY');
            $modification_date = $dateUtil->formatDate($row['modification_date'], 'DD MMM YYYY');

            $cancelling_notes = '';
            if ($row['receipt_status'] == 'Cancelled') {
                $cancelling_notes = "{$formObj->getTARow('Cancelled Notes', 'cancelling_notes', $row['cancelling_notes'], $exp)}";
            }
            $text = "
            <div class='txtCenter'><strong>RECEIPT DETAILS</strong></div><br/>
            <table class='thinlist'>
                <tbody>
                    <tr style='background-color:#EAEAE8;'>
                        <th>Receipt Code</th>
                        <th>Mode of payment</th>
                        <th>Receipt Date</th>
                        <th>Amount</th>
                    </tr>
                    <tr>
                        <td>{$row['receipt_code']}</td>
                        <td>{$row['mode_of_payment']}</td>
                        <td>{$date}</td>
                        <td>{$row['amount']}</td>
                    </tr>
                    <tr style='background-color:#EAEAE8;'>
                        <th>Status</th>
                        <th colspan='2'>Notes</th>
                        <th>Cancelled Notes</th>
                    </tr>
                    <tr>
                        <td>{$row['receipt_status']}</td>
                        <td colspan='2'>{$row['remarks']}</td>
                        <td>{$row['cancelling_notes']}</td>
                    </tr>
                    <tr style='background-color:#EAEAE8;'>
                        <th>Created By</th>
                        <th>Creation Date</th>
                        <th>Modified By</th>
                        <th>Modification Date</th>
                    </tr>
                    <tr>
                        <td>{$row['created_by']}</td>
                        <td>{$creation_date}</td>
                        <td>{$row['modified_by']}</td>
                        <td>{$modification_date}</td>
                    </tr>
                </thead>
            </table>
            ";
        }
        
        return $text;
     }
}