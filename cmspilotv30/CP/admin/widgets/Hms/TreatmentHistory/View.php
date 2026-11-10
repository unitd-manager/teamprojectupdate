  <?
class CP_Admin_Widgets_Hms_TreatmentHistory_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Treatment Name</th>
					<th>% Used</th>
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
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $site_id  = $fn->getReqParam('site_id');

        $rows = '';

        foreach($this->model->dataArray as $row){
            $recCount = $fn->getRecordCount('treatment_visit', "");
            
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                if($site_id != ''){
                    $appendSql = "AND pv.site_id = {$site_id}";
                }
            }

            $SQLTreatmentVisit = "
            SELECT t.*
            FROM treatment_visit t
            LEFT JOIN patient_visit pv ON(pv.patient_visit_id = t.patient_visit_id)
            WHERE t.treatment_id = '{$row['treatment_id']}'
            AND pv.status != 'Cancelled'
            {$appendSql}
            ";

            $resultCountTreat = $db->sql_query($SQLTreatmentVisit);
            $recCountTreat    = $db->sql_numrows($resultCountTreat);

            //$recCountTreat = $fn->getRecordCount('treatment_visit', "treatment_id = '{$row['treatment_id']}'");

            $used = $recCountTreat/$recCount * 100;
            $used = number_format($used, 2);

			    $rows .= "
				<tr>
					<td>{$row['title']}</td>
					<td>{$used}%</td>
				</tr>
				";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}