<?
class CP_Admin_Widgets_Project_BottomCompanies_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Least Revenue Companies</h2>
        <div class='tableOuter' id='chart_bottomCompanies'>
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

                var chart = new google.visualization.ColumnChart(document.getElementById('chart_bottomCompanies'));
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
            $rows .= "['Company A', 4000],";
            $rows .= "['Company C', 4500],";
            $rows .= "['Company F', 7000],";
            $rows .= "['Company X', 7800],";
            $rows .= "['Company M', 9000],";
        }*/
        
        $rows = "
        ['Company G', 4000],
        ['Company D', 4500],
        ['Company V', 7000],
        ['Company S', 7800],
        ['Company T', 9000],
        ";
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}