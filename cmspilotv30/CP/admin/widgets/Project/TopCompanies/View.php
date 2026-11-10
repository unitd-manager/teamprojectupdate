<?
class CP_Admin_Widgets_Project_TopCompanies_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Best Revenue Companies</h2>
        <div class='tableOuter' id='chart_topCompanies'>
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
                data.addColumn('string', 'Company');
                data.addColumn('number', 'Revenue');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('chart_topCompanies'));
                chart.draw(data, {width: 500, height: 240, title: '',
                        hAxis: {title: 'Company', titleTextStyle: {color: 'red'}}
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

        /*foreach($this->model->dataArray as $row){
            $rows .= "['Company A', 60000],";
            $rows .= "['Company C', 50000],";
            $rows .= "['Company F', 40000],";
            $rows .= "['Company X', 25000],";
            $rows .= "['Company M', 15000],";
        }*/
        
        $rows = "
        ['Company A', 60000],
        ['Company C', 50000],
        ['Company F', 40000],
        ['Company X', 25000],
        ['Company M', 15000],
        ";

        $text = "
        {$rows}
        ";

        return $text;
    }

}