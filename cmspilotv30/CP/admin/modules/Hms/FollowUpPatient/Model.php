<?
class CP_Admin_Modules_Hms_FollowUpPatient_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT f.patient_information_id
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS Patient_Name
              ,p.nric
              ,p.mobile
              ,f.follow_up_date
              ,f.follow_up_time
              ,f.appointment_id
              ,f.follow_up_patient_id
        FROM follow_up_patient f
        LEFT JOIN (patient_information p) ON (p.patient_information_id = f.patient_information_id)
       ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar1($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $status       = $fn->getReqParam('status');
        $company_id   = $fn->getReqParam('company_id');
        $company_name = $fn->getReqParam('company_name');

        if ($company_id != "") {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.company_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            //$searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the company name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['category'] = 'Client';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }


    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getEventDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $doctor_id = $fn->getReqParam('doctor_id');
        $cpCfg = Zend_Registry::get('cpCfg');

        $jsonArray = array();

        $appendSql = "";
        if($doctor_id != ''){
           $appendSql ="WHERE f.employee_id = {$doctor_id}";
        }

        $appendSqlFp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites'] && $doctor_id != '') {
            $appendSqlFp = "AND f.site_id = {$cpSiteIdSession}";
        }else if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlFp = "WHERE f.site_id = {$cpSiteIdSession}";
        }


        $SQL = "
        SELECT  f.appointment_id
               ,f.follow_up_patient_id
               ,f.follow_up_time
               ,f.follow_up_date
               ,f.patient_visit_id
               ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS name
               ,f.status
               ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
               ,e.category
               ,e.color
        FROM follow_up_patient f
        LEFT JOIN (patient_information p) ON (p.patient_information_id = f.patient_information_id)
        LEFT JOIN (employee e) ON (e.employee_id = f.employee_id)
        {$appendSql}
        {$appendSqlFp}
       ";
        $result  = $db->sql_query($SQL);

        $title = '';
        while ($row = $db->sql_fetchrow($result)) {
             $desig = '';
             if($row['category'] == 'Doctor'){
                $desig = 'Dr';
             }
             elseif($row['category'] == 'Nurse'){
                $desig = 'Nr';
             }

             $eventStartdate          = $row['follow_up_date'] .' ' . $row['follow_up_time'];
             $eventEnddate            = $row['follow_up_date'] .' ' . $row['follow_up_time'];
             $follow_up_patient_Link  = "index.php?module=hms_followUpPatient&_spAction=followUpDetails&follow_up_patient_id={$row['follow_up_patient_id']}&showHTML=0";
             $doctor_name             = "<a class='evenDetails' href='{$follow_up_patient_Link}'> Dr:{$row['employee_name']}</a>";
             $patient_name            = "<a class='evenDetails' href='{$follow_up_patient_Link}'>{$row['name']}</a>";
             $cancelFollowUp          = "<a class='cancelFollowUp cancelFollowUpOnEvent' follow_up_patient_id='{$row['follow_up_patient_id']}' appointment_id='{$row['appointment_id']}'>Cancel</a>";

            $createAppointment  = "<a class='createAppointment' follow_up_patient_id={$row['follow_up_patient_id']}>Create Appt</a>";

            if($row['appointment_id'] != ''){
                $SQLAppointment = "
                SELECT a.appointment_id
                      ,a.status
                FROM appointment a
                WHERE appointment_id = {$row['appointment_id']}
                ";
                $resultAppointment  = $db->sql_query($SQLAppointment);
                $numRowsAppointment = $db->sql_numrows($resultAppointment);
                $rowAppointment     = $db->sql_fetchrow($resultAppointment);

                if($numRowsAppointment > 0){
                    $createAppointment = "<b>Appt Created</b>";
                }
            }

             $backgroundColor = $row['color'];
             if($row['status'] == 'Cancelled'){
                $createAppointment = "";
                $backgroundColor = '#808080';
                $cancelFollowUp = "<b>Cancelled</b>";
             }


             $buildjson = array(
              'title'                => $title
             ,'patient_name'         => $patient_name
             ,'doctor_name'          => $doctor_name
             ,'start'                => $eventStartdate
             ,'end'                  => $eventEnddate
             ,'allDay'               => false
             ,'backgroundColor'      => $backgroundColor
             ,'borderColor'          => $backgroundColor
             ,'follow_up_patient_id' => $row['follow_up_patient_id']
             ,'cancelFollowUp'       => $cancelFollowUp
             ,'createAppointment'    => $createAppointment
             );

             // Adds each array into the container array
             array_push($jsonArray, $buildjson);
        }

        echo json_encode($jsonArray);
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getFollowUpDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expNoEdit = array('disabled' => true);

        $follow_up_patient_id  = $fn->getReqParam('follow_up_patient_id');
        $followUpDetailsRow    = $this->getFollowUpDetailsRow($follow_up_patient_id);
        $rowFollowUp           = $fn->getRecordRowByID('follow_up_patient', 'follow_up_patient_id', $follow_up_patient_id);

        $sqlEmployee = "
        SELECT employee_id
              ,CONCAT_WS(' ', first_name, middle_name, last_name) AS employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $appointmentDetails = "
        <div id='appointmentDetails1'>
            <div class='doctorFilter'>
                {$formObj->getDDRowBySQL('Doctor/Nurse :', 'followUp_employee_id', $sqlEmployee, $rowFollowUp['employee_id'], $expNoEdit)}
                <input type='hidden' id='appointment_id_appoint' value='{$follow_up_patient_id}' />
            </div>
            <div  class='followUpvisitScroll1'>
                <table class='thinlist'>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th class= 'txtCenter'>Appointment</th>
                            <th>NRIC</th>
                            <th>Phone No</th>
                            <th>Email</th>
                            <th>Notes</th>
                            <th>Edit</th>
                            <th>Cancel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <div id='followUpDetailsEvent'>
                            {$followUpDetailsRow}
                        </div>
                    </tbody>
                </table>
            </div>
        </div>
        ";

        return $appointmentDetails;
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getFollowUpDetailsRow($follow_up_patient_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $followUpDetailsRow = '';
        $sqlfollowUpDetails = "
        SELECT  CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS Patient_Name
               ,p.phone
               ,p.email
               ,p.nric
               ,f.patient_information_id
               ,f.follow_up_date
               ,f.follow_up_time
               ,f.employee_id
               ,f.status
               ,f.follow_up_patient_id
               ,f.appointment_id
               ,f.description
        FROM follow_up_patient f
        LEFT JOIN employee e ON (e.employee_id = f.employee_id)
        LEFT JOIN (patient_information p) ON (p.patient_information_id = f.patient_information_id)
        WHERE f.follow_up_patient_id = '{$follow_up_patient_id}'
        ORDER BY follow_up_patient_id ASC
        ";
        $resultfollowUpDetails     = $db->sql_query($sqlfollowUpDetails);
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
                <td>{$rowfollowUpDetails['nric']}</td>
                <td>{$rowfollowUpDetails['phone']}</td>
                <td>{$rowfollowUpDetails['email']}</td>
                <td id='notesFollowupTd'>
                    {$this->getFollowUpNotes($rowfollowUpDetails['description'], $rowfollowUpDetails['follow_up_patient_id'])}
                </td>
                <td>
                    <a class='notesEditLink'>Click To Edit</a>
                </td>
                <td>{$cancelFollowUp}</td>
            </tr>
            ";
        }

        return $followUpDetailsRow;

    }


    /**
     *
     */
    function getFollowUpNotes($description = '', $follow_up_patient_id = ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $expNoEdit = array('isEditable' => 0);

        if($description == ''){
            $description = $fn->getReqParam('notes');
        }

        if($follow_up_patient_id == ''){
            $follow_up_patient_id = $fn->getReqParam('follow_up_patient_id');
        }

        $text = "
        <div class='notesFollowupDefault'>
            {$formObj->getTARow('', 'description', $description, $expNoEdit)}
        </div>
        <div class='notesFollowup notesFollowupdisable'>
            {$formObj->getTARow('', 'description', $description)}
            <input type='button' follow_up_patient_id = '{$follow_up_patient_id}' name='Update' class='followUpNotesUpdate' value='Update'/>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getUpdateFollowUpNotes(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $follow_up_patient_id = $fn->getReqParam('follow_up_patient_id');
        $notes                = $fn->getReqParam('notes');

        $SQLUpdateNotes = "
        UPDATE follow_up_patient SET description = '{$notes}'
        WHERE follow_up_patient_id = {$follow_up_patient_id}
        ";

        $resultUpdateNotes = $db->sql_query($SQLUpdateNotes);
    }


    /**
     *
     */
    function getSearchPatientDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $patientDetail = $extractor[0];

        $SQL = "
        SELECT  CONCAT_WS(' ', first_name, middle_name, last_name) AS value
               ,CONCAT_WS(' :: ', first_name, middle_name, last_name, nric, mobile, email) AS label
               ,patient_information_id AS id
               ,CONCAT_WS(' ', first_name, middle_name, last_name) AS Patient_Name
        FROM patient_information
        WHERE (patient_information_id LIKE '%{$patientDetail}%'
        OR first_name LIKE '%{$patientDetail}%'
        OR middle_name LIKE '%{$patientDetail}%'
        OR last_name LIKE '%{$patientDetail}%'
        OR mobile LIKE '%{$patientDetail}%'
        OR email LIKE '%{$patientDetail}%')
        ORDER BY Patient_Name
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getFollowUpValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $follow_up_date         = $fn->getPostParam('follow_up_date');
        $follow_up_time         = $fn->getPostParam('follow_up_time');

        $validate->resetErrorArray();
        //$validate->validateData('employee_id', 'Please select Dr/Nurse');
        $validate->validateData('patient_name', 'Please select patient');
        $validate->validateData('follow_up_date', 'Please select Date');
        $validate->validateData('follow_up_time', 'Please select Time');

        $appendSqlFp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlFp = "AND site_id = {$cpSiteIdSession}";
        }

        if($patient_information_id != ''){
            $SQL = "
            SELECT patient_information_id
            FROM follow_up_patient
            WHERE patient_information_id = {$patient_information_id}
            AND follow_up_date = '{$follow_up_date}'
            AND follow_up_time = '{$follow_up_time}'
            {$appendSqlFp}
            ";
            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if($numRows > 0){
                $validate->errorArray['follow_up_time']['name'] = "follow_up_time";
                $validate->errorArray['follow_up_time']['msg']  = "Follow Up already Created for this time";
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddFollowUpDetailsFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getFollowUpValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $employee_id            = $fn->getPostParam('employee_id');
        $follow_up_date         = $fn->getPostParam('follow_up_date');
        $follow_up_time         = $fn->getPostParam('follow_up_time');
        $description            = $fn->getPostParam('description');
        $cpSiteIdSession        = $fn->getSessionParam('cp_site_id');

        $fa = array();

        $fa['patient_information_id'] = $patient_information_id;
        $fa['employee_id']            = $employee_id;
        $fa['follow_up_date']         = $follow_up_date;
        $fa['follow_up_time']         = $follow_up_time;
        
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $fa['status']				  = 'New';
        $fa['description']            = $description;
        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'follow_up_patient');
        $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     *
     */
    function getChangeFollowUpByDrag(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $follow_up_patient_id = $fn->getReqParam('follow_up_patient_id');
        $follow_up_date       = $fn->getReqParam('follow_up_date');
        $follow_up_time       = $fn->getReqParam('follow_up_time');
        $viewName             = $fn->getReqParam('viewName');
        $cpSiteIdSession      = $fn->getSessionParam('cp_site_id');

        $fa = array();

        if($viewName == 'month'){
            $fa['follow_up_date']     = $follow_up_date;
            $fa['modification_date']  = date("Y-m-d H:i:s");
            $fa['modified_by']        = $fn->getSessionParam('userName');
        }else{
            $fa['follow_up_date']     = $follow_up_date;
            $fa['follow_up_time']     = $follow_up_time;
            $fa['modification_date']  = date("Y-m-d H:i:s");
            $fa['modified_by']        = $fn->getSessionParam('userName');
        }

        $whereCondition  = "WHERE follow_up_patient_id = {$follow_up_patient_id}";
        $sqlUpdate       = $dbUtil->getUpdateSQLStringFromArray($fa, "follow_up_patient", $whereCondition);
        $resultUpdate    = $db->sql_query($sqlUpdate);

    }

    /**
     *
     */
    function getAppointmentValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $check_up_date          = $fn->getPostParam('check_up_date');
        $check_up_time          = $fn->getPostParam('check_up_time');

        $validate->resetErrorArray();
        $validate->validateData('patient_name', 'Please select patient');
        $validate->validateData('check_up_date', 'Please select Date');
        $validate->validateData('check_up_time', 'Please select Time');

        $appendSqlAp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlAp = "AND site_id = {$cpSiteIdSession}";
        }

        if($patient_information_id != ''){
            $SQL = "
            SELECT patient_information_id
            FROM appointment
            WHERE patient_information_id = {$patient_information_id}
            AND check_up_date = '{$check_up_date}'
            AND check_up_time = '{$check_up_time}'
            {$appendSqlAp}
            ";
            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if($numRows > 0){
                $validate->errorArray['check_up_time']['name'] = "check_up_time";
                $validate->errorArray['check_up_time']['msg']  = "Appointment already Created for this time";
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     *
     */
    function getCreateAppointmentRecordSubmit(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $follow_up_patient_id   = $fn->getReqParam('follow_up_patient_id');
        $employee_id            = $fn->getPostParam('employee_id');
        $check_up_date          = $fn->getPostParam('check_up_date');
        $check_up_time          = $fn->getPostParam('check_up_time');
        $cpSiteIdSession        = $fn->getSessionParam('cp_site_id');

        if (!$this->getAppointmentValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();

        $fa['patient_information_id'] = $patient_information_id;
        $fa['dr_Linked']              = $employee_id;
        $fa['check_up_date']          = $check_up_date;
        $fa['check_up_time']          = $check_up_time;

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $fa['status']                 = 'New';
        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'appointment');
        $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
        $appointment_id       = $db->sql_nextid();

        $SqlFollowUp = "
        UPDATE follow_up_patient SET appointment_id = {$appointment_id}
        WHERE follow_up_patient_id = {$follow_up_patient_id}
        ";
        $resultFollowUp = $db->sql_query($SqlFollowUp);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getFollowUpUpdateValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $validate->resetErrorArray();
        $validate->validateData('employee_id', 'Please select Dr/Nurse');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     *
     */
    function getUpdateFollowUpDetailsSubmit(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getFollowUpUpdateValidate()){
            return $validate->getErrorMessageXML();
        }

        $employee_id          = $fn->getPostParam('employee_id');
        $follow_up_patient_id = $fn->getPostParam('follow_up_patient_id');

        $fa = array();

        $fa['employee_id']        = $employee_id;
        $fa['modification_date']  = date("Y-m-d H:i:s");
        $fa['modified_by']        = $fn->getSessionParam('userName');

        $whereCondition  = "WHERE appointment_id = {$appointment_id}";
        $sqlUpdate       = $dbUtil->getUpdateSQLStringFromArray($fa, "appointment", $whereCondition);
        $resultUpdate    = $db->sql_query($sqlUpdate);

        return $validate->getSuccessMessageXML();
    }


    /**
     *
     *
     */
    function getUpdateAppointmentEventDetails(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $employee_id    = $fn->getReqParam('employee_id');
        $appointment_id = $fn->getReqParam('appointment_id');

        $fa = array();

        $fa['dr_Linked']          = $employee_id;
        $fa['modification_date']  = date("Y-m-d H:i:s");
        $fa['modified_by']        = $fn->getSessionParam('userName');

        $whereCondition  = "WHERE appointment_id = {$appointment_id}";
        $sqlUpdate       = $dbUtil->getUpdateSQLStringFromArray($fa, "appointment", $whereCondition);
        $resultUpdate    = $db->sql_query($sqlUpdate);
    }

    /**
     *
     *
     */
    function getCancelFollowUpRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $follow_up_patient_id = $fn->getReqParam('follow_up_patient_id');
        $appointment_id       = $fn->getReqParam('appointment_id');

        $SqlCancelFollowUp = "
        UPDATE follow_up_patient SET status = 'Cancelled'
        WHERE follow_up_patient_id = {$follow_up_patient_id}
        ";
        $resultFollowUp  = $db->sql_query($SqlCancelFollowUp);
    }
}
