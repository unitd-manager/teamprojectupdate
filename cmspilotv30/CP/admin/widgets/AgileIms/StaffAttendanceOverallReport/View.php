<?
class CP_Admin_Widgets_AgileIms_StaffAttendanceOverallReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;
        
        $header = '';
        
        $month = $fn->getReqParam('month');
        if ($month != '') {
            $header .= "<th class='txtCenter'>Month</th>";
        }

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Staff Name</th>
                        {$header}
                        <th class='txtCenter'>No of Leave Days</th>
                        <th class='txtCenter'>Avg Time In</th>
                        <th class='txtCenter'>Avg Time Out</th>
                        <th class='txtCenter'>Attendance Percentage</th>
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
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';

        $serial_no = 0;
        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            
            $month = $fn->getReqParam('month');
            $year  = $fn->getReqParam('year');
            
            $SQLAppend = '';
            
            if ($year != ''){
                $startYear = $year .'-01-01'; 
                $endYear   = $year .'-12-31';
            
                $SQLAppend .= "AND sa.record_date BETWEEN '{$startYear}' AND '{$endYear}'";
            }
    
            if ($month != ''){
                if ($year != '') {
                    $startMonth = $year . '-' . $month . '-' . '01';
                    $endMonth   = $year . '-' . $month . '-' . '31';
                } else {
                    $year = date('Y');
                    $startMonth = $year . '-' . $month . '-' . '01';
                    $endMonth   = $year . '-' . $month . '-' . '31';
                }
                $SQLAppend .= "AND sa.record_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
            }
            
            $SQLLeave = "
            SELECT count(sa.staff_attendance_id) AS total_leave_days
            FROM staff_attendance sa
            WHERE sa.staff_id = {$row['staff_id']}
              AND sa.on_leave = 1
              {$SQLAppend}
            ";
            $resultLeave = $db->sql_query($SQLLeave);
            $rowLeave = $db->sql_fetchrow($resultLeave);
            
            switch ($fn->getReqParam('month')) {
                case '': $monthRow = '';
                break;

                case '01': $monthRow = "<td class='txtCenter'>January</td>";
                break;

                case '02': $monthRow = "<td class='txtCenter'>February</td>";
                break;

                case '03': $monthRow = "<td class='txtCenter'>March</td>";
                break;

                case '04': $monthRow = "<td class='txtCenter'>April</td>";
                break;

                case '05': $monthRow = "<td class='txtCenter'>May</td>";
                break;

                case '06': $monthRow = "<td class='txtCenter'>June</td>";
                break;

                case '07': $monthRow = "<td class='txtCenter'>July</td>";
                break;

                case '08': $monthRow = "<td class='txtCenter'>August</td>";
                break;

                case '09': $monthRow = "<td class='txtCenter'>September</td>";
                break;

                case '10': $monthRow = "<td class='txtCenter'>October</td>";
                break;

                case '11': $monthRow = "<td class='txtCenter'>November</td>";
                break;

                case '12': $monthRow = "<td class='txtCenter'>December</td>";
                break;
            }
            
            $attendance_percent = number_format(($row['total_present_days'] / $row['total_attendance_days']) * 100, 2);

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['staff_name']}</td>
                {$monthRow}
                <td class='txtCenter'>{$rowLeave['total_leave_days']}</td>
                <td class='txtCenter'>{$row['avg_time_in']}</td>
                <td class='txtCenter'>{$row['avg_leave_time']}</td>
                <td class='txtCenter'>{$attendance_percent}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}