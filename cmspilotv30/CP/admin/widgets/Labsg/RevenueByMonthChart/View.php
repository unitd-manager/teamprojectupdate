<?
class CP_Admin_Widgets_Labsg_RevenueByMonthChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Revenue by last 12 Months</h2>
        <div class='tableOuter' id='sales_by_month_div' style='width: 100%;'>
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
                data.addColumn('number', 'Revenue');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('sales_by_month_div'));
                chart.draw(data, {colors: ['#8B4513'], width: 768, height: 240, title: '',
                        hAxis: {title: 'Month', titleTextStyle: {color: 'black'}}
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
        $invoice_amount_monthly = 0;
        foreach($this->model->dataArray as $row){
            $invoice_amount_monthly = $row['total_invoice_amount'] - $row['total_discount'];
            $rows .= "['{$row['invoice_month']}', {$invoice_amount_monthly}],";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}