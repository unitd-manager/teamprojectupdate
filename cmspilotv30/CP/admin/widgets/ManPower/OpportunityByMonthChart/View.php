<?
class CP_Admin_Widgets_ManPower_OpportunityByMonthChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Opportunity by Last 12 Months</h2>
        <div class='tableOuter' id='opp_by_month_div'>
        </div>

        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
            <script type='text/javascript'>
                // Load the Visualization API and the piechart package.
                google.load('visualization', '1.0', {'packages':['corechart']});
                
                // Set a callback to run when the Google Visualization API is loaded.
                google.setOnLoadCallback(drawChart);
                
                // Callback that creates and populates a data table,
                // instantiates the pie chart, passes in the data and
                // draws it.
                function drawChart() {
                
                // Create the data table.
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Month');
                data.addColumn('number', 'Amount');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('opp_by_month_div'));
                chart.draw(data, {width: 400, height: 240, title: '',
                        hAxis: {title: 'Month', titleTextStyle: {color: 'red'}}
                });
            }
        </script>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        
        $rows = '';
        foreach($this->model->dataArray as $row){
            $month_year = $row['opportunity_month'] . ' -  ' . $row['opportunity_year'];
            $rows .= "['{$month_year}', {$row['total_estimated_value']}],";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}