<?
class CP_Admin_Widgets_Labsg_RevenueByDay_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            if ($year == '' || $year == 'null') {
                $year = date('Y');
            }

            $month_year = $year . '-' . $month . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='2'>Summary</th>
            </thead>
            <tr>
                <td>Start Date : {$start_date_formatted}</td>
                <td>End Date : {$end_date_formatted}</td>
            </tr>
        </table>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Date</th>
					<th>Day</th>
					<th class='txtRight'>Total</th>
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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $total = 0;
		$siteTitle = '' ;
		$siteLocationTotal = '' ;

        foreach($this->model->dataArray as $row){
            $invoice_amount_monthly = number_format(($row['total_invoice_amount'] - $row['total_discount']), 2);
			$creationDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");

            $rows .= "
			<tr>
				<td>{$creationDate}</td>
				<td>{$row['day']}</td>
				<td class='txtRight'>{$invoice_amount_monthly}</td>
			</tr>
			";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}