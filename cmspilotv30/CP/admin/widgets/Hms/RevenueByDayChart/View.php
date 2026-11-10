<?
class CP_Admin_Widgets_Hms_RevenueByDayChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Revenue by Day (Current Month)</h2>
        <div class='tableOuter' id='revenue_by_day_div'>
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
                data.addColumn('string', 'Day');
                data.addColumn('number', 'Revenue');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('revenue_by_day_div'));
                chart.draw(data, {colors: ['#660066'], width: 768, height: 240, title: '',
                        hAxis: {title: 'Day', titleTextStyle: {color: 'black'}}
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
            $rows .= "['{$row['day']}', {$row['invoice_amount']}],";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}