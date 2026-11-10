<?
class CP_Admin_Widgets_AgileIms_AgeingReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $enrollment_type = $fn->getReqParam('enrollment_type');

        $rowsHTML = $this->getRowsHTML($enrollment_type);

        if ($rowsHTML != "") {
            $text = "
    		<div class = 'tableOuter scroll-pane'>
    		<table class='thinlist'>
    			<thead>
    				<tr>
    					<th>S.NO</th>
    					<th>STUDENT/COMPANY NAME</th>
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
    function getRowsHTML($enrollment_type = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        if ($enrollment_type == 'Individual') {
            $sql = "
            SELECT DISTINCT o.contact_id AS company_contact_id
                  ,o.order_id
                  ,c.first_name AS company_contact_name
                  ,c.mobile AS company_contact_no
                  ,c.email AS company_contact_email
            FROM `order` o
            LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
            LEFT JOIN (contact c) ON (o.contact_id = c.contact_id)
            WHERE i.status != 'Cancelled'
              AND o.contact_id IS NOT NULL
            ";
        } else {
            $sql = "
            SELECT DISTINCT o.company_id AS company_contact_id
                  ,o.order_id
                  ,c.title AS company_contact_name
                  ,c.phone AS company_contact_no
                  ,c.email AS company_contact_email
            FROM `order` o
            LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
            LEFT JOIN (company c) ON (o.company_id = c.company_id)
            WHERE i.status != 'Cancelled'
              AND o.company_id IS NOT NULL
            ";
        }
        $result = $db->sql_query($sql);

        $rows = '';
        $serial_no = 1;
        while ($row = $db->sql_fetchrow($result)) {
            if ($this->getOverallDueForCompanyContact($enrollment_type, $row['company_contact_id']) > 0) {

                $overallDueFormatted = number_format($this->getOverallDueForCompanyContact($enrollment_type, $row['company_contact_id']), 2);
                $export = "index.php?module=agileIms_invoice&_spAction=printAgeingReport&enrollment_type={$enrollment_type}&company_contact_id={$row['company_contact_id']}&showHTML=0";

                $rows .= "
                <tr>
                    <td>{$serial_no}</td>
                    <td>{$row['company_contact_name']}</td>
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
    function getInvoiceIdsForCompanyContact($enrollment_type, $company_contact_id) {
        $db = Zend_Registry::get('db');

        $current_date = date('Y-m-d');

        if ($enrollment_type == 'Individual') {
            $where = "o.contact_id = {$company_contact_id}";
        } else {
            $where = "o.company_id = {$company_contact_id}";
        }
        
        $sql = "
        SELECT DISTINCT i.invoice_id
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE {$where}
          AND (i.status = 'Due'
           OR i.status = 'Partial Payment')
           AND i.invoice_date <= '{$current_date}'
        ";        
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        $count   = 1;
        
        $rowsInvoice = '';
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == $numRows) {
                $rowsInvoice .= $row['invoice_id'];
            } else {
                $rowsInvoice .= $row['invoice_id'] . ',';
            }
            $count++;
        }
        
        return $rowsInvoice;
    }

    /**
     *
     */
    function getOverallDueForCompanyContact($enrollment_type, $company_contact_id) {
        $db = Zend_Registry::get('db');
        
        $invoice_ids = $this->getInvoiceIdsForCompanyContact($enrollment_type, $company_contact_id);
        
        $total_amt_payable = 0;
        if ($invoice_ids) {
            $sqlInv = "
            SELECT SUM(i.invoice_amount) AS total_invoice_amount_due
            FROM invoice i
            WHERE i.invoice_id IN ({$invoice_ids})
            ";
            $resultInv = $db->sql_query($sqlInv);
            $rowInv    = $db->sql_fetchrow($resultInv);
            
            $total_inv_amt = $rowInv['total_invoice_amount_due'];
            
            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id IN ({$invoice_ids})
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);
    
            $total_amt_payable = $total_inv_amt - $rowRec['total_invoice_amount_paid'];
        }            

        return $total_amt_payable;
    }
}