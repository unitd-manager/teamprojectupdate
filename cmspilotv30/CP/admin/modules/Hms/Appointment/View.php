<?
class CP_Admin_Modules_Hms_Appointment_View extends CP_Common_Lib_ModuleViewAbstract
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
              ,CONCAT_WS(' ', first_name, middle_name, last_name) AS employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        /*
        $text = "
        <div class='appointmentCalendarView'>
            <div class='doctorFilter'>
                {$formObj->getDDRowBySQL('Doctor/Nurse : ', 'employee_id', $sqlEmployee,'')}
            </div>
            <div>Please update API</div>
        <div class='appointmentCalendarViewRight'>
            <div>Please update API</div>
        </div>
        <div class='appointmentCalendarViewRight2'>
            <div>Please update API</div>
        </div>
        ";
        */

        $text = "
        <div class='appointmentCalendarView'>
            <div class='doctorFilter'>
                {$formObj->getDDRowBySQL('Doctor/Nurse : ', 'employee_id', $sqlEmployee,'')}
            </div>
            {$this->getAppointmentCalendarView()}
        </div>
        <div class='appointmentCalendarViewRight'>
            {$this->getDoctorDetails()}
        </div>
        <div class='appointmentCalendarViewRight2'>
            <div class='doctorFilter_visit'>
                {$formObj->getDDRowBySQL('Doctor/Nurse : ', 'employee_id_visit', $sqlEmployee,'')}
            </div>
            {$this->getAppointmentListDetails()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getAppointmentListDetails(){
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $currentDate  = date("Y-m-d");
        $employee_id = $fn->getReqParam('employee_id');

        $appendSql = '';
        if($employee_id != ''){
            $appendSql = "AND a.dr_Linked = {$employee_id}";
        }

        $appendSqlAp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlAp = "AND a.site_id = {$cpSiteIdSession}";
        }

        $visitDetailsRow = '';
        $sqlVisitDetails = "
        SELECT  CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS Patient_Name
               ,p.nric
               ,a.patient_information_id
               ,a.check_up_date
               ,a.check_up_time
               ,a.dr_Linked
               ,a.appointment_id
               ,a.status
        FROM appointment a
        LEFT JOIN employee e ON (e.employee_id = a.dr_Linked)
        LEFT JOIN (patient_information p) ON (p.patient_information_id = a.patient_information_id)
        WHERE a.check_up_date = '{$currentDate}'
        {$appendSql}
        {$appendSqlAp}
        ORDER BY appointment_id ASC
        ";
        $resultVisitDetails     = $db->sql_query($sqlVisitDetails);

        $cancelPatientVisit = '';

        while ($rowVisitDetails = $db->sql_fetchrow($resultVisitDetails)) {

            $SQLPatientVisit = "
            SELECT pv.appointment_id
                  ,pv.patient_visit_id
                  ,pv.status
            FROM patient_visit pv
            WHERE appointment_id = {$rowVisitDetails['appointment_id']}
            ";
            $resultPatientVisit  = $db->sql_query($SQLPatientVisit);
            $numRowsPatientVisit = $db->sql_numrows($resultPatientVisit);
            $rowPatientVisit     = $db->sql_fetchrow($resultPatientVisit);

            $createVisit = "
            <div class='button visitCreateButton'>
                <a class = 'createVisit' appointment_id={$rowVisitDetails['appointment_id']} dr_required={$rowVisitDetails['dr_Linked']}>
                    Create Visit
                </a>
            <div>
            ";

            if($numRowsPatientVisit > 0){
                $patientVisitLink = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPatientVisit['patient_visit_id']}";
                $createVisit = "<a class = 'viewVisitRecord' href='{$patientVisitLink}'><u>
                                    View Record
                                </u></a>
                ";

                $cancelPatientVisit = "<a class='cancelPatientVisit' patient_visit_id={$rowPatientVisit['patient_visit_id']}>
                                          Cancel
                                       </a>";

                if($rowPatientVisit['status'] == 'Cancelled'){
                    $cancelPatientVisit = "<b>{$rowPatientVisit['status']}</b>";
                }
            }

            $cancelAppointment = "<a class='cancelAppointment eventCancelAppointment' appointment_id={$rowVisitDetails['appointment_id']}>Cancel Appointment</a>";
            if($rowVisitDetails['status'] == 'Cancelled'){
                $cancelAppointment = "<b>{$rowVisitDetails['status']}</b>";
                //$createVisit       = "<b>{$rowVisitDetails['status']}</b>";
            }

            $visitDetailsRow .="
            <tr>
                <td>{$rowVisitDetails['Patient_Name']}</td>
                <td class= 'txtCenter'>{$createVisit}</td>
                <td class='txtRight'>{$rowVisitDetails['nric']}</td>
                <td>{$cancelAppointment}</td>
            </tr>
            ";
        }

        $appointmentDetails = "
        <div id='appointmentListDetails'>
            <div class='header'>
                <div class='floatbox'>
                    <div  class='txtCenter'>Patients</div>
                </div>
            </div>

            <div  class='appointmentvisitScroll'>
                <table class='thinlist'>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th class= 'txtCenter'>Visit</th>
                            <th>NRIC</th>
                            <th>Cancel</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$visitDetailsRow}
                    </tbody>
                </table>
            </div>
        </div>
        ";

        return $appointmentDetails;
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
        $cpCfg   = Zend_Registry::get('cpCfg');

        $currentDate  = date("Y-m-d");

        $appendSqlAp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlAp = "AND a.site_id = {$cpSiteIdSession}";
        }

        $doctorDetailsRow = '';
        $sqlAppointmentDetails = "
        SELECT  CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS Doctor_Name
               ,e.color
               ,count(a.patient_information_id) AS Appointments
        FROM appointment a
        LEFT JOIN employee e ON (e.employee_id = a.dr_Linked)
        WHERE a.check_up_date = '{$currentDate}'
        AND a.dr_Linked != ''
        AND a.status != 'Cancelled'
        {$appendSqlAp}
        GROUP BY a.dr_Linked
        ";
        $resultAppointmentDetails = $db->sql_query($sqlAppointmentDetails);
        while ($rowAppointDetails = $db->sql_fetchrow($resultAppointmentDetails)) {
            $doctorDetailsRow .="
            <tr>
                <td>{$rowAppointDetails['Doctor_Name']}</td>
                <td class='txtRight'>{$rowAppointDetails['Appointments']}</td>
                <td style='background-color:{$rowAppointDetails['color']};'></td>
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
                            <th>Total Appointments</th>
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
    function getAppointmentCalendarView() {
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
            cpm.hms.appointment.run(exp);
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
    function getAddAppointmentDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $check_up_date = $fn->getReqParam('check_up_date');
        $check_up_time = $fn->getReqParam('check_up_time');

        $formAction = "index.php?_topRm=main&module=hms_appointment&_spAction=addAppointmentFormSubmit&showHTML=0";

        $sqlEmployee = "
        SELECT employee_id
              ,CONCAT_WS(' ', first_name, middle_name, last_name) AS employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $text = "
        <form id='portalFormAppointment' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDateRow('Checkup Date', 'check_up_date', $check_up_date)}
                {$formObj->getTimeRow('Checkup Time', 'check_up_time', $check_up_time)}
                {$formObj->getTBRow('Patient Name', 'patient_name', '')}
                {$formObj->getDDRowBySQL('Doctor/Nurse', 'employee_id', $sqlEmployee,'')}
                {$formObj->getTARow('Notes', 'description','')}
                <input type='hidden' name='patient_information_id' value=''>
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
    function getUpdateAppointmentDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $appointment_id = $fn->getReqParam('appointment_id');

        $formAction = "index.php?_topRm=main&module=hms_appointment&_spAction=updateAppointmentDetailsSubmit&showHTML=0";

        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');

        $sqlEmployee = "
        SELECT e.employee_id
              ,e.first_name AS employee_name
        FROM employee e
        LEFT JOIN (duty_roster dr) ON (e.employee_id = dr.employment_id)
        WHERE e.status = 'Current'
          AND dr.work_date = '{$current_date}'
          AND ('{$current_time}' BETWEEN dr.work_from_time AND dr.work_to_time)
        ORDER BY employee_name ASC
        ";
        $resultEmployee = $db->sql_query($sqlEmployee);

        /* Choosing the appointed Doctor for the Patient Visit if the Doctor is available in Duty Roster */
        $dr_linked = '';
        while($recEmployee = $db->sql_fetchrow($resultEmployee)){
            $appointmentRec = $fn->getRecordRowByID('appointment', 'appointment_id', $appointment_id);

            if ($recEmployee['employee_id'] == $appointmentRec['dr_Linked']) {
                $dr_linked = $appointmentRec['dr_Linked'];
            }
        }

        $current_date_formatted = date('d-m-Y');
        $text = "
        </div>Practioners available on <b>{$current_date_formatted}</b> at <b>{$current_time}</b></div>
        <form id='portalFormAppointmentUpdate' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDDRowBySQL('Practioner', 'employee_id', $sqlEmployee, $dr_linked)}
                <input type='hidden' name='appointment_id' value='{$appointment_id}'>
            </table>
        </form>
        ";

        return $text;
    }
}