<?
class CP_Admin_Widgets_Pms_StatementofAccountReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidgetOld() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $parent_id  = $fn->getReqParam('parent_id');
        
        if ($parent_id == '') {
            return "<div class='txtCenter'>Please choose a parent to generate statement of account.</div>";
        }

        $text = '';

        $rowParent = $fn->getRecordRowByID('parent', 'parent_id', $parent_id);

        $year         = date('Y');
        $month        = date('m');
        $current_date = date('Y-m-d');

        if ($start_date) {
            $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        } else {
            $start_date = $year . '-01-01';
            $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        }
        
        if ($end_date) {
            $end_date_formatted = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');
        } else {
            $end_date_formatted = $dateUtil->formatDate($current_date, 'DD/MM/YYYY');
        }

        $prev_outstanding_amount            = $this->model->getPreviousOutstandingBalanceAmount($parent_id, $start_date);
        $prev_outstanding_amount_formatted  = number_format($prev_outstanding_amount, 2);

        $total_outstanding_amount             = $this->model->getTotalOutstandingAmountSummary($parent_id, $start_date, $end_date);
        $overall_outstanding_amount           = $prev_outstanding_amount + $total_outstanding_amount;
        $overall_outstanding_amount_formatted = number_format($overall_outstanding_amount, 2);

        $rowsHTML = $this->getRowsHTML($parent_id, $start_date, $end_date, $prev_outstanding_amount);

        $text = "
		<div class = 'tableOuter scroll-pane'>
        <table class='thinlist summaryTable mb20'>
            <thead>
                <th colspan='6'>Summary</th>
            </thead>
            <tr>
                <td>Parent Name : {$rowParent['first_name']}</td>
                <td>Start Date : {$start_date_formatted}</td>
                <td>End Date : {$end_date_formatted}</td>
                <td class='txtRight'>Total Amount Payable : {$overall_outstanding_amount_formatted}</td>
            </tr>
        </table>

		<table class='thinlist'>
			<thead>
				<tr>
					<th>INVOICE #</th>
					<th>DATE</th>
					<th>BRANCH</th>
					<th>STUDENT NAME</th>
					<th>MONTH</th>
					<th>YEAR</th>
					<th class='txtRight'>INVOICE AMOUNT (SGD)</th>
				</tr>
			</thead>
			<tbody>
			    <tr>
			        <td colspan='6'><strong>OPENING BALANCE</strong></td>
			        <td class='txtRight'>{$prev_outstanding_amount_formatted}</td>
			    </tr>
				{$this->getRowsHTML($parent_id, $start_date, $end_date, $prev_outstanding_amount)}
			</tbody>
		</table>

        <table class='thinlist summaryTable mt20'>
            <thead>
                <th colspan='6' class='txtCenter'>STATEMENT SUMMARY</th>
            </thead>
            <tr>
                <td class='txtRight'>Past Due 1-30</td>
                <td class='txtRight'>Past Due 31-60</td>
                <td class='txtRight'>Past Due >60</td>
                <td class='txtRight'>Total Amount</td>
            </tr>
            <tr>
                <td class='txtRight'>{$this->model->getPastBalanceAmount($parent_id, $end_date, 30)}</td>
                <td class='txtRight'>{$this->model->getPastBalanceAmount($parent_id, $end_date, 60)}</td>
                <td class='txtRight'>{$this->model->getPastBalanceAmount($parent_id, $end_date, 61)}</td>
                <td class='txtRight'>{$overall_outstanding_amount_formatted}</td>
            </tr>
        </table>
		</div>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTMLOld($parent_id, $start_date, $end_date, $prev_outstanding_amount) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $rows = '';
        
        $current_date = date('Y-m-d');
        $year  = date('Y');
        $month = date('m');

        $total_outstanding_amount = 0;
        foreach($this->model->dataArray as $row){
            $amount_payable = $row['invoice_amount'] - $row['discount_amount'];
            $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD/MM/YYYY');
            $formatted_invoice_amount = number_format($amount_payable, 2);
            
            switch ($row['invoice_month']) {
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
            
            $rows .= "
            <tr>
				<td>{$row['invoice_code']}</td>
				<td>{$invoice_date}</td>
				<td>{$row['branch_name']}</td>
				<td>{$row['student_name']}</td>
				<td>{$prefix_month}</td>
				<td>{$row['year_of_enrollment']}</td>
				<td class='txtRight'>{$formatted_invoice_amount}</td>
            </tr>
            ";
            $total_outstanding_amount += $amount_payable;
        }
        
        $outstanding_amount = $prev_outstanding_amount + $total_outstanding_amount;        
        $formatted_outstanding = number_format($outstanding_amount, 2);

        $text = "
        {$rows}
	    <tr>
	        <td COLSPAN='6'><strong>TOTAL AMOUNT PAYABLE</strong></td>
	        <td class='txtRight'>{$formatted_outstanding}</td>
	    </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>S.NO</th>
					<th>DDA</th>
					<th>PARENT NAME</th>
					<th>MOBILE</th>
					<th>STUDENTS</th>
					<th class='txtRight'>OVERALL DUE (SGD)</th>
					<th class='txtRight'>EXPORT</th>
				</tr>
			</thead>
			<tbody>
				{$this->getRowsHTML()}
			</tbody>
		</table>
		</div>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        
        $rows = '';
        
        $counter = 1;
        foreach($this->model->dataArray as $row){
            
            if ($this->getStudentIdsForParent($row['parent_id']) != '' &&
               $this->getOverallDueForParent($row['parent_id']) > 0) {
                
                $overallDueFormatted = number_format($this->getOverallDueForParent($row['parent_id']), 2);
                
                $parent_link = "/admin/index.php?_topRm=reports&module=pms_reports&showHTML=0report%3DstatementofAccountReport&parent_id={$row['parent_id']}&report=statementofAccountReport&_spAction=exportData&report=statementofAccountReport&showHTML=0";

                $export = "index.php?widget=pms_statementofAccountReport&_spAction=printStatementOfAccount&parent_id={$row['parent_id']}&showHTML=0";
                $export = "index.php?module=pms_invoice&_spAction=printStatementOfAccount&parent_id={$row['parent_id']}&showHTML=0";
                
                $rows .= "
                <tr>
    				<td>{$counter}</td>
    				<td>{$row['dda']}</td>
    				<td>{$row['parent_name']}</td>
    				<td>{$row['mobile']}</td>
    				<td>{$this->getStudentsNameForParent($row['parent_id'])}</td>
    				<td class='txtRight'>{$overallDueFormatted}</td>
    				<!--<td class='txtRight'><a href='{$parent_link}'>Export to Excel</a></td>-->
    				<td class='txtRight'><a href='{$export}'>Export to Excel</a></td>
                </tr>
                ";
                $counter++;
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
    function getStudentIdsForParent($parent_id) {
        $db = Zend_Registry::get('db');

        $sql = "
        SELECT DISTINCT c.contact_id
        FROM contact c
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)          ON (pc.parent_id = p.parent_id)
        WHERE c.status = 'Active'
            AND p.parent_id = {$parent_id}
        ";
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        $count   = 1;
        
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == $numRows) {
                $rows .= $row['contact_id'];
            } else {
                $rows .= $row['contact_id'] . ',';
            }
            $count++;
        }
        
        return $rows;
    }

    /**
     *
     */
    function getStudentsNameForParent($parent_id) {
        $db = Zend_Registry::get('db');
        
        $student_ids = $this->getStudentIdsForParent($parent_id);

        $sql = "
        SELECT DISTINCT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as student_name
        FROM contact c
        WHERE c.contact_id IN ({$student_ids})
        ORDER BY student_name ASC
        ";
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        $count   = 1;
        
        $student_name = '';
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == $numRows) {
                $student_name .= $row['student_name'];
            } else {
                $student_name .= $row['student_name'] . ',<br/>';
            }
            $count++;
        }
        
        return $student_name;
    }

    /**
     *
     */
    function getInvoiceIdsForContact($parent_id) {
        $db = Zend_Registry::get('db');

        $current_date = date('Y-m-d');
        
        $sql = "
        SELECT DISTINCT i.invoice_id
        FROM invoice i
        LEFT JOIN (contact c)         ON (i.contact_id = c.contact_id)
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)          ON (pc.parent_id = p.parent_id)
        WHERE c.status = 'Active'
          AND p.parent_id = {$parent_id}
          AND (i.status = 'Due'
           OR i.status = 'Partial Payment')
           AND i.invoice_date < '{$current_date}'
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
    function getOverallDueForParent($parent_id) {
        $db = Zend_Registry::get('db');
        
        $invoice_ids = $this->getInvoiceIdsForContact($parent_id);
        
        $total_amt_payable = 0;
        if ($invoice_ids) {
            $sqlInv = "
            SELECT SUM(i.invoice_amount) AS total_invoice_amount_due
                  ,SUM(i.discount_amount) AS total_invoice_amount_discounted
            FROM invoice i
            WHERE i.invoice_id IN ({$invoice_ids})
            ";
            $resultInv = $db->sql_query($sqlInv);
            $rowInv    = $db->sql_fetchrow($resultInv);
            
            $total_inv_amt_after_discount = $rowInv['total_invoice_amount_due'] - $rowInv['total_invoice_amount_discounted'];
            
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
    
            $total_amt_payable = $total_inv_amt_after_discount - $rowRec['total_invoice_amount_paid'];
        }            

        return $total_amt_payable;
    }

}