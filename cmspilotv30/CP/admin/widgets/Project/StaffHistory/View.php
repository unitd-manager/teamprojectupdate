<?
class CP_Admin_Widgets_Project_StaffHistory_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     * Leave Days | Time In (Avg) | Time Out (Avg)
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $staff_id = $_SESSION['staff_id'];
        $totalAmt = '';
        $total = '';
        $currency = '';
        
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        $current_month = date('m');
        
        $start_date  = $current_year . '-' . $current_month . '-' . '01';
        $end_date    = $current_year . '-' . $current_month . '-' . '31';

        $start_date_year = $current_year . "-01-01";
        $end_date_year   = $current_year . "-12-31";
        
        $history_id = $cpCfg['cp.attendanceTable'] . '_id';

        $threeMonthBefore = date('Y-m-d', mktime (0,0,0,date('m')-3,date('d'), date('Y')));

        $SQL = "SELECT DISTINCT a.staff_id
               ,a.time_in
               ,a.leave_time
               ,a.on_leave
               ,(
                 SELECT a.record_date
                 FROM {$cpCfg['cp.attendanceTable']} a
                   WHERE a.staff_id = {$staff_id}
                   AND a.on_leave = 0
                   AND month(a.record_date) BETWEEN '{$start_date}' AND '{$end_date}'
                ) as current_month 
               ,(
                 SELECT count(a.type_of_leave) 
                 FROM {$cpCfg['cp.attendanceTable']} a
                   WHERE a.staff_id = {$staff_id}
                   AND a.type_of_leave != 'Holiday'
                   AND a.on_leave = 1
                   AND a.record_date BETWEEN '{$start_date_year}' AND '{$end_date_year}'
                ) as total_leave_days 
             ,(
               SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(a.time_in)))
               FROM {$cpCfg['cp.attendanceTable']} a
               WHERE a.staff_id = {$staff_id}
                 AND a.record_date BETWEEN '{$start_date}' AND '{$current_date}'
                 AND a.on_leave IS NULL
             ) AS avg_time_in
             ,(
               SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(a.leave_time)))
               FROM {$cpCfg['cp.attendanceTable']} a
               WHERE a.staff_id = {$staff_id}
                 AND a.record_date BETWEEN '{$start_date}' AND '{$current_date}'
                 AND a.on_leave IS NULL
             ) AS avg_leave_time
             ,(
               SELECT count(a.type_of_leave) 
               FROM {$cpCfg['cp.attendanceTable']} a
               WHERE a.staff_id = {$staff_id}
                 AND a.type_of_leave = 'Personal Leave'
                 AND a.record_date BETWEEN '{$start_date_year}' AND '{$end_date_year}'
                 AND a.on_leave = 1
             ) AS personal_leave
             ,(
               SELECT count(a.type_of_leave) 
               FROM {$cpCfg['cp.attendanceTable']} a
               WHERE a.staff_id = {$staff_id}
                 AND a.type_of_leave = 'Sick Leave'
                 AND a.record_date BETWEEN '{$start_date_year}' AND '{$end_date_year}'
                 AND a.on_leave = 1
             ) AS sick_leave
        FROM {$cpCfg['cp.attendanceTable']} a
        WHERE a.staff_id = {$staff_id}
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);             

        $SQLAmt = "SELECT DISTINCT a.staff_id
               ,a.time_in
               ,a.leave_time
               ,a.on_leave
               ,a.record_date
        FROM {$cpCfg['cp.attendanceTable']} a
        WHERE a.record_date BETWEEN '{$start_date}' AND '{$end_date}'
        AND a.staff_id = {$staff_id}
        ";
        $resultAmt  = $db->sql_query($SQLAmt);
        
        $totalPointMinutesIn  = 0;  
        $totalPointMinutesOut = 0;   
        
        while($row1 = $db->sql_fetchrow($resultAmt)){
            $amount = 0;

            $time_in = strtotime($row1['time_in']);
            $leave_time = strtotime($row1['leave_time']);
            
            $hour = date('H', $time_in);
            $mins = date('i', $time_in);
            
            $amount = 0;
            if ($row1['on_leave'] == 0) {
                if($row1['staff_id'] == 11) {
                    if ($hour == '10') {
                        if ($mins <= '30') {
                            $amount = 0;
                        } else {
                            $amount = 20;
                        }
                    } else if ($hour >= '11'){
                        $amount = 40;
                    }
                } else {
                    /*if ($hour == '10') {
                        if ($mins <= '45') {
                            $amount = 0;
                        } else {
                            $amount = 50;
                        }
                    } else if ($hour >= '11'){
                        $amount = 100;
                    }*/

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
            if ($row1['on_leave'] == 0) {
                $datetimeIn1     = strtotime("{$row1['record_date']} 10:00:00");
                $datetimeIn2     = strtotime("{$row1['record_date']} {$row1['time_in']}");
                $intervalIn      = $datetimeIn2 - $datetimeIn1;
                $pointsMinutesIn = floor($intervalIn / 60);
                $pointsMinutesIn = $pointsMinutesIn * 4;

                $datetimeOut1     = strtotime("{$row1['record_date']} {$row1['leave_time']}");
                $datetimeOut2     = strtotime("{$row1['record_date']} 18:45:00");
                
                $Checkdate = strtotime($row1['record_date']);
                if (date('l', $Checkdate) == 'Saturday'){
                    $datetimeOut2     = strtotime("{$row1['record_date']} 17:30:00");
                }

                $intervalOut      = $datetimeOut2 - $datetimeOut1;
                $pointsMinutesOut = floor($intervalOut / 60);
                $pointsMinutesOut = $pointsMinutesOut * 4;


                if($pointsMinutesOut < 0 || $row1['record_date'] == date('Y-m-d')){
                    $pointsMinutesOut = 0;
                }
            }

            $totalPointMinutesIn  += $pointsMinutesIn;
            $totalPointMinutesOut += $pointsMinutesOut;                       

            $totalAmt .= "
            {$amount}
            ";
            $total += $amount;
            
            if($row1['staff_id'] == 11) {
                $currency = 'SG$';
            } else {
                $currency = 'Rs';
            }
        }

        $text = "
        <h2 class='floatbox pbt10 ui-widget-header ui-corner-top'>
            Staff History
        </h2>
        <div class='tableOuter scroll-pane'>    
            <div class='floatbox p10'>
                <div class='float_left'>Total Leave Days : </div>
                <div class='float_left'>{$row['total_leave_days']}</div>

                <div class='float_left'>Personal Leave : </div>
                <div class='float_left'>{$row['personal_leave']}</div>

                <div class='float_left'>Sick Leave : </div>
                <div class='float_left'>{$row['sick_leave']}</div>

                <div class='float_left'>Amount : </div>
                <div class='float_left'>{$currency} {$total}</div>

                <div class='float_left ml20'>Time In (Avg) : </div>
                <div class='float_left'>{$row['avg_time_in']}</div>

                <div class='float_left ml20'>Time Out (Avg) : </div>
                <div class='float_left'>{$row['avg_leave_time']}</div>

                <div class='float_left'>Time In Points : </div>
                <div class='float_left'>{$totalPointMinutesIn}</div>

                <div class='float_left'>Time Out Points : </div>
                <div class='float_left'>{$totalPointMinutesOut}</div>
            </div>
        </div>
        ";

        return $text;
    }
}