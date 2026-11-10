<?
class CP_Admin_Widgets_Project_TaskHoursByStaffReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $status     = $fn->getReqParam('status');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $current_year = date('Y');
        $current_month = date('m');

        if ($start_date == '') {
            $start_date = $current_year . '-' . $current_month . '-' . '01';
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 

        $start_date  = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date    = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6'>Summary</th>
                </thead>
                <tr>
                    <td>Enquiry Start Date : {$start_date}</td>
                    <td>Enquiry End Date : {$end_date}</td>
                </tr>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Date</th>
                        <th>Moinudeen</th>
                        <th>Thamim</th>
                        <th>Arif</th>
                        <th>Ansari</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            </div>
            ";
        }

        return $text;
    }
    /**
     *
     */
    function gethours($date, $staff_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT SUM( hours ) AS staff_hrs
        FROM timesheet
        WHERE entry_date = '{$date}'
        AND staff_id = {$staff_id}
        GROUP BY entry_date
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        return $row['staff_hrs'];
    }
    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $text = '';
        $totalmoinhrs = 0;
        $totalthamimhrs = 0;
        $totalarifhrs = 0;
        $totalansarihrs = 0;
        $counter = 1;
        foreach($this->model->dataArray as $row){
            $record_date = $fn->getCPDate($row['record_date'],"d-m-Y");           
            $moinhrs = $this->gethours($row['record_date'], 16);
            $thamimhrs = $this->gethours($row['record_date'], 32);
            $arifhrs = $this->gethours($row['record_date'], 11);
            $ansarihrs = $this->gethours($row['record_date'], 39);
            $totalmoinhrs += $moinhrs;
            $totalthamimhrs += $thamimhrs;
            $totalarifhrs += $arifhrs;
            $totalansarihrs += $ansarihrs;
            $totalmoinhrs = number_format($totalmoinhrs, 2);
            $totalthamimhrs = number_format($totalthamimhrs, 2);
            $totalarifhrs = number_format($totalarifhrs, 2);
            $totalansarihrs = number_format($totalansarihrs, 2);
            $rows .= "
            <tr >
                <td>{$counter}</td>
                <td>{$record_date}</td>                
                <td>{$moinhrs}</td>
                <td>{$thamimhrs}</td>
                <td>{$arifhrs}</td>
                <td>{$ansarihrs}</td>

            </tr>


            ";
            $counter++;
        }
        
        $text = "
        {$rows}
        <tr bgcolor=\"#A9A9A9\">
            <th colspan='2' class='lastRowBgColor txtRight'>TOTAL HOURS</th>
            <th>{$totalmoinhrs}</th>
            <th>{$totalthamimhrs}</th>
            <th>{$totalarifhrs}</th>
            <th>{$totalansarihrs}</th>
        </tr>
        ";

        return $text;
    }
}