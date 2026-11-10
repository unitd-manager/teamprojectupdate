<?
class CP_Admin_Widgets_Labsg_PatientVisitSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
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

            if ($month == '') {
                $month = date('m');
            }

            $month_year = $year . '-' . $month . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');

        if($tv['module'] == 'common_dashboard'){
            $heading = "Patient Visit Summary - Current Month";
            $summaryTable = '';
        }else {
            $heading = "";
            $summaryTable = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='2'>Summary</th>
                </thead>
                <tr>
                    <td>Start Date : {$start_date_formatted}</td>
                    <td>End Date : {$end_date_formatted}</td>
                </tr>
            </table>
            ";
        }

        $text = "
        <h2>{$heading}</h2>
        {$summaryTable}
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Date</th>
						<th>Day</th>
						<th>No of Fixed Appoinment</th>
						<th>Turn Up Patient</th>
						<th>Did Not Turn Up</th>
                        <th>Walk In Patient</th>
                        <th>Total</th>
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
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $site_id = $fn->getSessionParam('cp_site_id');
        
        $rows = '';
        $totalAppFixed = 0;
        $totalPatVisited = 0;
        $totalPatNotVisited = 0;
        $totalWalkIn = 0;
        $totalOverAll = 0;

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND pv.site_id = {$site_id}";
        }

        foreach($this->model->dataArray as $row){
            $SqlAppointment ="
            SELECT count(pv.patient_visit_id) AS patients_visited
            FROM patient_visit pv
            WHERE pv.record_type = 'By Appointment'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
              {$appendSql}
            ";
            $resultAppointment = $db->sql_query($SqlAppointment);
            $rowAp = $db->sql_fetchrow($resultAppointment);

            $SqlWalkIn ="
            SELECT count(pv.patient_visit_id) AS patients_walkin
            FROM patient_visit pv
            WHERE pv.record_type = 'Walk In'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
              {$appendSql}
            ";
            $resultWalkIn = $db->sql_query($SqlWalkIn);
            $rowWI = $db->sql_fetchrow($resultWalkIn);

        	$patient_not_visited = $row['appointment_fixed'] - $rowAp['patients_visited'];

            $total = $rowAp['patients_visited'] + $rowWI['patients_walkin'];

			$check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

		    $rows .= "
			<tr>
				<td>{$check_up_date}</td>
				<td>{$row['day']}</td>
				<td>{$row['appointment_fixed']}</td>
				<td>{$rowAp['patients_visited']}</td>
				<td>{$patient_not_visited}</td>
                <td>{$rowWI['patients_walkin']}</td>
                <td>{$total}</td>
			</tr>
			";
            $totalAppFixed += $row['appointment_fixed'];
            $totalPatVisited += $rowAp['patients_visited'];
            $totalPatNotVisited += $patient_not_visited;
            $totalWalkIn += $rowWI['patients_walkin'];
            $totalOverAll += $total;
        }

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='2'>Total</td>
            <td class='lastRowBgColor'>{$totalAppFixed}</td>
            <td class='lastRowBgColor'>{$totalPatVisited}</td>
            <td class='lastRowBgColor'>{$totalPatNotVisited}</td>
            <td class='lastRowBgColor'>{$totalWalkIn}</td>
            <td class='lastRowBgColor'>{$totalOverAll}</td>
        </tr>
        ";

        return $text;
    }
}