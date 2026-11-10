<?
class CPL_Admin_Widgets_Project_InvoiceSummaryChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $duration       = $fn->getReqParam('duration');

        $durationArray = array(
            "Current Month"  => "Current Month"
           ,"Previous Month" => "Previous Month"
           ,"Last 3 Months"  => "Last 3 Months"
           ,"Last 6 Months"  => "Last 6 Months"
           ,"Last 9 Months"  => "Last 9 Months"
           ,"Last 12 Months" => "Last 12 Months"
        );
        if($duration == "") {
            $duration = "Current Month";
        }

        $text = "
        <h2 class='ui-widget-header ui-corner-top'>
            <div class='floatbox invoiceSummaryfilter'>
                <div class='float_left'>
                    Invoice Summary
                </div>
                <div class='float_right mb5 ml10'>
                    <select name='duration'>
                        {$cpUtil->getDropDownFromArr($durationArray, $duration)}
                    </select>
                </div>
            </div>
        </h2>
        <div class='tableOuter' id='piechart' style='height: 500px;'>
        </div>

        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
            <script type='text/javascript'>
                // Load the Visualization API and the piechart package.
                google.load('visualization', '1', {'packages':['corechart']});

                // Set a callback to run when the Google Visualization API is loaded.
                google.setOnLoadCallback(drawChart);

                // Callback that creates and populates a data table,
                // instantiates the pie chart, passes in the data and
                // draws it.
                function drawChart() {

                    // Create the data table.
                    var data = google.visualization.arrayToDataTable([
                        ['Invoice', 'Value'],
                      {$this->getRowsHTML()}
                    ]);

                    var options = {
                        title: 'Invoice Summary',
                        is3D: true,
                    };

                    var chart = new google.visualization.PieChart(document.getElementById('piechart'));
                    chart.draw(data, options);
                }

        </script>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $duration       = $fn->getReqParam('duration');

        $durationArray = array(
            "Current Month"  => "Current Month"
           ,"Previous Month" => "Previous Month"
           ,"Last 3 Months"  => "Last 3 Months"
           ,"Last 6 Months"  => "Last 6 Months"
           ,"Last 9 Months"  => "Last 9 Months"
           ,"Last 12 Months" => "Last 12 Months"
        );

        if($duration == "") {
            $month = "Current Month";
        }

        $rows = '';

        foreach($this->model->dataArray as $row){
            
        }

        if($duration == "Current Month"){
            $month = 'Current Month';
        }
        if($duration == "Previous Month"){
            $month = 'Previous Month';
        }
        if($duration == "Last 3 Months"){
            $month = 2;
        }
        if($duration == "Last 6 Months"){
            $month = 5;
        }
        if($duration == "Last 9 Months"){
            $month = 8;
        }
        if($duration == "Last 12 Months"){
            $month = 11;
        }
        $invoiceRaised = $this->model->getTotalInvoicesThisMonth($month);
        $invoicePaid = $this->model->getTotalInvoicesPaidThisMonth($month);
        $invoiceOutstanding = $this->model->getTotalOutstandingInvoices($month);

        if($invoiceRaised == ''){
            $invoiceRaised = 0;
        }
        if($invoicePaid == ''){
            $invoicePaid = 0;
        }
        if($invoiceOutstanding == ''){
            $invoiceOutstanding = 0;
        }
        $rows .= "['Total invoices raised', {$invoiceRaised}],";
        $rows .= "['Total invoices paid', {$invoicePaid}],";
        $rows .= "['Total outstanding invoices', {$invoiceOutstanding}],";

        $text = "
        {$rows}
        ";

        return $text;
    }

}