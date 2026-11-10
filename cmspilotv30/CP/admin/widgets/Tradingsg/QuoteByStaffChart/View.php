<?
class CP_Admin_Widgets_Tradingsg_QuoteByStaffChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Quote By Staff</h2>
        <div class='tableOuter' id='quote_by_staff_div'>
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
                data.addColumn('string', 'Staff');
                data.addColumn('number', 'Quote');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('quote_by_staff_div'));
                chart.draw(data, {colors: ['#FF00FF'], width: 800, height: 340, title: '',
                        hAxis: {title: 'Staff', titleTextStyle: {color: 'black'}}
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
            $staff_name = $row['staff_name'];
            $rows .= "['{$staff_name}', {$row['total_quote_monthly']}],";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}