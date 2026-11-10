<?
class CP_Admin_Widgets_ManPower_OpportunityPositionChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Oppurtunity Position Chart for the Month</h2>
        <div class='tableOuter' id='oppurtunity_position_chart_div'>
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
                data.addColumn('string', 'Position');
                data.addColumn('number', 'No of oppurtunity');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('oppurtunity_position_chart_div'));
                chart.draw(data, {colors: ['#5BA300'], width: 400, height: 240, title: '',
                        hAxis: {title: 'Position', titleTextStyle: {color: 'red'}}
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
        $db = Zend_Registry::get('db');

        $rows = '';
        foreach($this->model->dataArray as $row){
            $rows .= "['{$row['position']}', {$row['total_count_oppurtunity']}],";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}