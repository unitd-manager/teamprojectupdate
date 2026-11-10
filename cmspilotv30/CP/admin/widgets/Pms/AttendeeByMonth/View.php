<?
class CP_Admin_Widgets_Pms_AttendeeByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = $this->getAttendeeByCourse();

        if ($cpCfg['cp.forAceIms']) {
            $text .= $this->getInvoiceByMonth();
        }
        
        return $text;

        $text = "
        <h2>Attendees by Course</h2>
        <div class='tableOuter' id='chart_div'>
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
                data.addColumn('string', 'Course');
                data.addColumn('number', 'Attendee');
                data.addRows([
                  {$this->getRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
                chart.draw(data, {width: 900, height: 240, title: '',
                        hAxis: {title: 'Course', titleTextStyle: {color: 'red'}}
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
            $rows .= "['{$row['course_title']}', {$row['attendee_count']}],";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
     /**
     *
     */
    function getAttendeeByCourse(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $text = "
        <h2>Trainee by {$modulesArr['pms_course']['title']}</h2>
        <div class='tableOuter mb20' id='chart_div'>
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
                data.addColumn('string', 'Course');
                data.addColumn('number', 'Attendee');
                data.addRows([
                  {$this->getAttendeeByCourseRowsHTML()}
                ]);
                var options = {
                  'title':'Trainee by {$modulesArr['pms_course']['title']}',
                  'is3D':true,
                  'width':900,
                  'height':320
                }
                var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
                chart.draw(data, options);

            }
        </script>
        ";
         return $text;
    }
    /**
     *
     */

    function getInvoiceByMonth(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Invoice by Month</h2>
        <div class='tableOuter' id='chart_divInvoice'>
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
                  {$this->getInvoiceByMonthRowsHTML()}
                ]);

                var chart = new google.visualization.ColumnChart(document.getElementById('chart_divInvoice'));
                chart.draw(data, {width: 1000, height: 240, title: '',
                        hAxis: {title: 'Invoice by Month', titleTextStyle: {color: 'red'}}
                });

            }
        </script>
        ";
         return $text;
    }
    /**
     *
     */
    function getAttendeeByCourseRowsHTML() {
        $rows = '';

        //foreach($this->model->getAttendeeByMonthSQL as $row){
            //$rows .= "['{$row['course_title']}', {$row['attendee_count']}],";
        //}
        $rows1 = $this->model->getAttendeeByCourseSQL(); 
        foreach($rows1 as $row){
            $rows .= "['{$row['course_title']}', {$row['attendee_count']}],";
        }
        $text = "
        {$rows}
        ";

        return $text;
    }
    /**
     *
     */
    function getInvoiceByMonthRowsHTML() {
        $rows = '';

        //foreach($this->model->getAttendeeByMonthSQL as $row){
            //$rows .= "['{$row['course_title']}', {$row['attendee_count']}],";
        //}
        $rows1 = $this->model->getInvoiceByMonthSQL(); 
        foreach($rows1 as $row){
            $rows .= "['{$row['month']}', {$row['total']}],";
        }
        $text = "
        {$rows}
        ";

        return $text;
    }
    

}