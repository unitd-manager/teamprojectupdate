<?
class CP_Admin_Modules_Labsg_Appointment_View extends CP_Common_Lib_ModuleViewAbstract
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
        <div class='appointmentCalendarView'>
            {$this->getAppointmentCalendarView()}
        </div>
        <div class='appointmentCalendarViewRight2'>
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

        $currentDate  = date("Y-m-d");
        $employee_id = $fn->getReqParam('employee_id');

        $appendSql = '';
        if($employee_id != ''){
            $appendSql = "AND a.dr_Linked = {$employee_id}";
        }

        $visitDetailsRow = '';
        $sqlVisitDetails = "
        SELECT  CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS Patient_Name
               ,p.nric
               ,a.patient_information_id
               ,a.check_up_date
               ,a.check_up_time
               ,a.appointment_id
               ,a.status
        FROM appointment a
        LEFT JOIN (patient_information p) ON (p.patient_information_id = a.patient_information_id)
        WHERE a.check_up_date = '{$currentDate}'
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
                <a class = 'createVisit' appointment_id={$rowVisitDetails['appointment_id']}>
                    Create Visit
                </a>
            <div>
            ";

            if($numRowsPatientVisit > 0){
                $patientVisitLink = "index.php?_topRm=main&module=labsg_patientVisit&_action=edit&patient_visit_id={$rowPatientVisit['patient_visit_id']}";
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
            cpm.labsg.appointment.run(exp);
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

        $formAction = "index.php?_topRm=main&module=labsg_appointment&_spAction=addAppointmentFormSubmit&showHTML=0";

        $text = "
        <form id='portalFormAppointment' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDateRow('Checkup Date', 'check_up_date', $check_up_date)}
                {$formObj->getTimeRow('Checkup Time', 'check_up_time', $check_up_time)}
                {$formObj->getTBRow('Patient Name', 'patient_name', '')}
                {$formObj->getTARow('Notes', 'description','')}
                <input type='hidden' name='patient_information_id' value=''>
            </table>
        </form>
        ";

        return $text;
    }

}