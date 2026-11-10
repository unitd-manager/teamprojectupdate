<?
class CP_Admin_Widgets_Tradingsg_GstReport_View extends CP_Common_Lib_WidgetViewAbstract
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
        $prefix_month = '';

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
            </tr>
        </table>

		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist mt10'>
			<thead>
				<tr>
					<th>Order Id</th>
					<th>Order Date</th>
					<th class='txtRight'>Total Amount</th>
					<th class='txtRight'>Amount Invoiced</th>
					<th class='txtRight'>GST Amount</th>
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows               = '';
        $overall_order_amount   = 0;
        $overall_amount_invoiced = 0;
        $overall_gst_amount  = 0;

        foreach ($this->model->dataArray as $row) {

            $urlOrder = "<a target = '_blank' href = 'index.php?_topRm=finance&module=tradingsg_order&_action=edit&record_id={$row['order_id']}'><u>{$row['order_id']}</u></a>";

                $gsttaxvalue              = $cpCfg['amtForGSTCalc'] ;
                $gstvalue                 = $row['order_amount'] * $gsttaxvalue / 100;
                $totalvalue               = $gstvalue + $row['order_amount'];
                $overall_order_amount    += $row['order_amount'];
                $overall_amount_invoiced += $row['amount_invoiced'];
                $overall_gst_amount      += $gstvalue;

                $gstvalue    = number_format($gstvalue,2);
                $totalvalue  = number_format($totalvalue,2);

		    $rows .= "
    			<tr>
    				<td>{$urlOrder}</td>
    				<td>{$row['order_date']}</td>
    				<td class='txtRight'>{$row['order_amount']}</td>
    				<td class='txtRight'>{$row['amount_invoiced']}</td>
                    <td class='txtRight'>{$gstvalue}</td>
    			</tr>
			";
        }
        $overall_order_amount   = number_format($overall_order_amount, 2);
        $overall_amount_invoiced = number_format($overall_amount_invoiced, 2);
        $overall_gst_amount  = number_format($overall_gst_amount, 2);

        $rows .= "
            <tr>
                <th colspan='2' class='txtRight'>Overall Amount</th>
                <th class='txtRight'>{$overall_order_amount}</th>
                <th class='txtRight'>{$overall_amount_invoiced}</th>
                <th class='txtRight'>{$overall_gst_amount}</th>
            </tr>
        ";

        $text = "
        {$rows}
        ";

        return $text;
    }

}