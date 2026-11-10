  <?
class CP_Admin_Widgets_Labsg_TreatmentHistory_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = date('Y') . '-' . date('m') . '-';
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
                    <th>S.No</th>
					<th>Treatment Name</th>
					<th>Visits</th>
				</tr>
			</thead>
			<tbody>
				{$this->getRowsHTML()}
			</tbody>
		</table>
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');

        $site_id      = $fn->getSessionParam('cp_site_id');
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = date('Y') . '-' . date('m') . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $SQLTreatment = "
        SELECT t.*
        FROM `treatment` t
        ORDER BY t.treatment_id ASC
        ";
        $resultTreatment = $db->sql_query($SQLTreatment);
        $rows = '';
        $count = 1;
        //foreach($this->model->dataArray as $row){
        while ($row = $db->sql_fetchrow($resultTreatment)) {
            $recCount = $fn->getRecordCount('treatment_visit', "", array('includeSiteId' => false));
            
            $appendSqlPv = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlPv = "AND pv.site_id = {$site_id}";
            }
            $SQLTreatmentVisit = "
            SELECT t.*
            FROM treatment_visit t
            LEFT JOIN patient_visit pv ON(pv.patient_visit_id = t.patient_visit_id)
            WHERE t.treatment_id = '{$row['treatment_id']}'
              AND pv.status != 'Cancelled'
              AND pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
              {$appendSqlPv}
            ";
            $resultCountTreat = $db->sql_query($SQLTreatmentVisit);
            $recCountTreat    = $db->sql_numrows($resultCountTreat);

            //$recCountTreat = $fn->getRecordCount('treatment_visit', "treatment_id = '{$row['treatment_id']}'");

            $used = '0';
            if ($recCount) {
                $used = $recCountTreat/$recCount * 100;
            }
            $used = number_format($used, 2);

		    $rows .= "
			<tr>
                <td>{$count}</td>
				<td>{$row['title']}</td>
				<td>{$recCountTreat}</td>
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