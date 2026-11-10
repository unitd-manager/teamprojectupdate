<?
class CP_Admin_Widgets_Tradingsg_InvoiceChartByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Invoice Chart for Last 12 Months</h2>
        <div class='tableOuter' id='invoice_chart_by_month_div'>
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
                data.addColumn('number', 'Invoice');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('invoice_chart_by_month_div'));
                chart.draw(data, {colors: ['#8A2BE2'], width: 525, height: 240, title: '',
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
        
        $rows = '';
        foreach($this->model->dataArray as $row){
            $month_year = $row['invoice_month'];

            if ($row['invoice_amount_monthly'] > 0) {
            	$rows .= "['{$month_year}', {$row['invoice_amount_monthly']}],";
            }
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}