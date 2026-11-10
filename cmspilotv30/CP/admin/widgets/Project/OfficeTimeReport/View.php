<?
class CP_Admin_Widgets_Project_OfficeTimeReport_View extends CP_Common_Lib_WidgetViewAbstract
{

    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $text = '';
        $rowsHTML = $this->getRowsHTML();
        //$staff_id = $_SESSION['staff_id'];

        $current_date = date('Y-m-d');
        $current_year = date('Y');
        $current_month = date('m');

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $staff_id       = $fn->getReqParam('staff_id');

        if ($start_date != '' && $end_date == '') {
            $appendSql = "AND a.record_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $appendSql = "AND a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql = "AND a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $current_year . '-' . $current_month . '-' . '01';
            $end_date = $current_year . '-' . $current_month . '-' . '31';
            $appendSql = "AND a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $start_date_year = $current_year . "-01-01";
        $end_date_year   = $current_year . "-12-31";

        $SQL = "SELECT DISTINCT a.staff_id
               ,a.time_in
               ,a.leave_time
               ,a.productive_level
               ,a.check_task_level
               ,a.late_level
               ,a.on_leave
               ,(
                 SELECT count(a.on_leave)
                 FROM attendance a
                   WHERE a.staff_id = {$staff_id}
                   AND a.type_of_leave != 'Holiday'
                   AND a.on_leave = 1
                   {$appendSql}
                ) AS total_leave_days
             ,(
               SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(a.time_in)))
               FROM attendance a
               WHERE a.staff_id = {$staff_id}
                 {$appendSql}
                 AND (a.on_leave IS NULL
                 OR a.on_leave = 0)
             ) AS avg_time_in
             ,(
               SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(a.leave_time)))
               FROM attendance a
               WHERE a.staff_id = {$staff_id}
                 {$appendSql}
                 AND (a.on_leave IS NULL
                 OR a.on_leave = 0)
             ) AS avg_leave_time
             ,(
               SELECT AVG(a.productive_level)
               FROM attendance a
               WHERE a.staff_id = {$staff_id}
                 {$appendSql}
                 AND (a.on_leave IS NULL
                 OR a.on_leave = 0)
             ) AS avg_productive_level
             ,(
               SELECT AVG(a.check_task_level)
               FROM attendance a
               WHERE a.staff_id = {$staff_id}
                 {$appendSql}
                 AND (a.on_leave IS NULL
                 OR a.on_leave = 0)
             ) AS avg_check_task_level
             ,(
               SELECT AVG(a.late_level)
               FROM attendance a
               WHERE a.staff_id = {$staff_id}
                 {$appendSql}
                 AND (a.on_leave IS NULL
                 OR a.on_leave = 0)
             ) AS avg_late_level
             ,(
               SELECT count(a.on_leave)
               FROM attendance a
               WHERE a.staff_id = {$staff_id}
                 AND a.type_of_leave = 'Personal Leave'
                 {$appendSql}
                 AND a.on_leave = 1
             ) AS personal_leave
             ,(
               SELECT count(a.on_leave)
               FROM attendance a
               WHERE a.staff_id = {$staff_id}
                 AND a.type_of_leave = 'Sick Leave'
                 {$appendSql}
                 AND a.on_leave = 1
             ) AS sick_leave
             ,(
               SELECT count(a.on_leave)
               FROM attendance a
               WHERE a.staff_id = {$staff_id}
                 AND a.type_of_leave = 'Holiday'
                 {$appendSql}
                 AND a.on_leave = 1
             ) AS holidays
        FROM attendance a
        WHERE a.staff_id = {$staff_id}
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        if($row['staff_id'] == 11) {
            $currency = 'SG$';
        } else {
            $currency = 'Rs';
        }
        $avg_productive_level='';
        $avg_check_task_level='';
        $avg_late_level      ='';

        if ($rowsHTML != ""){
            $avg_productive_level= number_format($row['avg_productive_level'],1);
            $avg_check_task_level= number_format($row['avg_check_task_level'],1);
            $avg_late_level      = number_format($row['avg_late_level'],1);
            $text = "
            <table class='thinlist summaryTable mb20'>
                <thead>
                    <th colspan='9'>Summary</th>
                </thead>
                <tr>
                    <td><u>Total Leave Days</u> : {$row['total_leave_days']}</td>
                    <td><u>Personal Leave </u>: {$row['personal_leave']}</td>
                    <td><u>Sick Leave </u>: {$row['sick_leave']}</td>
                    <td><u>Holidays</u> : {$row['holidays']}</td>
                    <td><u>Avg TI</u> : {$row['avg_time_in']}</td>
                    <td><u>Avg TO</u> : {$row['avg_leave_time']}</td>
                    <td><u>Avg Task Listing</u> :{$avg_productive_level} </td>
                    <td><u>Avg Check Task </u> : {$avg_check_task_level}</td>
                    <td><u>Avg Update Task</u> : {$avg_late_level}</td>
                </tr>
            </table>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Staff Name</th>
                        <th>Date</th>
                        <th>Leave Taken</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Task Listing</th>
                        <th>Check Task Level</th>
                        <th>Update Task</th>
                        <th class='txtRight'>Time In Points</th>
                        <th class='txtRight'>Time Out Points</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }
    /**
     *
     */

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $total = '';
        $currency = '';
        $serial_no = 0;

        define("SECONDS_PER_HOUR", 60*60);
        $totalPointMinutesIn  = 0;
        $totalPointMinutesOut = 0;
        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            $amount = 0;

            $time_in = strtotime($row['time_in']);
            $leave_time = strtotime($row['leave_time']);

            $hour = date('H', $time_in);
            $mins = date('i', $time_in);

            if ($row['on_leave'] == 0) {
                if($row['staff_id'] == 11) {
                    if ($hour == '10') {
                        if ($mins <= '15') {
                            $amount = 0;
                        } else {
                            $amount = 20;
                        }
                    } else if ($hour >= '11'){
                        $amount = 40;
                    }
                } else {
                    if ($hour == '10') {
                        if ($mins > '30') {
                            $amount = 50;
                        }

                        if ($mins > '45'){
                            $amount = 100;
                        }

                    }
                    else if ($hour >= '11'){
                        $amount = 100;
                    }
                }
            }

            $pointsMinutesIn  = 0;
            $pointsMinutesOut = 0;
            if ($row['on_leave'] == 0) {
                $datetimeIn1     = strtotime("{$row['record_date']} 10:00:00");
                $datetimeIn2     = strtotime("{$row['record_date']} {$row['time_in']}");
                $intervalIn      = $datetimeIn2 - $datetimeIn1;
                $pointsMinutesIn = floor($intervalIn / 60);
                $pointsMinutesIn = $pointsMinutesIn * 4;

                $datetimeOut1     = strtotime("{$row['record_date']} {$row['leave_time']}");
                $datetimeOut2     = strtotime("{$row['record_date']} 18:45:00");

                $Checkdate = strtotime($row['record_date']);
                if (date('l', $Checkdate) == 'Saturday'){
                    $datetimeOut2     = strtotime("{$row['record_date']} 17:30:00");
                }

                $intervalOut      = $datetimeOut2 - $datetimeOut1;
                $pointsMinutesOut = floor($intervalOut / 60);
                $pointsMinutesOut = $pointsMinutesOut * 4;


                if($pointsMinutesOut < 0){
                    $pointsMinutesOut = 0;
                }
            }

            $totalPointMinutesIn  += $pointsMinutesIn;
            $totalPointMinutesOut += $pointsMinutesOut;

            $attendance_date = $dateUtil->formatDate($row['record_date'], 'DD-MM-YYYY');
            $on_leave = ($row['on_leave'] == 1) ? "Yes" : "No";

            if($row['staff_id'] == 11) {
                $currency = 'SG$';
            } else {
                $currency = 'Rs';
            }

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['staff_name']}</td>
                <td>{$attendance_date}</td>
                <td>{$on_leave}</td>
                <td>{$row['time_in']}</td>
                <td>{$row['leave_time']}</td>
                <td>{$row['productive_level']}</td>
                <td>{$row['check_task_level']}</td>
                <td>{$row['late_level']}</td>
                <td class='txtRight'>{$pointsMinutesIn}</td>
                <td class='txtRight'>{$pointsMinutesOut}</td>
                <td>{$row['notes']}</td>
            </tr>
            ";
            $total += $amount;
        }

        $total = number_format($total, 2);

        $text = "
        {$rows}
        <tr>
            <td colspan='9' class='lastRowBgColor txtRight'>Total ($currency)</td>
            <td class='txtRight lastRowBgColor'>{$totalPointMinutesIn}</td>
            <td class='txtRight lastRowBgColor'>{$totalPointMinutesOut}</td>
            <td class='txtRight lastRowBgColor'></td>
        </tr>
        ";

        return $text;
    }
}