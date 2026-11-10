<?
class CP_Admin_Widgets_Labsg_AgeingReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rowsHTML = $this->getRowsHTML();

        if ($rowsHTML != "") {
            $text = "
    		<div class = 'tableOuter scroll-pane'>
    		<table class='thinlist'>
    			<thead>
    				<tr>
    					<th>S.NO</th>
    					<th>COMPANY NAME</th>
                        <th>PHONE</th>
                        <th>EMAIL</th>
    					<th class='txtRight'>OVERALL DUE (SGD)</th>
    					<th class='txtRight'>EXPORT</th>
    				</tr>
    			</thead>
    			<tbody>
                    {$rowsHTML}
    			</tbody>
    		</table>
    		</div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $sql = "
        SELECT DISTINCT c.company_id
              ,c.company_name
              ,c.phone AS company_contact_no
              ,c.email AS company_contact_email
        FROM `company` c
        LEFT JOIN (`order` o) ON (c.company_id = o.company_id)
        LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
        WHERE i.status != 'Cancelled'
          AND o.company_id IS NOT NULL
        ORDER BY c.company_name ASC
        ";
        $result = $db->sql_query($sql);

        $rows = '';
        $serial_no = 1;
        while ($row = $db->sql_fetchrow($result)) {
            if ($this->getOverallDueForCompany($row['company_id']) > 0) {

                $overallDueFormatted = number_format($this->getOverallDueForCompany($row['company_id']), 2);
                $export = "index.php?module=labsg_invoice&_spAction=printAgeingReport&company_id={$row['company_id']}&showHTML=0";

                $rows .= "
                <tr>
                    <td>{$serial_no}</td>
                    <td>{$row['company_name']}</td>
                    <td>{$row['company_contact_no']}</td>
                    <td><a href='mailto:{$row['company_contact_email']}'>{$row['company_contact_email']}</a></td>
                    <td class='txtRight'>{$overallDueFormatted}</td>
                    <td class='txtRight'><a href='{$export}'>Export to Excel</a></td>
                </tr>
                ";
                $serial_no++;
            }
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
    /**
     *
     */
    function getInvoiceIdsForCompany($company_id) {
        $db = Zend_Registry::get('db');

        $current_date = date('Y-m-d');

        $sql = "
        SELECT DISTINCT i.invoice_id
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE o.company_id = {$company_id}
          AND (i.status = 'Due'
           OR i.status = 'Partial Payment'
           OR i.status = 'Late')
           AND i.invoice_date <= '{$current_date}'
        ";        
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        $count   = 1;
        
        $rowsInvoice = '';
        if ($numRows) {
            while ($row = $db->sql_fetchrow($result)) {
                if ($count == $numRows) {
                    $rowsInvoice .= $row['invoice_id'];
                } else {
                    $rowsInvoice .= $row['invoice_id'] . ',';
                }
                $count++;
            }
        }

        return $rowsInvoice;
    }

    /**
     *
     */
    function getOverallDueForCompany($company_id) {
        $db = Zend_Registry::get('db');

        $invoice_ids = $this->getInvoiceIdsForCompany($company_id);

        if ($invoice_ids == '') {
            return $total_amt_payable = 0;
        }

        $total_amt_payable = 0;
        $total_invoice_amount = 0;
        $total_receipt_amount = 0;

        $invoiceIds = explode(',', $invoice_ids);
        foreach($invoiceIds AS $invoice_id){
            $sqlInv = "
            SELECT i.invoice_amount
                  ,i.gst_percentage
            FROM invoice i
            WHERE i.invoice_id = {$invoice_id}
            ";
            $resultInv = $db->sql_query($sqlInv);
            $rowInv    = $db->sql_fetchrow($resultInv);

            if ($rowInv['gst_percentage'] > 0) {


                $gst_amount = (($rowInv['invoice_amount'] * $rowInv['gst_percentage'])/100);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);

                    /* Checking whether 3rd decimal point is more than or equal to 5
                       If Yes, add 1 to 2nd decimal point
                     */
                    $gstDecimalMore = substr($fraction, 2, 1);
                    $fraction = substr($fraction, 0, 2);
                    if ($gstDecimalMore >= 5) {
                        if ($fraction == '99') { //Increasing integer to 1 if decimal is 99
                            $fraction = '0.00';
                            $integer = $integer + 1;
                        } else {
                            $fraction = $fraction + 1;
                        }
                    }

                    $fraction = substr($fraction, 0, 2);
                    $gst_amount = $integer . "." . $fraction;
                } else if ($fraction_length == 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);
                    
                    if ($fraction == '99') { //Increasing integer to 1 if decimal is 99
                        $fraction = '0.00';
                        $integer = $integer + 1;
                    }
                }

        $total_invoice_amount += $rowInv['invoice_amount'] + $gst_amount;






                //$gst_amount = (($rowInv['invoice_amount'] * $rowInv['gst_percentage'])/100);
                ///* Taking two decimal values for gst amount */
                //$fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                //if ($fraction_length > 2) {
                //    list($integer, $fraction) = explode(".", (string) $gst_amount);
                //    $fraction = substr($fraction, 0, 2);
                //    $gst_amount = $integer . "." . $fraction;
                //}
                //
                //$total_invoice_amount += $rowInv['invoice_amount'] + $gst_amount;
            } else {
                $total_invoice_amount += $rowInv['invoice_amount'];
            }

            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id = {$invoice_id}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $total_receipt_amount += $rowRec['total_invoice_amount_paid'];
        }

        //$total_amt_payable = $total_invoice_amount - $total_receipt_amount;
        $total_amt_payable = $total_invoice_amount;

        return $total_amt_payable;
    }
}