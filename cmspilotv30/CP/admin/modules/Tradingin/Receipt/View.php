<?
class CP_Admin_Modules_Tradingin_Receipt_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getGenerateReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

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
            ,q.currency
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE i.order_id = {$order_id}
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
                    <div class='float_left'>{$row['invoice_code']}({$row['invoice_amount']}{$row['currency']})</div>
                    <div class=''>Paid:{$row['prev_inv_amount']}{$row['currency']}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=tradingin_order&_spAction=generateReceiptFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;

    }
}