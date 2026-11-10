<?
class CP_Admin_Widgets_Labsg_PatientVisitDetailReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $invoiced     = $fn->getReqParam('invoiced');

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

        $start_date = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        $end_date   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');

        if ($invoiced == 'Invoiced') {
            $invoicedVal = '';
        } else {
            $invoicedVal = $invoiced;
        }

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='3'>Summary</th>
            </thead>
            <tr>
                <td>Start Date : {$start_date}</td>
                <td>End Date : {$end_date}</td>
                <td>Invoiced : {$invoicedVal}</td>
            </tr>
        </table>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>S.No</th>
						<th>Visit Code</th>
						<th>Date</th>
                        <th>Time</th>
                        <th>Patient Name</th>
                        <th>Company</th>
                        <th>Gender</th>
                        <th>Passport/ID</th>
                        <th>DOB</th>
                        <th class='txtCenter'>Invoiced</th>
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

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $count = 1;

        foreach($this->model->dataArray as $row){
            $check_up_date = $dateUtil->formatDate($row['check_up_date'], 'DD-MM-YYYY');
            $dob = $dateUtil->formatDate($row['dob'], 'DD-MM-YYYY');

            $company = $row['company_name'];
            if ($row['company_name'] == '') {
                $company = 'Individual';
            }

            if($row['order_id']){
                $invoiced = "Yes";
            } else {
                $invoiced = "No";
            }

		    $rows .= "
			<tr>
				<td>{$count}</td>
				<td>{$row['visit_code']}</td>
				<td>{$check_up_date}</td>
				<td>{$row['check_up_time']}</td>
                <td>{$row['name']}</td>
                <td>{$company}</td>
                <td>{$row['gender']}</td>
                <td>{$row['registration_no']}</td>
                <td>{$dob}</td>
                <td class='txtCenter'>{$invoiced}</td>
			</tr>
			";
            $count++;
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}