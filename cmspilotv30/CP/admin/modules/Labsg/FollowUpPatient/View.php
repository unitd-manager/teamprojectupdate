<?
class CP_Admin_Modules_Labsg_FollowUpPatient_View extends CP_Common_Lib_ModuleViewAbstract
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

        $currentDate  = date("Y-m-d");

        $sqlEmployee = "
        SELECT employee_id
              ,employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $text = "
        <div class='followUpPatientCalendarView'>
            <div class='doctorFilter'>
                {$formObj->getDDRowBySQL('Doctor/Nurse : ', 'employee_id', $sqlEmployee,'')}
            </div>
            {$this->getFollowupCalendarView()}
        </div>
        <div class='followUpPatientCalendarViewRight'>
            {$this->getDoctorDetails()}
        </div>
        <div class='followUpPatientCalendarViewRight2'>
            <div class='doctorFilter_visit'>
                {$formObj->getDDRowBySQL('Doctor/Nurse : ', 'employee_id_appointment', $sqlEmployee,'')}
            </div>
            {$this->getFollowUpListDetails()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getFollowUpListDetails(){
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $currentDate  = date("Y-m-d");
        $employee_id = $fn->getReqParam('employee_id');

        $appendSql = '';
        if($employee_id != ''){
            $appendSql = "AND f.employee_id = {$employee_id}";
        }

        $appendSqlFp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlFp = "AND f.site_id = {$cpSiteIdSession}";
        }

        $followUpDetailsRow = '';
        $sqlfollowUpDetails = "
        SELECT  CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS Patient_Name
               ,p.nric
               ,f.patient_information_id
               ,f.follow_up_date
               ,f.follow_up_time
               ,f.employee_id
               ,f.follow_up_patient_id
               ,f.appointment_id
               ,f.status
        FROM follow_up_patient f
        LEFT JOIN employee e ON (e.employee_id = f.employee_id)
        LEFT JOIN (patient_information p) ON (p.patient_information_id = f.patient_information_id)
        WHERE f.follow_up_date = '{$currentDate}'
        {$appendSql}
        {$appendSqlFp}
        ORDER BY follow_up_patient_id ASC
        ";
        $resultfollowUpDetails = $db->sql_query($sqlfollowUpDetails);

        $cancelFollowUp = '';

        while ($rowfollowUpDetails = $db->sql_fetchrow($resultfollowUpDetails)) {

            $createAppointment = "
            <div class='button'>
                <a class = 'createAppointment' follow_up_patient_id={$rowfollowUpDetails['follow_up_patient_id']} employee_id={$rowfollowUpDetails['employee_id']}>
                    Create Appointment
                </a>
            <div>
            ";

            if($rowfollowUpDetails['appointment_id'] != ''){
                $SQLAppointment = "
                SELECT a.appointment_id
                      ,a.status
                FROM appointment a
                WHERE a.appointment_id = {$rowfollowUpDetails['appointment_id']}
                ";
                $resultAppointment  = $db->sql_query($SQLAppointment);
                $numRowsAppointment = $db->sql_numrows($resultAppointment);
                $rowAppointment     = $db->sql_fetchrow($resultAppointment);

                if($numRowsAppointment > 0){
                    $createAppointment = "<font>
                                            <b>Appointment Created</b>
                                          </font>
                    ";

                    if($rowAppointment['status'] == 'Cancelled'){
                        $createAppointment = "<b>{$rowAppointment['status']}</b>";
                    }
                }
            }

            $cancelFollowUp = "<a class='cancelFollowUpDetail cancelFollowUp' follow_up_patient_id='{$rowfollowUpDetails['follow_up_patient_id']}' appointment_id='{$rowfollowUpDetails['appointment_id']}'>Cancel Follow Up</a>";
            if($rowfollowUpDetails['status'] == 'Cancelled'){
                $cancelFollowUp = "<b>{$rowfollowUpDetails['status']}</b>";
                $createAppointment = "<b>{$rowfollowUpDetails['status']}</b>";
            }

            $followUpDetailsRow .="
            <tr>
                <td>{$rowfollowUpDetails['Patient_Name']}</td>
                <td class= 'txtCenter'>{$createAppointment}</td>
                <td>{$cancelFollowUp}</td>
            </tr>
            ";

        }

        $FollowUpDetails = "
        <div id='followUpListDetails'>
            <div class='header'>
                <div class='floatbox'>
                    <div  class='txtCenter'>Patients</div>
                </div>
            </div>

            <div  class='followUpScroll'>
                <table class='thinlist'>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th class= 'txtCenter'>Appointment</th>
                            <th>Cancel</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$followUpDetailsRow}
                    </tbody>
                </table>
            </div>
        </div>
        ";

        return $FollowUpDetails;
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

        $currentDate  = date("Y-m-d");

        $appendSqlFp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlFp = "AND f.site_id = {$cpSiteIdSession}";
        }

        $doctorDetailsRow = '';
        $sqlFollowUpDetails = "
        SELECT  e.employee_name AS Doctor_Name
               ,e.color
               ,count(f.patient_information_id) AS FollowUps
        FROM follow_up_patient f
        LEFT JOIN employee e ON (e.employee_id = f.employee_id)
        WHERE f.follow_up_date = '{$currentDate}'
        AND f.employee_id != ''
        AND f.status != 'Cancelled'
        {$appendSqlFp}
        GROUP BY f.employee_id
        ";
        $resultFollowUpDetails = $db->sql_query($sqlFollowUpDetails);
        while ($rowFollowUpDetails = $db->sql_fetchrow($resultFollowUpDetails)) {
            $doctorDetailsRow .="
            <tr>
                <td>{$rowFollowUpDetails['Doctor_Name']}</td>
                <td class='txtRight'>{$rowFollowUpDetails['FollowUps']}</td>
                <td style='background-color:{$rowFollowUpDetails['color']};'></td>
            </tr>
            ";
        }

        $doctorDetails = "
        <div id='doctorDetails'>
            <div class='header'>
                <div class='floatbox'>
                    <div  class='txtCenter'>Dr / Nurse</div>
                </div>
            </div>

            <div  class='appointmentScroll'>
                <table class='thinlist'>
                    <thead>
                        <tr>
                            <th>Dr/Nurse</th>
                            <th>Total Follow Up</th>
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

    /**
     *
     */
    function getFollowupCalendarView() {
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
            cpm.labsg.followUpPatient.run(exp);
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
    function getCreateAppointmentRecord(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_topRm=main&module=labsg_followUpPatient&_spAction=createAppointmentRecordSubmit&showHTML=0";

        $follow_up_patient_id = $fn->getReqParam('follow_up_patient_id');
        $check_up_date = date("Y-m-d");
        $check_up_time = date("H:i:s");

        $sqlEmployee = "
        SELECT employee_id, employee_name FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $FollowUpRec = $fn->getRecordRowByID('follow_up_patient', 'follow_up_patient_id', $follow_up_patient_id);

        $employee_id = '';
        if($FollowUpRec['employee_id'] != ''){
            $employee_id = $FollowUpRec['employee_id'];
        }

        $patientRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $FollowUpRec['patient_information_id']);

        $text = "
        <form id='portalFormAppointment' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDateRow('Checkup Date', 'check_up_date', $check_up_date)}
                {$formObj->getTimeRow('Checkup Time', 'check_up_time', $check_up_time)}
                {$formObj->getTBRow('Patient Name', 'patient_name', $patientRec['name'])}
                {$formObj->getDDRowBySQL('Doctor/Nurse', 'employee_id', $sqlEmployee, $employee_id)}
                <input type='hidden' name='patient_information_id' value='{$FollowUpRec['patient_information_id']}'>
                <input type='hidden' name='follow_up_patient_id' value='{$follow_up_patient_id}'>
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
    function getAddFollowUpDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $follow_up_date = $fn->getReqParam('follow_up_date');
        $follow_up_time = $fn->getReqParam('follow_up_time');

        $formAction = "index.php?_topRm=main&module=labsg_followUpPatient&_spAction=addFollowUpDetailsFormSubmit&showHTML=0";

        $sqlEmployee = "
        SELECT employee_id, employee_name FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $text = "
        <form id='portalFormFollowup' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDateRow('Checkup Date', 'follow_up_date', $follow_up_date)}
                {$formObj->getTimeRow('Checkup Time', 'follow_up_time', $follow_up_time)}
                {$formObj->getTBRow('Patient Name', 'patient_name', '')}
                {$formObj->getDDRowBySQL('Doctor/Nurse', 'employee_id', $sqlEmployee,'')}
                {$formObj->getTARow('Notes', 'description','')}
                <input type='hidden' name='patient_information_id' value=''>
            </table>
        </form>
        ";

        return $text;
    }
}