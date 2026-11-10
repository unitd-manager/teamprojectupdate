<?
class CP_Admin_Widgets_Hms_PatientVisitSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($tv['module'] == 'common_dashboard'){
            $heading = "Patient Visit Summary last 7 days";
        }else {
            $heading = "Patient Visit Summary";
        }
        $text = "
        <h2>{$heading}</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Date</th>
						<th>Day</th>
						<th>Dr In Charge</th>
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
        $fn = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $site_id        = $fn->getReqParam('site_id');
        $rows = '';
        $totalAppFixed = 0;
        $totalPatVisited = 0;
        $totalPatNotVisited = 0;
        $totalWalkIn = 0;
        $totalOverAll = 0;

        foreach($this->model->dataArray as $row){
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                if($site_id != ''){
                    $appendSql = "AND pv.site_id = {$site_id}";
                }
            }

            $SqlAppointment ="
            SELECT count(ev.patient_visit_id) AS patients_visited
            FROM employee_visit ev
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
            WHERE pv.record_type = 'By Appointment'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
              {$appendSql}
            ";
            $resultAppointment = $db->sql_query($SqlAppointment);
            $rowAp = $db->sql_fetchrow($resultAppointment);

            $SqlWalkIn ="
            SELECT count(ev.patient_visit_id) AS patients_walkin
            FROM employee_visit ev
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
            WHERE pv.record_type = 'Walk In'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
              {$appendSql}
            ";
            $resultWalkIn = $db->sql_query($SqlWalkIn);
            $rowWI = $db->sql_fetchrow($resultWalkIn);

        	$patient_not_visited = $row['appointment_fixed'] - $rowAp['patients_visited'];

            $total = $rowAp['patients_visited'] + $rowWI['patients_walkin'];

        	$SQL = "
			SELECT e.employee_name
			FROM employee e
        	LEFT JOIN (employee_visit ev) ON (ev.employee_id = e.employee_id)
        	LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
			WHERE pv.check_up_date = '{$row['check_up_date']}'
            AND pv.status != 'Cancelled'
            {$appendSql}
			GROUP BY e.employee_name
        	";
        	$result = $db->sql_query($SQL);

        	$employee_name = '';

        	while ($rowEM = $db->sql_fetchrow($result)) {
        		$employee_name .= $rowEM['employee_name'].', ';
        	}
        	$employee_name = rtrim($employee_name, ', ');
			$check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

		    $rows .= "
			<tr>
				<td>{$check_up_date}</td>
				<td>{$row['day']}</td>
				<td>{$employee_name}</td>
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
            <td class='txtRight lastRowBgColor' colspan='3'>Total</td>
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