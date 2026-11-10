<?
class CP_Admin_Widgets_Project_SalesByMonthReports_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $text = "
        <h2>Sales by Last 12 Months</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Month</th>
					<th class='txtRight'>Total Project Amount (INR)</th>
                    <th class='txtRight'>Total Due Amount (INR)</th>
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
    function getRowsHTMLOld() {
        $rows = '';
        foreach($this->model->dataArray as $row){
            $project_month_monthly = number_format($row['project_month_monthly'], 2);
            $rows .= "
			<tr>
				<td>{$row['project_month']}</td>
				<td class='txtRight'>{$project_month_monthly}</td>
			</tr>
			";                
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

    function getRowsHTMLArif() {
        $db = Zend_Registry::get('db');
        
        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $text = '';
        $sqlOrder = "
        SELECT o.* FROM `order` o
        WHERE o.order_date BETWEEN '{$last12Month}' AND '{$today}'
        ";
        $resultOrder = $db->sql_query($sqlOrder);
        $prev_month_val = '';
        while ($rowOrder = $db->sql_fetchrow($resultOrder)) {        
            $month_val = substr($rowOrder['order_date'], 5,2);
            
            $sqlOI = "
            SELECT (oi.unit_price * oi.qty) AS total_amount
            FROM order_item oi
            WHERE oi.order_id = {$rowOrder['order_id']}
            ";
            $resultOI = $db->sql_query($sqlOI);

            $total_amount = 0;
            $prev_month   = 0;

            while ($rowOI = $db->sql_fetchrow($resultOI)) {                
                if ($prev_month != $month_val) {                    
                    $total_amount = $rowOI['total_amount'];

                    $text .= "
    		    	<tr>
    		    		<td>{$rowOrder['order_date']}</td>
    		    		<td class='txtRight'>{$rowOI['total_amount']}</td>
    		    	</tr>
                    ";
                    
                    $prev_month = $month_val;
                } else {
                    $total_amount .= $rowOI['total_amount'];
                    $prev_month = substr($rowOrder['order_date'], 5,2);
                }
            }
            /*
            $text .= "
	    	<tr>
	    		<td>{$rowOrder['order_date']}</td>
	    		<td class='txtRight'>{$rowOI['total_amount']}</td>
	    	</tr>
            ";
            */
        }
        
        return $text;
    }
    /**
     *
     */
    function getRowsHTML() {
        $rows = '';
        $total = 0;
        $total_due = 0;
        foreach($this->model->dataArray as $row){
            $monthly_details = $this->getMonthlyProjectDetails($row['project_month']);
            $project_amount_monthly = number_format($row['project_amount_monthly'], 2);

            // Total amount paid for the month
            $amount_paid_monthly = $this->model->getPaidAmountForMonth($row['project_month']);

            $total_due_amount = ($row['project_amount_monthly'] - $amount_paid_monthly);
            $total_due_amount_formatted = number_format($total_due_amount, 2);

            $rows .= "
			<tr class='projectSalesSummary'>
                <td colspan='3'>
                    <table class=''>
                        <tr>
                            <td class='projectMonth'>{$row['project_month']}</td>
                            <td class='txtRight projectAmt'>{$project_amount_monthly}</td>
                            <td class='txtRight projectDueAmt'>{$total_due_amount_formatted}</td>
                        </tr>

                        <tr>
                            <td class='monthlyDetailsMain' colspan='3'>{$monthly_details}</td> 
                        </tr>
                    </table>
                </td>
			</tr>
			";                
            $total += $row['project_amount_monthly'];
            $total_due += $total_due_amount;
        }
        $total = number_format($total, 2);
        $total_due = number_format($total_due, 2);
        
        $text = "
        {$rows}
        <tr>
            <td class='lastRowBgColor'>Total</td>
            <td class='txtRight lastRowBgColor'>{$total}</td>
            <td class='txtRight lastRowBgColor'>{$total_due}</td>
        </tr>
        ";

        return $text;
    }

    /**
     * 
     */
    function getMonthlyProjectDetails($yearMonth) {
        $db = Zend_Registry::get('db');

        $rows  = '';
        $count = 1;

        $sqlProj = "
        SELECT DISTINCT project_id
              ,title
              ,project_value_ref
        FROM project
        WHERE status != 'Cancelled'
          AND status != 'On Hold'
          AND DATE_FORMAT(start_date, '%b %Y') = '{$yearMonth}'
        ORDER BY project_id ASC
        ";
        $resultProj = $db->sql_query($sqlProj);
        while ($rowProj = $db->sql_fetchrow($resultProj)) {
            $proj_val = number_format($rowProj['project_value_ref'], 2);
            $invoice_amt_paid = $this->model->getOutstandingAmountForProject($rowProj['project_id']);

            $outstanding_amt = number_format($rowProj['project_value_ref'] - $invoice_amt_paid, 2);
            $rows .= "
            <tr>
                <td>{$count}</td>
                <td class='projectTitle'><a href='/admin/index.php?_topRm=project&module=project_project&_action=detail&project_id={$rowProj['project_id']}'>{$rowProj['title']}</a></td>
                <td class='projectAmtDetail txtRight'>{$proj_val}</td>
                <td class='projectDueAmtDetail txtRight'>{$outstanding_amt}</td>
            <tr>
            ";
            $count++;
        }

        $text = "
        <div class='projectDetails mt5'>
            <table class='projectDetails'>
                <tr>
                    <td>S.No</td>
                    <td class='projectTitle'>Project Name </td>
                    <td class='projectAmtDetail txtRight'>Project Amount (INR)</td>
                    <td class='projectDueAmtDetail txtRight'>Amount Due (INR)</td>
                </tr>
                {$rows}
            </table>
        </div>
        ";
        
        return $text;
    }
}