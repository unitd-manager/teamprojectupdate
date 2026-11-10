<?
class CP_Admin_Widgets_Project_SalesByYear_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Sales by Year</h2>
        <div class='tableOuter' id='chart_sales_by_year'>
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
                data.addColumn('string', 'Year');
                data.addColumn('number', 'Sales');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('chart_sales_by_year'));
                chart.draw(data, {width: 500, height: 275, title: '',
                        hAxis: {title: 'Year', titleTextStyle: {color: 'red'}}
                });
            }
        </script>
        ";
        return $text;
    }

    function getRowsHTML() {
        $rows = '';
        foreach($this->model->dataArray as $row){
            $rows .= "['{$row['value1']}', {$row['value2']}],";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}