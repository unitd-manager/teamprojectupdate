<?
class CP_Admin_Widgets_Hms_TreatmentHistoryChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Treatment History</h2>
        <div class='tableOuter' id='piechart' style='height: 500px;'>
        </div>

        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
            <script type='text/javascript'>
                // Load the Visualization API and the piechart package.
                google.load('visualization', '1', {'packages':['corechart']});

                // Set a callback to run when the Google Visualization API is loaded.
                google.setOnLoadCallback(drawChart);

                // Callback that creates and populates a data table,
                // instantiates the pie chart, passes in the data and
                // draws it.
                function drawChart() {

                    // Create the data table.
                    var data = google.visualization.arrayToDataTable([
                        ['Task', 'Hours per Day'],
                      {$this->getRowsHTML()}
                    ]);

                    var options = {
                        title: 'Treatment History',
                        is3D: true,
                    };

                    var chart = new google.visualization.PieChart(document.getElementById('piechart'));
                    chart.draw(data, options);
                }

        </script>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $site_id = $fn->getSessionParam('cp_site_id');

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
            $rows .= "['{$row['title']}', {$used}],";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}