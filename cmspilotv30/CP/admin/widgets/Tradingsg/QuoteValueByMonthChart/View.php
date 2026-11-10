<?
class CP_Admin_Widgets_Tradingsg_QuoteValueByMonthChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Quote Value By Month</h2>
        <div class='tableOuter' id='quote_value_by_month_div'>
        </div>

        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
            <script type='text/javascript'>
                // Load the Visualization API and the piechart package.
                google.load('visualization', '1.0', {'packages':['corechart']});
                
                // Set a callback to run when the Google Visualization API is loaded.
                //google.setOnLoadCallback(drawChart);
                
                // Callback that creates and populates a data table,
                // instantiates the pie chart, passes in the data and
                // draws it.
                
                function drawChart() {
                
                // Create the data table.
                var data = new google.visualization.arrayToDataTable();
                data.addColumn('string', 'Month');
                data.addColumn('number', 'Values');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);
                
                //var data = google.visualization.arrayToDataTable([
                  //{$this->getRowsHTML()}
                //]);
                
                var options = {
                    title : 'Quote Value By Month',
                    width: 600,
                    height: 400,
                    vAxis: {title: 'Values'},
                    hAxis: {title: 'Month'},
                    seriesType: 'bars',
                    series: {2: {type: 'line'}}
                };

                var chart = new google.visualization.ComboChart(document.getElementById('quote_value_by_month_div'));
                chart.draw(data, options);
            }
            google.setOnLoadCallback(drawChart);
        </script>
        <body style='font-family: Arial;border: 0 none;'>
            <div id='quote_value_by_month_div' style='width: 400px; height: 240px;'></div>
        </body>
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
        $rows1 = '';

        /*$SQL = "
        SELECT DATE_FORMAT(q.quote_date, '%M') AS quote_value_month
        ,(SUM( qp.selling_price * qp.qty )) AS confirmed_selling_price
        FROM quote_product qp
        LEFT JOIN quote q ON ( q.quote_id = qp.quote_id )
        WHERE q.status = 'Customer Confirmed'
        ";
        $result = $db->sql_query($SQL);
        $rowPrice = $db->sql_fetchrow($result);*/

        foreach($this->model->dataArray as $row){
            $month_year = $row['quote_value_month'];
            $rows .= "['{$month_year}', {$row['total_selling_price']}],";
            $rows1 .= "['{$month_year}', {$row['confirmed_selling_price']}],";
        }
        
        $text = "
        {$rows}
        {$rows1}
        ";

        return $text;
    }

    /**
     *
     */
    function getRowsHTML1() {
        $fn = Zend_Registry::get('fn');
        
        $rows1 = '';
        foreach($this->model->dataArray as $row){
            $month_year = $row['quote_value_month'];
            $rows1 .= "['{$month_year}', {$row['confirmed_selling_price']}],";
        }
        
        $text = "
        {$rows1}
        ";

        return $text;
    }
}