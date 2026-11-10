<?
class CP_Admin_Widgets_ManPower_MarketingCallHistoryTodayChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Marketing Call History for Today</h2>
        <div class='tableOuter' id='marketing_call_status_today_div'>
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
                data.addColumn('string', 'Staff Name');
                data.addColumn('number', 'No of calls');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('marketing_call_status_today_div'));
                chart.draw(data, {colors: ['#5BA300'], width: 500, height: 240, title: '',
                        hAxis: {title: 'Staff Name', titleTextStyle: {color: 'red'}}
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
            $rows .= "['{$row['staff_name']}', {$row['total_count_status']}],";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_marketingCallHistoryTodayChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}