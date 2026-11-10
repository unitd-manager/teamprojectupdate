<?
class CP_Admin_Widgets_Tradingsg_EnquiryByMonthChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Enquiry By Month</h2>
        <div class='tableOuter' id='enquiry_by_month_div'>
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
                data.addColumn('number', 'Enquiry');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('enquiry_by_month_div'));
                chart.draw(data, {colors: ['#FF00FF'], width: 525, height: 240, title: '',
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
        $fn = Zend_Registry::get('fn');
        
        $rows = '';
        foreach($this->model->dataArray as $row){
            $month_year = $row['enquiry_month'];
            $rows .= "['{$month_year}', {$row['total_enquiry_monthly']}],";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}