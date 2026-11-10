<?
class CP_Admin_Widgets_Tradingsg_SummarySalesReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /*
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $company_id = $fn->getReqParam('company_id');
        
        switch ($month) {
            case 1: $prefix_month = 'January';
            break;
            case 2: $prefix_month = 'February';
            break;
            case 3: $prefix_month = 'March';
            break;
            case 4: $prefix_month = 'April';
            break;
            case 5: $prefix_month = 'May';
            break;
            case 6: $prefix_month = 'June';
            break;
            case 7: $prefix_month = 'July';
            break;
            case 8: $prefix_month = 'August';
            break;
            case 9: $prefix_month = 'September';
            break;
            case 10: $prefix_month = 'October';
            break;
            case 11: $prefix_month = 'November';
            break;
            case 12: $prefix_month = 'December';
            break;
        }

        if ($start_date != '' && $end_date!= '' && ($end_date < $start_date)) {
            return "<div class='txtCenter'>Start date should not be after End date</div>";
        }

        $companyRec = $fn->getRecordRowById('company', 'company_id', $company_id);

        if ($start_date != '' && $end_date == '') {
            $end_date = date('Y-m-d');
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
        }

        $start_date = $dateUtil->formatDate($start_date, 'DD-MMM-YYYY');
        $end_date   = $dateUtil->formatDate($end_date, 'DD-MMM-YYYY');
        
        if ($start_date != '' || $start_date != '') {
            $prefix_month = '';
            $year = '';
        }
        
        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='5'>Summary</th>
            </thead>
            <tr>
                <td>From Date : {$start_date}</td>
                <td>To Date : {$end_date}</td>
                <td>Month : {$prefix_month}</td>
                <td>Year : {$year}</td>
                <td>Client : {$companyRec['company_name']}</td>
            </tr>
        </table>
		
		<div class = 'tableOuter scroll-pane'>
    		<table class='thinlist mt10'>
    			<thead>
    				<tr>
    					<th>Quote Code</th>
    					<th>Order Date</th>
    					<th>Client Name</th>
    					<th class='txtRight'>Total Sales</th>
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

    /*
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
       
        $rows             = '';
        $overall_sales    = 0;
		
        foreach ($this->model->dataArray as $row) {
            $total_sales    = number_format($row['total_sales'], 2);
            
            $overall_sales  += $row['total_sales'];

		    $rows .= "
			<tr>
				<td>{$row['quote_code']}</td>
				<td>{$row['formatted_order_date']}</td>
				<td>{$row['company_name']}</td>
				<td class='txtRight'>{$total_sales}</td>
			</tr>
			";
        }
        
        $format_overall_sales = number_format($overall_sales, 2);
        
        $rows .= "
        <tr>
            <td colspan='3' class='txtRight'>Overall Amount</td>
            <td class='txtRight'>{$format_overall_sales}</td>
        </tr>
        ";

        $text = "
        {$rows}
        ";

        return $text;
    }

    /*
     *
     */
    function getOverallSales() {
        $overall_sales = 0;

        foreach ($this->model->dataArray as $row) {
            $overall_sales += $row['total_sales'];
        }
        
        $format_overall_sales  = number_format($overall_sales, 2);        
        return $format_overall_sales;
    }
}