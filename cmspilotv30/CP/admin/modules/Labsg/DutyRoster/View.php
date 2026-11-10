<?
class CP_Admin_Modules_Labsg_DutyRoster_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('fullcalendar-1.6.4', 'jqUITimePickerAddon-0.9.3');
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $count   = 0;
        $rows    = '';

        $sqlEmployee = "
        SELECT employee_id
              ,employee_name AS employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $printPdfLink = "<a href='#' class='btn btn-info printDutyRosterLink'><span class='fa-print'></span>Print PDF</a>";

        $addAppointmentLink = "<a href='#' class='btn btn-info addAppointmentLinkBtn'><span class='fa-plus'></span>Add Duty Roster</a>";
        $text = "
        <div class='dutyRosterCalendarView'>
            <div class='floatbox'>
                <div class='doctorFilter float_left'>
                    {$formObj->getDDRowBySQL('Doctor/Nurse : ', 'employee_id', $sqlEmployee,'')}
                </div>
                <div class='float_right'>
                    {$addAppointmentLink}
                    {$printPdfLink}
                </div>
            </div>
            {$this->getDutyRosterCalendarView()}
        </div>
        <div class='dutyRosterCalendarViewRight'>
            {$this->getDoctorDetails()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDutyRosterForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_topRm=main&module=labsg_dutyRoster&_spAction=printDutyRosterFormSubmit&showHTML=0";

        $sqlEmployee = "
        SELECT employee_id
              ,employee_name AS employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $PreviousYear = date("Y") - 1;
        $currentYear  = date("Y");
        $nextYear     = date("Y") + 1;
        
        $currentMonth = date("m");

        $yearArray = array( $PreviousYear
                          , $currentYear
                          , $nextYear
                     );

        $exp = array(
            'hideFirstOption' => true
        );

        $expmonth = array(
            'hideFirstOption' => true,
            'useKey' => true
        );

        $monthArray = array(
                         1 => 'January'
                        ,2 => 'February'
                        ,3 => 'March'
                        ,4 => 'April'
                        ,5 => 'May'
                        ,6 => 'June'
                        ,7 => 'July'
                        ,8 => 'August'
                        ,9 => 'September'
                        ,10 => 'October'
                        ,11 => 'November'
                        ,12 => 'December'
                      );


        $text = "
        <form id='portalFormPrintDutyRoster' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDropDownRowByArray('Year', 'duty_year', $yearArray, $currentYear, $exp)}
                {$formObj->getDropDownRowByArray('Month', 'duty_Month', $monthArray, $currentMonth, $expmonth)}
                {$formObj->getDDRowBySQL('Doctor/Nurse', 'employee_id_pdf', $sqlEmployee,'')}
                <input type='hidden' name='patient_information_id' value=''>
            </table>
        </form>
        ";

        return $text;
    }


    /**
     *
     */
    function getPrintDutyRosterPdf(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $year            = $fn->getReqParam('year');
        $month           = $fn->getReqParam('month');
        $employee_id     = $fn->getReqParam('employee_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $appendSqlDr = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlDr = "AND site_id = {$cpSiteIdSession}";
        }

        $employee_id_condition = '';
        if($employee_id != ''){
            $employee_id_condition = "AND employment_id = {$employee_id}"; 
        }

        $SQLTimings = "
        SELECT CONCAT_WS(' - ', LOWER(DATE_FORMAT(work_from_time, '%l:%i %p'))
             , LOWER(DATE_FORMAT(work_to_time, '%l:%i %p'))) AS work_time
        FROM `duty_roster`
        WHERE DATE_FORMAT(work_date, '%Y-%m') = '{$year}-{$month}'
        {$employee_id_condition}
        {$appendSqlDr}
        GROUP BY work_time
        ORDER BY work_from_time ASC
        ";
        $resultTimings  = $db->sql_query($SQLTimings);
        $numRowsTimings = $db->sql_numrows($resultTimings);

        $tbl1 ='<table border="1" width="100%" cellpadding="4" style="font-size:15px;">
                    <thead>
                        <tr style="text-align:center;font-weight:bold;">
                            <th rowspan="2">DATE</th>
                            <th rowspan="2">DAY</th>
                            <th colspan="'.$numRowsTimings.'">BRANCH NAME</th>
                        </tr>
                ';

        $tbl1 = $tbl1.'<tr style="text-align:center;font-weight:bold;">
                      ';
        while ($rowTimings = $db->sql_fetchrow($resultTimings)) {
            $tbl1 = $tbl1.'<th>'.$rowTimings['work_time'].'</th>';

        }
        
         $tbl1 = $tbl1.'</tr>
                     ';

        $tbl1 = $tbl1.'</thead><tbody>
                    ';

        $SQLDetails = "
        SELECT DATE_FORMAT(work_date, '%W') AS DayName
              ,DATE_FORMAT(work_date, '%d/%m/%Y') AS work_date_formatted
              ,work_date
        FROM `duty_roster`
        WHERE DATE_FORMAT(work_date, '%Y-%m') = '{$year}-{$month}'
        {$employee_id_condition}
        {$appendSqlDr}
        GROUP BY work_date
        ORDER BY work_date ASC
        ";
        $resultDetails  = $db->sql_query($SQLDetails);
        $numRowsDetails = $db->sql_numrows($resultDetails);

        while ($rowDetails = $db->sql_fetchrow($resultDetails)) {
            $tbl1 = $tbl1.'<tr style="text-align:center;">
                            <td>'.$rowDetails['work_date_formatted'].'</td>
                            <td style="font-weight:bold;">'.$rowDetails['DayName'].'</td>
                            ';

            $employee_id_condition_inner = '';
            if($employee_id != ''){
                $employee_id_condition_inner = "AND d.employment_id = {$employee_id}"; 
            }

            $appendSqlDr = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlDr = "AND d.site_id = {$cpSiteIdSession}";
            }

            $SQLDname = "
            SELECT employee_name AS employee_name
                  ,d.work_from_time
                  ,d.work_to_time
                  ,d.work_date
            FROM `duty_roster` d
            LEFT JOIN employee e ON (e.employee_id = d.employment_id)
            WHERE d.work_date = '{$rowDetails['work_date']}'
            {$employee_id_condition_inner}
            {$appendSqlDr}
            ORDER BY d.work_date, d.work_from_time ASC
            ";
            $resultDname  = $db->sql_query($SQLDname);
            $numRowsDname = $db->sql_numrows($resultDname);
            $from_duty_time = '';
            $to_duty_time = '';
            $count = 1;
            while ($rowDname = $db->sql_fetchrow($resultDname)) {                                
                if($from_duty_time != $rowDname['work_from_time'] && $to_duty_time != $rowDname['work_to_time']){
                    $employee_name = $rowDname['employee_name'];
                }else{
                    $employee_name .= '/'.$rowDname['employee_name'];
                }

                $SameRecCount = $fn->getRecordCount('duty_roster', "work_from_time = '{$rowDname['work_from_time']}' AND work_to_time = '{$rowDname['work_to_time']}' AND work_date = '{$rowDname['work_date']}'");
                
                $endTda = '';
                if($SameRecCount ==  $count){
                    $count = 0;
                    $tbl1 = $tbl1.'<td>'.$employee_name.'</td>';
                }

                $from_duty_time = $rowDname['work_from_time'];
                $to_duty_time   = $rowDname['work_to_time'];

                $count++;
            }

            $tbl1 = $tbl1.'</tr>';

        }
        
        $tbl1 = $tbl1.'</tbody></table>
                     ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $download_title = 'Duty Roster.pdf';
        $pdf->Output($download_title, 'I');
    }


    /**
     *
     */
    function getDutyRosterCalendarView() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $text = "
        <div id='{$c->handle}' class='{$c->cssClass}'>
        </div>
        ";

        $headerObj = "
        {
             left: '{$c->headerLeft}'
            ,center: '{$c->headerCenter}'
            ,right: '{$c->headerRight}'
        }
        ";
            
        $timeFormatObj = "{
             {$c->monthTimeFormat}
            ,{$c->genTimeFormat}
            }
        ";
            
        $minTime = $c->minTime;
        $maxTime = $c->maxTime;
            
        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,eventAction: '{$c->eventAction}'
                ,headerObj: $headerObj
                ,timeFormatObj: $timeFormatObj
                ,minTime: $minTime
                ,maxTime: $maxTime
            }
            cpm.labsg.dutyRoster.run(exp);
        "));

        
        $text = "
        <div id='{$c->handle}'></div>
        ";
        return $text;
    }


    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getAddDutyRosterDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $work_date      = $fn->getReqParam('work_date');
        $work_from_time = $fn->getReqParam('work_from_time');
        
        $formAction = "index.php?_topRm=main&module=labsg_dutyRoster&_spAction=addDutyRosterDetailsSubmit&showHTML=0";
        
        $sqlEmployee = "
        SELECT employee_id
              ,employee_name AS employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $categoryTypeArr = array('Daily', 'Weekly', 'Monthly');
        $dailyTypeArr    = array('Sunday', 'Saturday & Sunday');
        $weekTypeArr     = array('Mon', 'Tue', 'Wed', 'Thur', 'Fri', 'Sat', 'Sun');
        $create_monthArr = array('1 Month', '3 Months', '6 Months');
        $create_monthArrdefault = '1 Month';
        $expPlus ="<div class='addTimeinOutLine'>
                        <img src='" . CP_LOCAL_PATH_ALIAS . "images/plus-512.png' />
                    </div>
        ";
        
        $text = "
        <form id='portalFormDutyRoster' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDDRowBySQL('Doctor/Nurse', 'employee_id', $sqlEmployee,'')}
                {$formObj->getTimeRow('Choose Time In', 'work_from_time', $work_from_time)}
                {$formObj->getTimeRow('Choose Time Out', 'work_to_time', '')}
                {$expPlus}
                {$formObj->getDDRowByArr('Repeat', 'roster_type', $categoryTypeArr, '')}
                <div class='create_MonthCheck'>
                    {$formObj->getRRow('Create For Month', 'create_month', $create_monthArrdefault, $create_monthArr)}
                </div>
                <div class='WeekendsExclude WeekendsExcludeDisable'>
                    {$formObj->getCheckBoxArrRowByArr('Exclude', 'daily_type', $dailyTypeArr, $dailyTypeArr)}
                </div>
                <div class='WeekdaysSelect WeekdaysSelectDisable'>
                    {$formObj->getCheckBoxArrRowByArr('Weekly', 'weekdays_select', $weekTypeArr, '')}
                </div>
                <input type='hidden' name='work_date' value='{$work_date}' />
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getAddMoreWorkingTime1(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $text = "<div class='secondWorkTimeDuty secondWorkTimeDutyDisable'>
                        <div class='addTimeinOutLine2'>
                            <img src='" . CP_LOCAL_PATH_ALIAS . "images/minus-512.png' />
                        </div>
                        {$formObj->getTimeRow('Choose Time In', 'work_from_time2', '')}
                        {$formObj->getTimeRow('Choose Time Out', 'work_to_time2', '')}
                        <div class='addTimeinOutLine3'>
                            <img src='" . CP_LOCAL_PATH_ALIAS . "images/plus-512.png' />
                        </div> 
                        <input type='hidden' name='work_time2' value='2' />
                    </div>
        ";

        return $text;
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getAddMoreWorkingTime2(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $text = "<div class='secondWorkTimeDuty2 secondWorkTimeDutyDisable2'>
                        <div class='addTimeinOutLine4'>
                            <img src='" . CP_LOCAL_PATH_ALIAS . "images/minus-512.png' />
                        </div>
                        {$formObj->getTimeRow('Choose Time In', 'work_from_time3', '')}
                        {$formObj->getTimeRow('Choose Time Out', 'work_to_time3', '')}
                        <input type='hidden' name='work_time3' value='3' />
                    </div>
        ";

        return $text;
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
     function getDutyRosterEdit(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $duty_roster_id = $fn->getReqParam('duty_roster_id');
        $row = $fn->getRecordRowByID('duty_roster', 'duty_roster_id', $duty_roster_id);

        $formAction = "index.php?_topRm=main&module=labsg_dutyRoster&_spAction=dutyRosterEditFormSubmit&showHTML=0";
        
        $sqlEmployee = "
        SELECT employee_id
            ,employee_name AS employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $update_typeArr = array( 'Edit this Occurence'
                                ,'Edit all Future Occurences');

        $update_typeDefault = 'Edit this Occurence';

        $text = "
        <form id='portalFormDutyRosterEdit' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDDRowBySQL('Doctor/Nurse', 'employee_id', $sqlEmployee, $row['employment_id'])}
                {$formObj->getTimeRow('Choose Time In', 'work_from_time', $row['work_from_time'])}
                {$formObj->getTimeRow('Choose Time Out', 'work_to_time', $row['work_to_time'])}
                <div class='create_MonthCheck onEditUpdateType'>
                    {$formObj->getRRow('Update', 'update_type', $update_typeDefault, $update_typeArr)}
                </div>
                <input type='hidden' name='work_date' value='{$row['work_date']}' />
                <input type='hidden' name='duty_roster_id' value='{$duty_roster_id}' />
                <input type='hidden' name='employee_id_current' value='{$row['employment_id']}' />
                <input type='hidden' name='work_from_time_current' value='{$row['work_from_time']}' />
                <input type='hidden' name='work_to_time_current' value='{$row['work_to_time']}' />
            </table>
        </form>
        ";

        return $text;
     }

    /**
     *
     */
    function getDoctorDetails(){
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $work_date = $fn->getReqParam('work_date');
        
        if($work_date == ''){
            $work_date  = date("Y-m-d");
        }

        $appendSqlDr = '';
        $appendSqlDri = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlDr  = "AND r.site_id = {$cpSiteIdSession}";
            $appendSqlDri = "AND site_id = {$cpSiteIdSession}";
        }

        $doctorDetailsRow = '';
        $sqldutyRosterDetails = "
        SELECT  employee_name AS Doctor_Name
               ,e.color
               ,r.employment_id
               ,r.work_date
               ,r.duty_roster_id
        FROM duty_roster r
        LEFT JOIN employee e ON (e.employee_id = r.employment_id)
        WHERE r.work_date = '{$work_date}'
        AND r.employment_id != ''
        {$appendSqlDr}
        GROUP BY r.employment_id
        ";
        
        $resultdutyRosterDetails = $db->sql_query($sqldutyRosterDetails);
        while ($rowdutyRosterDetails = $db->sql_fetchrow($resultdutyRosterDetails)) {
            $SQLTimings = "
            SELECT work_from_time
                  ,work_to_time
            FROM duty_roster
            WHERE employment_id = {$rowdutyRosterDetails['employment_id']}
            AND work_date = '{$rowdutyRosterDetails['work_date']}'
            {$appendSqlDri}
            ";

            $resultTimings = $db->sql_query($SQLTimings);
            $timingsRow = '';
            while ($rowTimings = $db->sql_fetchrow($resultTimings)) {
                $timingsRow .= "{$rowTimings['work_from_time']} - {$rowTimings['work_to_time']}<br/>";
            }

            $doctorName    = "<a class='editDutyRoasterDetails' duty_roster_id='{$rowdutyRosterDetails['duty_roster_id']}'>{$rowdutyRosterDetails['Doctor_Name']}</a>";
            $doctorDetailsRow .="
            <tr>
                <td>{$doctorName}</td>
                <td>{$timingsRow}</td>
                <td style='background-color:{$rowdutyRosterDetails['color']};'></td>
            </tr>
            ";
        }

        $date = $work_date;
        $prev_date = date('Y-m-d', strtotime($date .' -1 day'));
        $next_date = date('Y-m-d', strtotime($date .' +1 day'));

        $work_date_Header = $fn->getCPDate($work_date, 'd-m-Y');
        $Current_date = date("Y-m-d");
        
        $todayButtonDisable = '';
        if($work_date == $Current_date){
            $todayButtonDisable = 'todayButtonDutyDisable';
        }
        
        $doctorDetails = "
        <div id='doctorDetails'>
            <div class='header'>
                <div class='floatbox'>
                     <div class='previous_dutyRoster_icon float_left button' prev_date={$prev_date}>
                        <
                    </div>
                    <div class='today_dutyRoster_icon button float_left {$todayButtonDisable}' today={$Current_date}>today</div>
                    <div  class='dutyRosterDateChange float_left'>{$work_date_Header}</div>
                    <div class='next_dutyRoster_icon float_right button' next_date={$next_date}>
                        >
                    </div>
                </div>
                <div class='floatbox'>
                    <div  class='dutyRosterListHeading'>Dr / Nurse</div>
                </div>
            </div>

            <div  class='appointmentScroll'>
                <table class='thinlist'>
                    <thead>
                        <tr>
                            <th>Dr/Nurse</th>
                            <th>Timings</th>
                            <th class='txtRight'>Color</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$doctorDetailsRow}
                    </tbody>
                </table>
            </div>
        </div>
        ";

        return $doctorDetails;
    }
   

}