<?
class CP_Admin_Modules_Hms_PatientVisit_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $count   = 0;
        $rows    = '';
        $searchDone = $fn->getReqParam('searchDone');

        foreach ($dataArray as $row){
            $email     = $row['email'];
            //$website   = $row['website'];
            $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

            $visit_code = '';
            if($row['visit_code'] != ''){
                $visit_code = 'VST-'.$row['visit_code'];
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell('')}
            {$listObj->getListDataCell($visit_code)}
            {$listObj->getListDataCell($check_up_date)}
            {$listObj->getListDataCell($row['patient_name'])}
            {$listObj->getListDataCell($row['nric'])}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListDataCell($row['bill_type'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['patient_information_id'])}
            ";

            $count++ ;
        }

        //$newPatientLink = "index.php?_topRm=main&module=hms_patientVisit&_action=new";
        $search_List    = "index.php?_topRm=main&module=hms_patientVisit";
        $class = '';
        $displayNone = '';
        $cpSearch = '';
        if($searchDone != 1){
            $class='defaultListDisplay';
        }else {
            $displayNone = 'displayNone';
            $cpSearch="
            <script>
                $('.cpSearch').css('display', 'block');
            </script>
            ";
        }
        $text = "
        <div class='searchListDisplay {$displayNone}'>{$this->getSearchList()}</div>
        <div class='{$class}'>
            <div class='floatbox goToSearchPatientVisit'>
                <div class='float_left'>
                    <a href='{$search_List}' class='button'>Go To Search</a>
                </div>
            </div>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Image', '')}
            {$listObj->getListHeaderCell('Visit Code', 'pv.visit_code')}
            {$listObj->getListHeaderCell('Visit Date', 'pv.check_up_date')}
            {$listObj->getListHeaderCell('Patient Name', 'patient_name')}
            {$listObj->getListHeaderCell('NRIC', 'p.nric')}
            {$listObj->getListHeaderCell('Email', 'p.email' )}
            {$listObj->getListHeaderCell('Mobile', 'p.mobile')}
            {$listObj->getListHeaderCell('Bill Type', 'p.bill_type')}
            {$listObj->getListHeaderCell('Status', 'pv.status')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        {$cpSearch}
        ";

        return $text;
    }

    /**
     *
     */
    function getUpdatePatientVisitCode(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL = "
        SELECT patient_visit_id
              ,visit_code
        FROM patient_visit
        WHERE site_id = {$cpSiteIdSession}
        ORDER BY creation_date ASC
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            if($row['visit_code'] == ''){
                $visit_code_setting = $fn->getSettingsValueByKey("nextPatientvisitCode");
                
                $SQLUpdatevisitCode = "
                UPDATE patient_visit SET visit_code = '{$visit_code_setting}'
                WHERE patient_visit_id = {$row['patient_visit_id']}
                ";
                $resultUpdatevisitCode = $db->sql_query($SQLUpdatevisitCode);

                $SQLvisitCode = "
                UPDATE setting SET value = (value+1) 
                WHERE key_text = 'nextPatientvisitCode'
                AND site_id = {$cpSiteIdSession}
                ";
                $resultvisitCode = $db->sql_query($SQLvisitCode);
            }
        }
    }

    /**
     *
     */
    function getUpdateInvoiceCode(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL = "
        SELECT invoice_id
              ,invoice_code
        FROM invoice
        WHERE site_id = {$cpSiteIdSession}
        ORDER BY creation_date ASC
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            if($row['invoice_code'] == '' || $row['invoice_code'] == 'INV - '){
                $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");
                $invoice_code = 'INV - ' . $invoice_code;
               
                $SQLUpdateInvoiceCode = "
                UPDATE invoice SET invoice_code = '{$invoice_code}'
                WHERE invoice_id = {$row['invoice_id']}
                ";
                $resultUpdateInvoiceCode = $db->sql_query($SQLUpdateInvoiceCode);

                $SQLInvoiceCode = "
                UPDATE setting SET value = (value+1) 
                WHERE key_text = 'nextInvoiceCode'
                AND site_id = {$cpSiteIdSession}
                ";
                $resultInvoiceCode = $db->sql_query($SQLInvoiceCode);
            }
        }
    }

    /**
     *
     */
    function getUpdateReceiptCode(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL = "
        SELECT receipt_id
              ,receipt_code
        FROM receipt
        WHERE site_id = {$cpSiteIdSession}
        ORDER BY creation_date ASC
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            if($row['receipt_code'] == '' || $row['receipt_code'] == 'RCPT - '){
                $ReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");
                $ReceiptCode = 'RCPT - ' . $ReceiptCode;
                
                $SQLUpdateReceiptCode = "
                UPDATE receipt SET receipt_code = '{$ReceiptCode}'
                WHERE receipt_id = {$row['receipt_id']}
                ";
                $resultUpdateReceiptCode = $db->sql_query($SQLUpdateReceiptCode);

                $SQLReceiptCode = "
                UPDATE setting SET value = (value+1) 
                WHERE key_text = 'nextReceiptCode'
                AND site_id = {$cpSiteIdSession}
                ";
                $resultReceiptCode = $db->sql_query($SQLReceiptCode);
            }
        }
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset1 = "
        {$formObj->getDateRow('Check Up Date (YYYY-MM-DD)', 'check_up_date')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getSearchList(){
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        //$newPatientLink     = "index.php?_topRm=main&module=hms_patientVisit&_action=new";
        $patient_visit_List = "index.php?_topRm=main&module=hms_patientVisit";
        $expHideFirstOpt    = array('hideFirstOption' => 1);
        $searchlistArr      = array('Search by Name'
                                   ,'Search by NRIC');
        $row    = '';

        $formActionAddpatient = "index.php?module=hms_patientVisit&_spAction=addPatientRecord&showHTML=0";

        $searchResultRows = $this->getPatientVisitSearchResult();
        $searchResultAppointmentRows = $this->getPatientVisitAppointmentSearchResult();

        $text = "
        <!--<div>Connection error occured.</div>-->
        <div class='floatbox'>
            <div class='float_left displayVisitRecords'>
                <a href='#' class='button'>Display Visit Records</a>
            </div>
            <div class='float_right mb10'>
                <a href='{$formActionAddpatient}' class='button' id='addPatientRecord'>Quick Add Patient</a>
            </div>
        </div>
        <div class='searchPanelInPatientVisitLabel'>
            <label class=''>Please key in the words (NAME or NRIC) below to search the patient records</label>

            <div class='searchPanelInPatientVisit'>
                <input class='searchInputPatientVisit'/>
                <!-- <div class='searchSelectPatientVisit'>
                    {$formObj->getDDRowByArr('', 'search_type_by_list', $searchlistArr, '', $expHideFirstOpt)}
                </div>
                -->
                <div class='searchButtonPatientVisit'>
                    <a href='#' class='button searchPatientButton'>Search</a>
                </div>
            </div>
        </div>
        <div class='searchTableInPatientVisit searchTableInPatientVisithide'>
            {$searchResultRows}
        </div>
        <div class='searchTableInPatientVisitAppointment'>
            {$searchResultAppointmentRows}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPatientVisitSearchResult(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $inputBoxVaue  = $fn->getReqParam('inputBoxVaue');
        $dropdownValue = $fn->getReqParam('dropdownValue');
        $lock          = $fn->getReqParam('lock');
        $currentDate   = date("Y-m-d");

        $text = '';
        $appendSql = '';
        $resultRow = '';

        if($dropdownValue == 'Search by NRIC'){
            $appendSql .= "WHERE nric LIKE '%{$inputBoxVaue}%'";
        }
        else if($dropdownValue == 'Search by Name'){
            $appendSql .= "WHERE patient_name LIKE '%{$inputBoxVaue}%'";
        }

        if($inputBoxVaue != ''){
            $SQL = "
            SELECT p.nric
                  ,p.patient_information_id
                  ,p.mobile
                  ,p.email
                  ,p.dob
                  ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
            FROM patient_information p
            WHERE (p.first_name LIKE '%{$inputBoxVaue}%'
               OR p.middle_name LIKE '%{$inputBoxVaue}%'
               OR p.last_name LIKE '%{$inputBoxVaue}%'
               OR p.nric LIKE '%{$inputBoxVaue}%')
            ";

            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            while($rec    = $db->sql_fetchrow($result)){
                $appendSqlPV = '';
                $appendSqlAp = '';
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
                    $appendSqlPV = "AND pv.site_id = {$cpSiteIdSession}";
                    $appendSqlAp = "AND a.site_id = {$cpSiteIdSession}";
                }

                $dob = $fn->getCPDate($rec['dob'], 'd-m-Y');

                $SQLPatientVisit = "
                SELECT pv.patient_visit_id
                      ,pv.status
                FROM patient_visit pv
                WHERE patient_information_id = {$rec['patient_information_id']}
                AND pv.check_up_date = '{$currentDate}'
                {$appendSqlPV}
                ";
                $resultPatientVisit  = $db->sql_query($SQLPatientVisit);
                $numRowsPatientVisit = $db->sql_numrows($resultPatientVisit);
                $rowPatientVisit     = $db->sql_fetchrow($resultPatientVisit);

                $SQLAppointment = "
                SELECT a.appointment_id
                      ,a.dr_Linked
                      ,a.check_up_time
                FROM appointment a
                WHERE patient_information_id = {$rec['patient_information_id']}
                AND a.check_up_date = '{$currentDate}'
                {$appendSqlAp}
                ";
                $resultAppointment   = $db->sql_query($SQLAppointment);
                $numRowsAppointment  = $db->sql_numrows($resultAppointment);
                $rowAppointment      = $db->sql_fetchrow($resultAppointment);

                $createVisit = "
                <div class='button btn btn-default visitCreateButton'>
                    <a class='createVisit' patient_information_id={$rec['patient_information_id']} dr_required='{$rowAppointment['dr_Linked']}' appointment_id='{$rowAppointment['appointment_id']}'>
                        Create Visit
                    </a>
                <div>
                ";

                if($numRowsPatientVisit > 0){
                    $patientVisitLink = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPatientVisit['patient_visit_id']}";
                    $createVisit = "<a class = 'button btn btn-default viewVisitRecord' href='{$patientVisitLink}'>
                                        View Record
                                    </a>
                    ";
                }

                $age = '';
                if($rec['dob'] != ''){
                    $dob_for_age = $dateUtil->formatDate($rec['dob'], 'DD-MM-YYYY');
                    $modObj = getCPModuleObj('hms_patientInformation');
                    $age = $modObj->view->getFindage($dob_for_age, date('d-m-Y'));
                }

                $text .= "
                <tr>
                    <td></td>
                    <td>{$rec['patient_name']}</td>
                    <td>{$rowAppointment['check_up_time']}</td>
                    <td class='txtCenter'>{$createVisit}</td>
                    <td>{$rec['nric']}</td>
                    <td>{$rec['email']}</td>
                    <td>{$rec['mobile']}</td>
                    <td>{$age}</td>
                </tr>
                ";
            }

            if($numRows > 0){
                $resultRow = "
                <div class='searchResultLabel'>
                    <label class=''>Please find the Search Results below : {$numRows} Record(s)</label>
                </div>
                <table class='thinlist'>
                    <thead>
                        <th>Image</th>
                        <th>Patient Name</th>
                        <th>Appointment Time</th>
                        <th class='txtCenter'>Visit</th>
                        <th>NRIC</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Age</th>
                    </thead>
                    <tbody>
                        {$text}
                    </tbody>
                </table>
                ";
            }else{
                $resultRow = "
                <div class='searchResultLabel'>
                    <label class=''>No Results found for '{$inputBoxVaue}'.</label>
                </div>
                ";
            }
        }

        return $resultRow;
    }

    /**
     *
     */
    function getPatientVisitAppointmentSearchResult(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $inputBoxVaue  = $fn->getReqParam('inputBoxVaue');
        $dropdownValue = $fn->getReqParam('dropdownValue');
        $lock          = $fn->getReqParam('lock');
        $currentDate   = date("Y-m-d");

        $text = '';
        $appendSql = '';
        $resultRow = '';

        if($dropdownValue == 'Search by NRIC'){
            $appendSql .= "WHERE nric LIKE '%{$inputBoxVaue}%'";
        }
        else if($dropdownValue == 'Search by Name'){
            $appendSql .= "WHERE name LIKE '%{$inputBoxVaue}%'";
        }

        $appendSqlPV = '';
        $appendSqlAp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $appendSqlPV = "AND pv.site_id = {$cpSiteIdSession}";
            $appendSqlAp = "AND a.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT a.check_up_date
              ,a.check_up_time
              ,p.patient_information_id
              ,p.nric
              ,p.mobile
              ,p.email
              ,p.dob
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
        FROM appointment a
        LEFT JOIN patient_information p ON (p.patient_information_id = a.patient_information_id)
        WHERE a.check_up_date = '{$currentDate}'
          AND p.patient_information_id != ''
        {$appendSqlAp}
        UNION
        SELECT pv.check_up_date
              ,pv.check_up_time
              ,p.patient_information_id
              ,p.nric
              ,p.mobile
              ,p.email
              ,p.dob
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
        FROM patient_visit pv
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        WHERE pv.check_up_date = '{$currentDate}'
          AND p.patient_information_id != ''
          {$appendSqlPV}
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        while($rec    = $db->sql_fetchrow($result)){

            $dob = $fn->getCPDate($rec['dob'], 'd-m-Y');

            $SQLPatientVisit = "
            SELECT pv.patient_visit_id
                  ,pv.status
                  ,pv.record_type
            FROM patient_visit pv
            WHERE patient_information_id = {$rec['patient_information_id']}
            AND pv.check_up_date = '{$currentDate}'
            {$appendSqlPV}
            ";
            $resultPatientVisit  = $db->sql_query($SQLPatientVisit);
            $numRowsPatientVisit = $db->sql_numrows($resultPatientVisit);
            $rowPatientVisit     = $db->sql_fetchrow($resultPatientVisit);

            $SQLAppointment = "
            SELECT a.appointment_id
                  ,a.dr_Linked
            FROM appointment a
            WHERE patient_information_id = {$rec['patient_information_id']}
            AND a.check_up_date = '{$currentDate}'
            {$appendSqlAp}
            ";
            $resultAppointment   = $db->sql_query($SQLAppointment);
            $numRowsAppointment  = $db->sql_numrows($resultAppointment);
            $rowAppointment      = $db->sql_fetchrow($resultAppointment);

            $createVisit = "
            <div class='button btn btn-default visitCreateButton'>
                <a class='createVisit' patient_information_id={$rec['patient_information_id']} dr_required='{$rowAppointment['dr_Linked']}' appointment_id='{$rowAppointment['appointment_id']}'>
                    Create Visit
                </a>
            <div>
            ";

            $PVstatusTd = '';
            if($numRowsPatientVisit > 0){
                $patientVisitLink = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPatientVisit['patient_visit_id']}";
                $createVisit = "<a class = 'button btn btn-default viewVisitRecord' href='{$patientVisitLink}'>
                                    View Record
                                </a>
                ";

                $PVstatusTd = "{$rowPatientVisit['status']}";
            }

            $age = '
            ';
            if($rec['dob'] != ''){
                $dob = $fn->getCPDate($rec['dob'], 'Y');
                $age = date('Y')- $dob;
            }

            $age = '';
            if($rec['dob'] != ''){
                $dob_for_age = $dateUtil->formatDate($rec['dob'], 'DD-MM-YYYY');
                $modObj = getCPModuleObj('hms_patientInformation');
                $age = $modObj->view->getFindage($dob_for_age, date('d-m-Y'));
            }

            $patient_Link = "index.php?_topRm=main&module=hms_patientInformation&_action=edit&patient_information_id={$rec['patient_information_id']}";
            $patient_name = "<a href='{$patient_Link}' target='_blank'><u>{$rec['patient_name']}</u></a>";

            if($rowPatientVisit['record_type'] == 'Walk In'){
                $check_up_time = $rec['check_up_time'];
                $visit_type = 'Walk in';
            }
            else{
                $check_up_time = $rec['check_up_time'];
                $visit_type = 'Appt Time : ' .$check_up_time;
            }

            $text .= "
            <tr>
                <td></td>
                <td>{$patient_name}</td>
                <td>{$visit_type}</td>
                <td class='txtCenter'>{$createVisit}</td>
                <td>{$PVstatusTd}</td>
                <td>{$rec['nric']}</td>
                <td>{$rec['email']}</td>
                <td>{$rec['mobile']}</td>
                <td>{$age}</td>
            </tr>
            ";
        }

        if($numRows > 0){
            $resultRow = "
            <div class='searchResultLabel'>
                <label class=''>Please find below the number of patients visited today : {$numRows} Patient(s)</label>
            </div>
            <table class='thinlist'>
                <thead>
                    <th>Image</th>
                    <th>Patient Name</th>
                    <th>By Appointment/Walk In</th>
                    <th class='txtCenter'>Visit</th>
                    <th>Status</th>
                    <th>NRIC</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Age</th>
                </thead>
                <tbody>
                    {$text}
                </tbody>
            </table>
            ";
        }else{
            $resultRow = "
            <div class='searchResultLabel'>
                <label class=''>No Results found for '{$inputBoxVaue}'.</label>
            </div>
            ";
        }

        return $resultRow;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        //$sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        //$expCountry = array('detailValue' => $row['address_country']);

        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('patientVisitCategory');
        $sqlTitle    = $fn->getValueListSQL('patientVisitTitle');
        $sqlBillType    = $fn->getValueListSQL('billType');
        $expNoEdit = array('isEditable' => 0);

        $followUp = array(
                       '1 week'  => 'One week later'
                      ,'2 weeks'  => 'Two weeks later'
                      ,'3 weeks'  => 'Three weeks later'
                      ,'4 weeks'  => 'Four weeks later'
                      ,'5 weeks'  => 'Five weeks later'
                      ,'6 weeks'  => 'Six weeks later'
                      ,'2 months'  => 'Two months later'
                      ,'3 months'  => 'Three months later'
                      ,'6 months'  => 'Six months later'
                      );
        $expArr = array('useKey' => 1);

        $addDrLbl = "
        <li class='first'>
            <a href='#tabs-2'>Add Dr / Nurse</a>
        </li>
        ";

        $formActionAddDr = "index.php?module=hms_patientVisit&_spAction=addDoctorRecord&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
        $addDrTab = "
        <div id='tabs-2'>
            <div class='button mb10'><a href='{$formActionAddDr}' id='addDoctorRecord' patient_visit_id={$row['patient_visit_id']}>Add Record</a></div>
            <div id='doctorDisplay'>{$this->getDoctorPortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $medHisLbl = "
        <li class='first'>
            <a href='#tabs-1'>Medical History</a>
        </li>
        ";

        $medHisTab = "
        <div id='tabs-1'>
            <div id='medicalHistory'>{$this->getMedicalHistoryDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $oralHygienicLbl = "
        <li class='first'>
            <a href='#tabs-3'>Oral Hygienic/Habits</a>
        </li>
        ";

        $oralHygienicTab = "
        <div id='tabs-3'>
            <div class='floatbox oralHygienicTab'>
                <div class='c25l'><div id='oralHygienic'>{$this->getOralHygienicDisplay($row['patient_visit_id'])}</div></div>
                <div class='c25l'><div id='habits'>{$this->getHabitsDisplay($row['patient_visit_id'])}</div></div>
                <div class='c50l'><div id='intraOral'>{$this->getIntraOralExaminationDisplay($row['patient_visit_id'])}</div></div>
                <div class='c50l mt20'><div id='extraOral'>{$this->getExtraOralExaminationDisplay($row['patient_visit_id'])}</div></div>
                <div class='c50l mt20'><div id='peridontium'>{$this->getPeridontiumDisplay($row['patient_visit_id'])}</div></div>
            </div>
        </div>
        ";

        $treatmentLbl = "
        <li class='first'>
            <a href='#tabs-4'>Treatment/Diagnosis</a>
        </li>
        ";

        $treatmentTab = "
        <div id='tabs-4'>
            <div class='subcolumns'>
                <div id='treatmentDisplay' class='treatmentTabDisplay'>{$this->getTreatmentPortalDisplay($row['patient_visit_id'])}</div>
                <div id='treatmentDisplay' class='diagnosisTabDisplay'>{$this->getDiagnosisPortalDisplay($row['patient_visit_id'])}</div>
            </div>
        </div>
        ";

        $medicineLbl = "
        <li class='first'>
            <a href='#tabs-5'>Medicines</a>
        </li>
        ";

        $medicineTab = "
        <div id='tabs-5'>
            <div class='button mb10'><a href='#' id='addMedicines' patient_visit_id={$row['patient_visit_id']}>Add Record</a></div>
            <div id='medicinesDisplay'>{$this->getMedicinesPortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $labTestLbl = "
        <li class='first'>
            <a href='#tabs-6'>Blood Test</a>
        </li>
        ";

        $labTestTab = "
        <div id='tabs-6'>
            <div id='labDisplay'>{$this->getLabPortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $labsLbl = "
        <li class='first'>
            <a href='#tabs-7'>Labs</a>
        </li>
        ";

        $formActionAddLabs = "index.php?module=hms_patientVisit&_spAction=addLabsRecord&patient_visit_id={$row['patient_visit_id']}&patient_information_id={$row['patient_information_id']}&showHTML=0";
        $labsTab = "
        <div id='tabs-7'>
            <div class='button mb10'><a href='{$formActionAddLabs}' id='addLabsRecord' patient_visit_id={$row['patient_visit_id']}>Add Record</a></div>
            <div id='labsDisplay'>{$this->getLabsDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $summaryLbl = "
        <li class='first'>
            <a href='#tabs-8'>Summary</a>
        </li>
        ";

        $summaryTab = "
        <div id='tabs-8'>
            <div id='summaryDisplay'>{$this->getSummaryPortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $medicalCertficateLbl = "
        <li class='first'>
            <a href='#tabs-9'>Medical Certificate</a>
        </li>
        ";

        $medicalCertficateTab = "
        <div id='tabs-9'>
            <div id='medicalCertficateDisplay'>{$this->getMedicalCertficatePortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $formPerioChart = "index.php?module=hms_patientVisit&_spAction=perioChartForm&patient_visit_id={$row['patient_visit_id']}&showHTML=0";

        $search_List = "index.php?_topRm=main&module=hms_patientVisit&_action=searchlist";

        $SQLOrder ="
        SELECT order_id
        FROM `order`
        WHERE patient_visit_id = {$row['patient_visit_id']}
        ";
        $resultOrder  = $db->sql_query($SQLOrder);
        $numRowsOrder = $db->sql_numrows($resultOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);

        $gotoOrder = '';
        $generateOrder = '';
        $invoicePortalDisplay = '';
        $actionButtons = '';
        $receiptPortalDisplay = '';
        if($numRowsOrder > 0){
            $OrderLink = "index.php?_topRm=finance&module=hms_order&_action=edit&order_id={$rowOrder['order_id']}";
            //$gotoOrder = "<a href='{$OrderLink}' class='button'>Goto Order</a>";

            $SQLInvoice = "
            SELECT i.*
            FROM invoice i
            WHERE i.order_id = {$rowOrder['order_id']}
            AND i.status != 'Cancelled'
            ";
            $resultInvoice = $db->sql_query($SQLInvoice);
            $numRowsInvoice = $db->sql_numrows($resultInvoice);

            if($numRowsInvoice == 0){
                if($row['status'] != 'Cancelled'){
                    $generateOrder = "<a href='#' id='createOrderRecord' patient_visit_id='{$row['patient_visit_id']}' class='button'>Generate Bill</a>";
                }
            }else{
                $billSummaryOrder = "index.php?module=hms_patientVisit&_spAction=summaryInOrder&order_id={$rowOrder['order_id']}&showHTML=0";
                $generateOrder = "<div class='billSummaryOrder float_left'><a class='button' href='{$billSummaryOrder}' id='billSummaryOrder' order_id='{$rowOrder['order_id']}'>Bill Summary</a></div>";
            }

            $modObj = getCPModuleObj('hms_order');
            $rowOrder = $fn->getRecordRowByID('order', 'patient_visit_id', $row['patient_visit_id']);
            $invoicePortalDisplay =  $modObj->view->getInvoicePortalDisplay($rowOrder['order_id']);

            $formActionReceipt = "index.php?module=hms_order&_spAction=generateReceiptForm&order_id={$rowOrder['order_id']}&patient_information_id={$row['patient_information_id']}&patient_visit_id={$row['patient_visit_id']}&showHTML=0";

            $actionButtons ="
            <div class='btn btn-info mb5'>
                <a href='{$formActionReceipt}' id='generateReceipt'>CREATE RECEIPT</a>
            </div>
            ";
            $receiptPortalDisplay =  $modObj->view->getReceiptPortalDisplay($rowOrder['order_id']);
        }
        else{
            $generateOrder = "<a href='#' id='createOrderRecord' patient_visit_id='{$row['patient_visit_id']}' class='btn btn-info'>Generate Bill</a>";
        }

        $cancelVisit = '';
        if($row['status'] != 'Cancelled'){
            $cancelVisit = "<a patient_visit_id='{$row['patient_visit_id']}' class='btn btn-danger cancelVisitRecord'>Cancel Visit</a>";
        }

        $gotoSearch = "
        <div class='floatbox editTopButtonActionDiv'>
            <div class='float_left'>
                {$generateOrder}
                {$gotoOrder}
                {$cancelVisit}
            </div>
            <div class='float_right createdModifiedEditTop'><b>Created By :</b> {$row['created_by']} on {$row['creation_date']}&nbsp;&nbsp;&nbsp;&nbsp;<b>Modified By:</b> {$row['modified_by']} {$row['modification_date']}</div>
        </div>";

        $SQLTreatment ="
        SELECT tv.*, t.title
        FROM `treatment_visit` tv
        LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
        WHERE tv.patient_visit_id = {$row['patient_visit_id']}
          AND tv.follow_up_date IS NOT NULL
        ORDER BY tv.follow_up_date
        ";
        $resultTreatment  = $db->sql_query($SQLTreatment);
        $treatmentTitle = '';
        while ($rowTv = $db->sql_fetchrow($resultTreatment)) {
            $follow_up_date = $fn->getCPDate($rowTv['follow_up_date'],"d-m-Y");
            $treatmentTitle .= $rowTv['title'] .' - '. $follow_up_date . '<br>';
        }

        $SQLPatientVisit = "
        SELECT pv.*
        FROM patient_visit pv
        WHERE pv.patient_information_id = '{$row['patient_information_id']}'
        ORDER BY check_up_date DESC
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        $employee_id_count = '';
        $treatment = '';
        $employeeTitle = '';
        $PvText = '';
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            $SQLEV = "
            SELECT Distinct ev.employee_id
                  ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
                  ,ev.patient_visit_id
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE ev.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY employee_name
            ";
            $resultEv = $db->sql_query($SQLEV);
            $drAttended = '';
            while ($rowEv = $db->sql_fetchrow($resultEv)) {
                $drAttended .=$rowEv['employee_name'] . ', ';
            }
            $dr_attended = rtrim($drAttended,', ');

            $SQLTV = "
            SELECT tv.treatment_id
                  ,t.title
                  ,tv.patient_visit_id
            FROM treatment_visit tv
            LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
            WHERE tv.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY tv.treatment_id
            ";
            $resultTv = $db->sql_query($SQLTV);
            $pvTreatment = '';
            while ($rowTv = $db->sql_fetchrow($resultTv)) {
                $pvTreatment .=$rowTv['title'] . ', ';
            }
            $pv_treatment = rtrim($pvTreatment,', ');
            $check_up_date = $fn->getCPDate($rowPv['check_up_date'],"d-m-Y");

            $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$rowPv['patient_visit_id']}'");
            $total_invoice_amount = '0.00';
            $invoiced_Paid_Amount = '0.00';
            $balance_Amount = '0.00';

            if($orderRec['order_id'] != ''){
                $subSqlForPercentSum = "
                SELECT o.*
                      ,(SELECT SUM(invHist.amount) AS prev_sum
                        FROM invoice_receipt_history invHist
                        LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                        LEFT JOIN `invoice` i ON (i.order_id = {$orderRec['order_id']})
                        WHERE invHist.invoice_id =  i.invoice_id
                        AND r.receipt_status != 'Cancelled'
                        AND i.status != 'Cancelled'
                        ) as Amount_Paid
                     ,(SELECT SUM(inv.invoice_amount)
                        FROM invoice inv
                        WHERE inv.order_id = o.order_id AND
                        inv.status != 'Cancelled'
                          ) as total_invoice_amount
                FROM `order`o
                WHERE o.order_id = {$orderRec['order_id']}
                ";
                $resultSubSql = $db->sql_query($subSqlForPercentSum);
                $rowSql       = $db->sql_fetchrow($resultSubSql);

                if($rowSql['total_invoice_amount'] != ''){
                    $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
                    $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
                    $balance_Amount = number_format($balance_Amount, 2);
                    $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                    $total_invoice_amount = number_format($total_invoice_amount, 2);
                }else{
                    $total_invoice_amount = $rowSql['total_invoice_amount'];
                    $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                    $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
                    $balance_Amount = number_format($balance_Amount, 2);
                    $total_invoice_amount = number_format($total_invoice_amount, 2);
                }
            }

            $visit_code_Link = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPv['patient_visit_id']}";
            $visit_codePVt = "<a href='{$visit_code_Link}' target='_blank'>VST- {$rowPv['visit_code']}</a>";

            $bgColorBalance = '';
            if($balance_Amount > 0){
                $bgColorBalance = "bgcolor='#BCFDFD'";
            }

            $PvText .= "
            <tr {$bgColorBalance}>
                <td>{$visit_codePVt}</td>
                <td>{$check_up_date}</td>
                <td>{$dr_attended}</td>
                <td>{$pv_treatment}</td>
                <td>{$total_invoice_amount}</td>
                <td>{$invoiced_Paid_Amount}</td>
                <td>{$balance_Amount}</td>
            </tr>
            ";
        }


        /*if($row['bill_type'] == 'Company' || $row['bill_type'] == 'Panel'){
            $sqlCompany ="
            SELECT company_name
            FROM company
            WHERE company_id = '{$row['company_id']}'
            ";
            $resultCompany = $db->sql_query($sqlCompany);
            $rowCompany    = $db->sql_fetchrow($resultCompany);
            if($rowCompany['company_name'] != ''){
                $row['bill_type'] = $rowCompany['company_name'].'('.$row['bill_type'].')';
            }
            else{
                $row['bill_type'] = $row['bill_type'];
            }
        }*/

        $visit_code = '';
        if($row['visit_code'] != ''){
            $visit_code = 'VST-'.$row['visit_code'];
        }

        $age = '';
        if($row['dob'] != ''){
            $dob = $fn->getCPDate($row['dob'], 'Y');
            $age = date('Y')- $dob;
        }


        $appointmentRec = $fn->getRecordByCondition('appointment', "source_patient_visit_id = '{$row['patient_visit_id']}'");
        $createAppointmentLabel = 'Create Appointment';

        $appointmentDr_Linked = $appointmentRec['dr_Linked'];
        if($appointmentDr_Linked == ''){
            $SQLEmpVisit = "
            SELECT employee_id
            FROM employee_visit
            WHERE patient_visit_id = {$row['patient_visit_id']}
            ORDER BY employee_visit_id
            ";
            $resultEmpVisit = $db->sql_query($SQLEmpVisit);
            $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);
            $appointmentDr_Linked = $rowEmpVisit['employee_id'];
        }

        if(is_array($appointmentRec)){
            $createAppointmentLabel = 'Appointment Created';
        }

        $patient_Link = "index.php?_topRm=main&module=hms_patientInformation&_action=edit&patient_information_id={$row['patient_information_id']}";
        $patient_name = "<a href='{$patient_Link}' target='_blank'><u>{$row['patient_name']}</u></a>";

        $sqlEmployee = "
        SELECT employee_id
              ,CONCAT_WS(' ', first_name, middle_name, last_name) AS employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $status  = $fn->getReqParam('status');
        $recordTypeArray = array(
            "By Appointment"
           ,"Walk In"
        );

        $statusArray = array(
            "status"
           ,"New"
           ,"Visited Dr"
           ,"Bill Due"
           ,"Closed"
           ,"On Hold"
           ,"Cancelled"
        );

        $expComp  = array('detailValue' => $row['company_name']);

        $categoryDDRLabel    = 'Client Name';
        $showHideForBillType = '';
        $showHideForAppointmentType = 'displayNone';
        if($row['bill_type'] == 'Company'){
            $categoryForDDR = 'Client';
            $showHideForBillType = 'displayNone';
            $showHideForAppointmentType = '';
        }elseif ($row['bill_type'] == 'Panel') {
            $categoryForDDR = $row['bill_type'];
            $categoryDDRLabel = 'Panel Name';
            $showHideForBillType = 'displayNone';
            $showHideForAppointmentType = '';
        }else{
            $categoryForDDR = $row['bill_type'];
        }

        $sqlCompany = "
        SELECT company_id
               ,company_name
        FROM company
        WHERE category = '{$categoryForDDR}'
        ORDER BY company_name
        ";

        $text = "
        {$gotoSearch}
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Patient Visit Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Visit Code: {$visit_code}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='20%'>{$formObj->getTBRow('Name', 'name', $patient_name, $expNoEdit)}</td>
                                <td width='20%'>{$formObj->getTBRow('Nric', 'nric', $row['nric'], $expNoEdit)}</td>
                                <td width='20%'>{$formObj->getTBRow('Mobile', 'mobile', $row['mobile'], $expNoEdit)}</td>
                                <td width='20%'>{$formObj->getTBRow('Age', 'dob', $age, $expNoEdit)}</td>
                                <td width='20%'>{$formObj->getYesNoRRow('Dr Required', 'dr_required', $row['dr_required'], $expNoEdit)}</td>
                            </tr>

                            <tr>
                                <td width='20%'>{$formObj->getDateRow('Check Up Date (YYYY-MM-DD)', 'check_up_date', $row['check_up_date'])}</td>
                                <td width='20%'>{$formObj->getTimeRow('Check Up Time', 'check_up_time', $row['check_up_time'])}</td>
                                <td width='20%'>{$formObj->getDDRowBySQL('Bill Type *', 'bill_type', $sqlBillType, $row['bill_type'], $expVl)}</td>
                                <td class='{$showHideForAppointmentType} showHideForAppointmentType'>{$formObj->getDDRowBySQL($categoryDDRLabel, 'company_id', $sqlCompany, $row['company_id'], $expComp)}</td>
                                <td width='20%'>{$formObj->getDDRowByArr('Status', 'status', $statusArray, $row['status'])}</td>
                                <td width='20%' class='notesTitle showHideForBillType {$showHideForBillType} boldFormObjField' width='25%'>
                                    {$formObj->getDDRowByArr('By Appointment/Walk In', 'record_type', $recordTypeArray, $row['record_type'])}
                                </td>
                            </tr>
                            <tr>
                                <td width='20%' class='notesTitle showHideForAppointmentType {$showHideForAppointmentType} boldFormObjField' width='25%'>
                                    {$formObj->getDDRowByArr('By Appointment/Walk In', 'record_type', $recordTypeArray, $row['record_type'])}
                                </td>
                                <td class='notesTitle' colspan='2'>{$formObj->getTARow('Notes', 'notes', $row['notes'])}</td>
                                <td class='notesTitle' width='20%'>
                                    <label>Chart</label>
                                    <a class='perio_chart_link' href='#' patient_visit_id='{$row['patient_visit_id']}'>DMFT/DMFS Chart</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class='linkPortalWrapper followupToggleTab'>
                        <div expanded='0' class='header followupToggleHeading'>
                            <div class='floatbox'>
                                <div class='float_left followupToggleHeadingText'>Follow Up Details (Click the '+' sign to add appointments)</div>
                                <div class='toggle'></div>
                            </div>
                        </div>
                        <div>
                            <div class='linkPortalDataWrapper'>
                                <table class='thinlist followupTab'>
                                    <tr>
                                        <td width='25%'>
                                            <div class='patientVisitAppointmentDetails'>{$createAppointmentLabel}</div>
                                            {$formObj->getDateRow('Checkup Date (YYYY-MM-DD)', 'appointment_check_up_date', $appointmentRec['check_up_date'])}
                                            {$formObj->getTimeRow('Checkup Time', 'appointment_check_up_time', $appointmentRec['check_up_time'])}
                                            {$formObj->getDDRowBySQL('Doctor/Nurse', 'appointment_employee_id', $sqlEmployee, $appointmentDr_Linked)}
                                            {$formObj->getTARow('Notes', 'appointment_description', $appointmentRec['description'])}
                                            <input type='hidden' name='patient_information_id' value='{$row['patient_information_id']}' />
                                        </td>
                                        <td width='25%'>
                                            {$formObj->getDropDownRowByArray('Follow Up Date', 'follow_up_value', $followUp, $row['follow_up_value'], $expArr)}
                                            {$formObj->getDateRow('(YYYY-MM-DD)', 'follow_up_date', $row['follow_up_date'])}
                                            {$formObj->getTARow('Notes', 'follow_up_notes', $row['follow_up_notes'])}
                                        </td>
                                        <td width='25%'>
                                            {$formObj->getDropDownRowByArray('Longtime Follow Up', 'longtime_follow_up_value', $followUp, $row['longtime_follow_up_value'], $expArr)}
                                            {$formObj->getDateRow('(YYYY-MM-DD)', 'longtime_follow_up_date', $row['longtime_follow_up_date'])}
                                            {$formObj->getTARow('Notes', 'longtime_follow_up_notes', $row['longtime_follow_up_notes'])}
                                            <input type='hidden' name='patient_visit_id' value='{$row['patient_visit_id']}' />
                                        </td>
                                        <td width='25%'>Treatment Follow Up <br> {$treatmentTitle}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            {$this->getOverallTabsSummary($row['patient_visit_id'])}
        </div>

        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Treatment Summary</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <div id='tabs' class='mb20'>
                        <ul>
                            {$medHisLbl}
                            {$addDrLbl}
                            {$oralHygienicLbl}
                            {$treatmentLbl}
                            {$medicineLbl}
                            {$labTestLbl}
                            {$labsLbl}
                            {$summaryLbl}
                            {$medicalCertficateLbl}
                        </ul>
                        {$medHisTab}
                        {$addDrTab}
                        {$oralHygienicTab}
                        {$treatmentTab}
                        {$medicineTab}
                        {$labTestTab}
                        {$labsTab}
                        {$summaryTab}
                        {$medicalCertficateTab}
                        <div class='tab-footer'>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id='patientVisitSummaryPortal'>
            {$this->getPatientVisitSummaryPortal($row['patient_information_id'])}
        </div>

        <div id='patientVisitInvoicePortal'>
            {$invoicePortalDisplay}
        </div>

        {$actionButtons}
        <div id='patientVisitReceiptPortal'>{$receiptPortalDisplay}</div>
        <input type='hidden' id='fld_order_id' name='order_id' value='{$rowOrder['order_id']}' />
        ";

        return $text;
    }


    /**
     *
     */
    function getPatientVisitSummaryPortal($patient_information_id=''){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $site_id = $fn->getSessionParam('cp_site_id');

        if($patient_information_id == ''){
            $patient_information_id = $fn->getReqParam('patient_information_id');
        }

        $patientVisitSummary_type_val = $fn->getReqParam('patientVisitSummary_type');

        if($patientVisitSummary_type_val == ''){
            $patientVisitSummary_type_val = 'Due';
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = " AND pv.site_id = {$site_id}";
        }

        $SQLPatientVisit = "
        SELECT pv.*
        FROM patient_visit pv
        WHERE pv.patient_information_id = '{$patient_information_id}'
        AND pv.status != 'Cancelled'
        {$appendSql}
        ORDER BY check_up_date DESC
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        $employee_id_count = '';
        $treatment = '';
        $employeeTitle = '';
        $PvText = '';
        $balance_Amount = '0.00';
        $overall_balance_Amount = '0.00';
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            $SQLEV = "
            SELECT Distinct ev.employee_id
                  ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
                  ,ev.patient_visit_id
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE ev.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY employee_name
            ";
            $resultEv = $db->sql_query($SQLEV);
            $drAttended = '';
            while ($rowEv = $db->sql_fetchrow($resultEv)) {
                $drAttended .=$rowEv['employee_name'] . ', ';
            }
            $dr_attended = rtrim($drAttended,', ');

            $SQLTV = "
            SELECT tv.treatment_id
                  ,t.title
                  ,tv.patient_visit_id
            FROM treatment_visit tv
            LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
            WHERE tv.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY tv.treatment_id
            ";
            $resultTv = $db->sql_query($SQLTV);
            $pvTreatment = '';
            while ($rowTv = $db->sql_fetchrow($resultTv)) {
                $pvTreatment .=$rowTv['title'] . ', ';
            }
            $pv_treatment = rtrim($pvTreatment,', ');
            $check_up_date = $fn->getCPDate($rowPv['check_up_date'],"d-m-Y");

            $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$rowPv['patient_visit_id']}'");
            $total_invoice_amount = '0.00';
            $invoiced_Paid_Amount = '0.00';

            if($orderRec['order_id'] != ''){
                $subSqlForPercentSum = "
                SELECT o.*
                      ,(SELECT SUM(invHist.amount) AS prev_sum
                        FROM invoice_receipt_history invHist
                        LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                        LEFT JOIN `invoice` i ON (i.order_id = {$orderRec['order_id']})
                        WHERE invHist.related_invoice_id =  i.invoice_id
                        AND r.receipt_status != 'Cancelled'
                        AND i.status != 'Cancelled'
                        ) as Amount_Paid
                     ,(SELECT SUM(inv.invoice_amount)
                        FROM invoice inv
                        WHERE inv.order_id = o.order_id AND
                        inv.status != 'Cancelled'
                          ) as total_invoice_amount
                FROM `order`o
                WHERE o.order_id = {$orderRec['order_id']}
                ";
                $resultSubSql = $db->sql_query($subSqlForPercentSum);
                $rowSql       = $db->sql_fetchrow($resultSubSql);

                if($rowSql['total_invoice_amount'] != ''){
                    $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
                    $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
                    $balance_Amount = number_format($balance_Amount, 2);
                    $overall_balance_Amount += $balance_Amount;
                    $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                    $total_invoice_amount = number_format($total_invoice_amount, 2);
                }else{
                    $total_invoice_amount = $rowSql['total_invoice_amount'];
                    $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                    $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
                    $overall_balance_Amount += $balance_Amount;
                    $balance_Amount = number_format($balance_Amount, 2);
                    $total_invoice_amount = number_format($total_invoice_amount, 2);
                }
            }

            $overall_balance_Amount = number_format($overall_balance_Amount, 2);

            $visit_code_Link = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPv['patient_visit_id']}";
            $visit_codePVt = "<a href='{$visit_code_Link}' target='_blank'>VST- {$rowPv['visit_code']}</a>";

            $viewSummaryTreatmentLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=viewSummaryTreatment&patient_visit_id={$rowPv['patient_visit_id']}&showHTML=0";
            $viewSummaryTreatment = "<a href='{$viewSummaryTreatmentLink}' class='viewSummaryForTreatmentRecord'><u>View Summary</u></a>";

            $bgColorBalance = '';
            if($balance_Amount > 0){
                $bgColorBalance = "bgcolor='#BCFDFD'";
            }

            if($patientVisitSummary_type_val == 'Due'){
                if($balance_Amount > 0){
                    $PvText .= "
                    <tr {$bgColorBalance}>
                        <td>{$visit_codePVt}</td>
                        <td>{$check_up_date}</td>
                        <td>{$dr_attended}</td>
                        <td>{$viewSummaryTreatment}</td>
                        <td>{$total_invoice_amount}</td>
                        <td>{$invoiced_Paid_Amount}</td>
                        <td>{$balance_Amount}</td>
                    </tr>
                    ";
                }
            }else{
                $PvText .= "
                <tr {$bgColorBalance}>
                    <td>{$visit_codePVt}</td>
                    <td>{$check_up_date}</td>
                    <td>{$dr_attended}</td>
                    <td>{$viewSummaryTreatment}</td>
                    <td>{$total_invoice_amount}</td>
                    <td>{$invoiced_Paid_Amount}</td>
                    <td>{$balance_Amount}</td>
                </tr>
                ";
            }
        }


        //<div class='float_left patientVisitSummary_Filter'>{$formObj->getDDRowByArr('Display payment due records', 'patientVisitSummary_type', $patientVisitSummary_type, $patientVisitSummary_type_val)}</div>
        $linkDisplayText = "Display payment due records";
        if($patientVisitSummary_type_val == 'Due'){
            $linkDisplayText = "Show All Records";
        }

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Patient Visit History</div>
                    <div class='float_left InvoiceToggleHeading'>- Overall Due : {$overall_balance_Amount}</div>
                    <div class='float_left patientVisitSummary_Filter'><a href='#' class='patientVisitSummary_type'>{$linkDisplayText}</a></div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper patientVisitSummaryPortal'>
                    <table class='thinlist mb20 visitSummary'>
                        <thead>
                            <tr>
                                <th class='label'>Visit Code</td>
                                <th class='label'>Date</td>
                                <th class='label'>Dr Attended</td>
                                <th class='label'>Treatment</td>
                                <th class='label'>Total Amount</td>
                                <th class='label'>Paid</td>
                                <th class='label'>Balance</td>
                            </tr>
                        </thead>
                        <tbody>
                            {$PvText}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <input type='hidden' id='fld_patient_information_id' value='{$patient_information_id}'>
        ";

        return $text;
    }
    /**
     *
     */
    function getviewSummaryTreatment() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');

            $SQLTV = "
            SELECT tv.treatment_id
                  ,t.title
                  ,tv.patient_visit_id
                  ,tv.notes AS treatment_notes
                  ,pv.notes
                  ,pv.complain
                  ,pv.treatment_summary                  
                  ,mv.title AS medicines_name
                  ,CONCAT_WS(', ', mv.dosage, mv.days) AS medicines_desc
                  ,mv.instruction
                  ,mv.days
                  ,mv.qty
                  ,mv.dosage
            FROM treatment_visit tv
            LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = tv.patient_visit_id)
            LEFT JOIN (medicines_visit mv) ON (mv.patient_visit_id = pv.patient_visit_id)
            WHERE tv.patient_visit_id = '{$patient_visit_id}'
            ";
            $resultTv = $db->sql_query($SQLTV);
            $rows = '';
            $count = 1;
            while($rowTv = $db->sql_fetchrow($resultTv)){
                $rows .= "
                <tr height='30px'>
                    <td><b>Main Notes</b></td>
                    <td>{$rowTv['notes']}</td>
                </tr>
                <tr height='30px'>
                    <td><b>Treatment</b></td>
                    <td></td>
                </tr>
                <tr height='30px'>
                    <td>{$rowTv['title']}</td>
                    <td>{$rowTv['treatment_notes']}</td>
                </tr>
                <tr height='30px'>
                    <td><b>Medicines</b></td>
                    <td></td>
                </tr>
                <tr height='30px'>
                    <td>{$rowTv['medicines_name']}</td>
                    <td>{$rowTv['medicines_desc']} days</td>
                </tr>
                <tr height='30px'>
                    <td><b>Summary</b></td>
                    <td></td>
                </tr>
                <tr height='30px'>
                    <td>Complain</td>
                    <td>{$rowTv['complain']}</td>
                </tr>
                <tr height='30px'>
                    <td>Treatment Summary</td>
                    <td>{$rowTv['treatment_summary']}</td>
                </tr>
                ";
            }

        $text = "
        <table class='thinlist'>
            <thead>
                <tr height='30px'>
                    <th>Title</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";

        return $text;
    }
    /**
     *
     */
    function getOverallTabsSummary($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $text= '';

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }
        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $SQLPatientVisit = "
        SELECT pv.*
        FROM patient_visit pv
        WHERE pv.patient_information_id = '{$patientVisitRec['patient_information_id']}'
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        $PvText = '';
        $MhText = '';
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            $SQLMH = "
            SELECT mh.medical_history_information_id
                  ,mh.title
                  ,mh.patient_visit_id
            FROM medical_history_information mh
            WHERE mh.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY mh.medical_history_information_id
            ";
            $resultMh = $db->sql_query($SQLMH);
            $pvMedHis = '';
            while ($rowMh = $db->sql_fetchrow($resultMh)) {
                $pvMedHis .=$rowMh['title'] . '<br> ';
            }
            $MhText .= "
            <div class=''>{$pvMedHis}</div>
            ";

            $SQLTV = "
            SELECT tv.treatment_id
                  ,t.title
                  ,tv.patient_visit_id
            FROM treatment_visit tv
            LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
            WHERE tv.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY tv.treatment_id
            ";
            $resultTv = $db->sql_query($SQLTV);
            $pvTreatment = '';
            while ($rowTv = $db->sql_fetchrow($resultTv)) {
                $pvTreatment .=$rowTv['title'] . '<br> ';
            }
            $PvText .= "
            <div class=''>{$pvTreatment}</div>
            ";
        }
        /*$text="
        {$pvMedHis}
        {$PvText}
        ";*/

        return $text;
    }

    /**
     *
     */
    function getDoctorPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";

        $SQL = "
        SELECT ev.*
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
              ,e.category
        FROM employee_visit ev
        LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
        WHERE ev.patient_visit_id = {$patient_visit_id}
        ORDER BY ev.employee_visit_id
        ";
        $result   = $db->sql_query($SQL);

        while ($rowEV = $db->sql_fetchrow($result)) {
            $editURL = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editDoctorRecord&showHTML=0&patient_visit_id={$rowEV['patient_visit_id']}&employee_visit_id={$rowEV['employee_visit_id']}";
            $editRow = "<td><a href='{$editURL}' id='editDoctorRecord' patient_visit_id={$rowEV['patient_visit_id']}><u>Edit</u></a></td>";
            $rows .= "
            <tr>
                <td>{$rowEV['category']}</td>
                <td>{$rowEV['employee_name']}</td>
                <td>{$rowEV['consultation_fees']}</td>
                <td>{$rowEV['consultation_room']}</td>
                <td>{$rowEV['notes']}</td>
                {$editRow}
                <td><a href='#' class='deleteDoctorRecord' employee_visit_id='{$rowEV['employee_visit_id']}' patient_visit_id={$rowEV['patient_visit_id']}><u>Delete</u></a></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Dr/Nurse</th>
        <th>Name</th>
        <th>Consulting Fees</th>
        <th>Room</th>
        <th>Notes</th>
        <th>Edit</th>
        <th>Delete</th>
        </tr>
        ";

        $text = "
        <div id='' class='doctorDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='doctorPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMedicinesPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";
        $stock = '';

        $sqlInstruction = $fn->getValueListSQL('instruction');

        $sqlDoctor = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
        FROM employee e
        WHERE position IN ('Doctor', 'Nurse')
        ORDER BY e.employee_id
        ";

        $SQL = "
        SELECT mv.*
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
              ,mv.product_id
        FROM medicines_visit mv
        LEFT JOIN (employee e) ON (e.employee_id = mv.employee_id)
        WHERE mv.patient_visit_id = {$patient_visit_id}
        ORDER BY mv.medicines_visit_id
        ";
        $result   = $db->sql_query($SQL);

        while ($rowMV = $db->sql_fetchrow($result)) {
            $editURL = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editMedicineRecord&showHTML=0&patient_visit_id={$rowMV['patient_visit_id']}&medicines_visit_id={$rowMV['medicines_visit_id']}";
            $editRow = "<td><a href='{$editURL}' id='editMedicineRecord' patient_visit_id={$rowMV['patient_visit_id']}>Edit</a></td>";

            if($rowMV['product_id'] != ''){
                $SQLStockTransfer = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$rowMV['product_id']} AND st.from_location = {$cpSiteIdSession}";

                $resultStockTransfer = $db->sql_query($SQLStockTransfer);
                $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);

                $SQLStockTransferto = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty_to
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$rowMV['product_id']} AND st.to_location = {$cpSiteIdSession}";

                $resultStockTransferto = $db->sql_query($SQLStockTransferto);
                $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

                $SQLOthersite = "
                SELECT
                    (SELECT SUM(qty) FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = {$rowMV['product_id']} AND po.site_id = {$cpSiteIdSession}) as product_qty_purchased

                   ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE record_id = {$rowMV['product_id']}
                      AND o.site_id = {$cpSiteIdSession}
                    ) as product_qty_sold_from_quote

                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = {$rowMV['product_id']}
                    AND inv.site_id = {$cpSiteIdSession}
                    ) as sales_return_qty
                ";
                $resultothersite = $db->sql_query($SQLOthersite);
                $rowothersite = $db->sql_fetchrow($resultothersite);


                $SqlExpenseProduct = "
                SELECT SUM(ep.qty) AS qty
                FROM expense_product ep
                LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
                WHERE ep.product_id = {$rowMV['product_id']}
                AND ep.status = 'Added'
                AND e.site_id = {$cpSiteIdSession}
                AND ep.stock_deducted = 1
                ";
                $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
                $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

                $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];
            }

            $rows .= "
            <tr recid='{$rowMV['medicines_visit_id']}' product_id='{$rowMV['product_id']}' class='portal-row2 row-hms_patientVisit__hms_product'>
                <td class='title'>
                    <input type='text' value='{$rowMV['title']}' name='title'>
                </td>
                <td class='dosage'>
                    <input type='text' value='{$rowMV['dosage']}' name='dosage'>
                </td>
                <td class='instruction'>
                    <select name='instruction'>
                        <option value=''>Please Select</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlInstruction, $rowMV['instruction'])}
                    </select>
                </td>
                <td class='days'>
                    <input type='text' value='{$rowMV['days']}' name='days'>
                </td>
                <td class='selling-price'>
                    <input type='text' value='{$rowMV['selling_price']}' name='selling_price'>
                </td>
                <td class='qty'>
                    <input type='text' id='fld_medicineQty_{$rowMV['medicines_visit_id']}' stock='{$stock}' previousQtyValue='{$rowMV['qty']}' value='{$rowMV['qty']}' name='qty' />
                </td>
                <td class='employee_id'>
                    <select name='employee_id'>
                        <option value=''>Please Select</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlDoctor, $rowMV['employee_id'])}
                    </select>
                </td>
                <td><a href='#' class='deleteMedicineRecord' medicines_visit_id='{$rowMV['medicines_visit_id']}' patient_visit_id={$rowMV['patient_visit_id']}><u>Delete</u></a></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th width='26%'>Medicine Name</th>
        <th width='10%'>Dosage</th>
        <th width='12%'>Instruction</th>
        <th width='10%'>Days</th>
        <th width='10%'>Price</th>
        <th width='10%'>Qty</th>
        <th width='12%'>Dr</th>
        <th width='10%'>Delete</th>
        </tr>
        ";

        $text = "
        <div id='' class='doctorDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='doctorPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     */
    function getLabPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=labRecordSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

        $MedHisArray = array(
                            'Cardiac Comp'
                           ,'Hypertension'
                           ,'Blood Disorders'
                           ,'Diabetes'
                           ,'Jaundice'
                           ,'Pregnant'
                           );

        $count = 0;
        foreach ($MedHisArray as $key=>$value){
            $labVisitRec = $fn->getRecordByCondition('lab_visit', "patient_visit_id = '{$patient_visit_id}' AND title='{$value}'");

            if($labVisitRec['lab_visit_id'] != ''){
                $checked = "checked='checked'";
                $class ="";
            } else {
                $checked = '';
                $class ="displayNone";
            }

            if($labVisitRec['notes'] != ''){
                $notes = 'View Notes';
            }else {
                $notes = 'Add Notes';
            }

            $addNoteUrl = "index.php?module=hms_patientVisit&_spAction=addNoteLab&lab_visit_id={$labVisitRec['lab_visit_id']}&showHTML=0";
            $inputRow .= "
            <div class='c33l'>
                <div class='type-check ym-fbox-check labTestBox'>
                    <input type='checkbox' id='title_{$count}' {$checked} value='{$value}_{$count}' name='title[]' class='labTitle'>
                    <label for='title_{$count}'>{$value}</label>
                    <div class='hideTreatmentDetails_{$value}_{$count} hideLabDetails {$class} labVisitNotes'>
                        <input type='text' value='{$labVisitRec['fees']}' id='fld_fees' class='text mt10 mb10' name='fees[]'>
                        <div><a href='#' class='addNoteLab'>{$notes}</a></div>
                        <div class='hideNotesLab'>
                            <div class='type-text ym-fbox-text row_notes'>
                                <textarea id='fld_notes' name='notes[]'>{$labVisitRec['notes']}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ";
            $count ++;
        }

        $text = "
        <div id='' class=''>
            <form id='portalForm_labDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='floatbox'>{$inputRow}</div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddNoteLab() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $lab_visit_id = $fn->getReqParam('lab_visit_id');

        $formAction = "index.php?module=hms_patientVisit&_spAction=addNoteLabSubmit&showHTML=0";
        $labVisitRec = $fn->getRecordRowByID('lab_visit', 'lab_visit_id', $lab_visit_id);

        $text = "
        <form id='portalForm' class='yform columnar addNoteForm' method='post' action='{$formAction}'>
            {$formObj->getTARow('Notes', 'notes', $labVisitRec['notes'])}
            <input type='hidden' name='lab_visit_id' value='{$lab_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getLabsDisplay($patient_visit_id='') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";

        $SQL = "
        SELECT  l.supplier_category
               ,ls.title
               ,l.labs_id
               ,l.patient_visit_id
               ,l.labs_code
               ,l.order_id
               ,l.amount
        FROM labs l
        LEFT JOIN (labs_supplier ls) ON (ls.labs_supplier_id = l.supplier_id)
        WHERE l.patient_visit_id = {$patient_visit_id}
        ORDER BY l.labs_id
        ";
        $result   = $db->sql_query($SQL);
        $serialNo = 1;
        while ($rowL = $db->sql_fetchrow($result)) {
            $supplier_category_link = '';
            $supplier_category = '';

            $receiptRec = $fn->getRecordByCondition('payments_receipt', "order_id != '' AND labs_id = '{$rowL['labs_id']}' AND receipt_status != 'Cancelled'");
            if($receiptRec){
                $supplier_category = "<a href='#' id='supplier_categoryFormLink'><u>View Form</u></a>";
                $editRow = "<a href='#' id='supplier_categoryFormLink'><u>Edit</u></a>";
                $viewSummaryLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=viewSummaryLabs&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&order_id={$rowL['order_id']}&labs_id={$rowL['labs_id']}";
                $viewSummary = "<a href='{$viewSummaryLink}' class='viewSummaryForLabsRecord'><u>View Summary</u></a>";
                $deleteLink   = "<a href='#' id='supplier_DeleteLink'><u>Delete</u></a>";
            }else{
                $editURL = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editLabsRecord&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                $editRow = "<a href='{$editURL}' id='editLabsRecord' patient_visit_id={$rowL['patient_visit_id']}><u>Edit</u></a>";

                if($rowL['supplier_category'] == 'Acrylic'){
                    $supplier_category_link = "index.php?_topRm=main&module=hms_patientVisit&_spAction=acrylicDentureForm&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                    $supplier_category = "<a href='{$supplier_category_link}' id='acrylicFormDenture' patient_visit_id={$rowL['patient_visit_id']}><u>View Form</u></a>";
                }else if($rowL['supplier_category'] == 'Ceramic'){
                    $supplier_category_link = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addCeramicForm&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                    $supplier_category = "<a href='{$supplier_category_link}' id='addCeramicForm' patient_visit_id={$rowL['patient_visit_id']}><u>View Form</u></a>";

                }else if($rowL['supplier_category'] == 'Orthodontic'){
                    $supplier_category_link = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addOrthodonticForm&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                    $supplier_category = "<a href='{$supplier_category_link}' id='addOrthodontic' patient_visit_id={$rowL['patient_visit_id']}><u>View Form</u></a>";

                }

                $viewSummary = "<a href='#' id='generatenoReceipt'><u>View Summary</u></a>";
                $deleteLink   = "<a href='#' class='deleteLabsRecord' labs_id='{$rowL['labs_id']}' patient_visit_id={$rowL['patient_visit_id']}><u>Delete</u></a>";
            }

            $labsCodeLink = "index.php?_topRm=inventory&module=hms_labs&_action=edit&labs_id={$rowL['labs_id']}";
            $LabsCode = "<a href='{$labsCodeLink}'><u>LB - {$rowL['labs_code']}</u></a>";

            if($rowL['amount'] == ''){
                $rowL['amount'] = 0;
            }

            $labsAmount = number_format($rowL['amount'], 2);
            $rows .= "
            <tr>
                <td>{$serialNo}</td>
                <td>{$LabsCode}</td>
                <td>{$rowL['title']}</td>
                <td>{$rowL['supplier_category']} - {$supplier_category}</td>
                <td>{$labsAmount}</td>
                <td>{$editRow}</td>
                <td>{$deleteLink}</td>
                <td>{$viewSummary}</td>
            </tr>
            ";
            $serialNo++;
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>S.No</th>
        <th>Labs Code</th>
        <th>Supplier Name</th>
        <th>Category</th>
        <th>Amount</th>
        <th>Edit</th>
        <th>Delete</th>
        <th>View Summary</th>
        </tr>
        ";

        $text = "
        <div id='' class='labsDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='labsPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddCeramicForm(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $porcelainArr = array('CROWN'
                             ,'BRIDGE'
                             ,'MARYLAND BRIDGE');

        $fullPorcelainArr = array('CROWN'
                                 ,'BRIDGE'
                                 ,'INLAY / ONLAY'
                                 ,'VENEER');

        $fullMetalArr = array('CROWN'
                             ,'BRIDGE'
                             ,'INLAY / ONLAY'
                             ,'POST-CORE');

        $marginArr = array('METAR COLLAR'
                          ,'NO METAR COLLAR'
                          ,'PORCELAIN MARGIN');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $labs_id          = $fn->getReqParam('labs_id');
        $form_type        = 'Ceramic';

        $rowlabs = $fn->getRecordRowByID('labs', 'labs_id', $labs_id);

        $toothlist2ArrCheckbox = $this->getToothlistFirst($patient_visit_id, $form_type, $labs_id);
        $toothlist3ArrCheckbox = $this->getToothlistSecond($patient_visit_id, $form_type, $labs_id);

        $count = 1;
        $porcelainArrResult = '';

        foreach($porcelainArr as $value){
            $SQLCeramic = "
            SELECT labs_id
            FROM labs_history
            WHERE labs_id = '{$labs_id}'
            AND form_type = '{$form_type}'
            AND title = '{$value}'
            AND title_group = 'PORCELAIN BONDED'
            ";
            $resultCeramic = $db->sql_query($SQLCeramic);
            $checkedStatus = '';
            while($rowCeramic = $db->sql_fetchrow($resultCeramic)){
                $checkedStatus   = "checked = checked";
            }

            $porcelainArrResult .= "
            <div class='type-check ym-fbox-check'>
                <input type='checkbox' id='porcelain_bonded_{$count}' value='{$value}' name='porcelain_bonded[]' {$checkedStatus}>
                <label for='porcelain_bonded_{$count}'>{$value}</label>
            </div>
            ";

            $count++;
        }

        $count2 = 1;
        $fullPorcelainArrResult = '';

        foreach($fullPorcelainArr as $value){
            $SQLCeramic = "
            SELECT labs_id
            FROM labs_history
            WHERE labs_id = '{$labs_id}'
            AND form_type = '{$form_type}'
            AND title = '{$value}'
            AND title_group = 'FULL PORCELAIN (Emax)'
            ";
            $resultCeramic = $db->sql_query($SQLCeramic);
            $checkedStatus = '';
            while($rowCeramic = $db->sql_fetchrow($resultCeramic)){
                $checkedStatus   = "checked = checked";
            }

            $fullPorcelainArrResult .= "
            <div class='type-check ym-fbox-check'>
                <input type='checkbox' id='full_porcelain_{$count2}' value='{$value}' name='full_porcelain[]' {$checkedStatus}>
                <label for='full_porcelain_{$count2}'>{$value}</label>
            </div>
            ";

            $count2++;
        }

        $count3 = 1;
        $fullMetalArrResult = '';

        foreach($fullMetalArr as $value){
            $SQLCeramic = "
            SELECT labs_id
            FROM labs_history
            WHERE labs_id = '{$labs_id}'
            AND form_type = '{$form_type}'
            AND title = '{$value}'
            AND title_group = 'FULL METAL'
            ";
            $resultCeramic = $db->sql_query($SQLCeramic);
            $checkedStatus = '';
            while($rowCeramic = $db->sql_fetchrow($resultCeramic)){
                $checkedStatus   = "checked = checked";
            }

            $fullMetalArrResult .= "
            <div class='type-check ym-fbox-check'>
                <input type='checkbox' id='full_Metal_{$count3}' value='{$value}' name='full_Metal[]' {$checkedStatus}>
                <label for='full_Metal_{$count3}'>{$value}</label>
            </div>
            ";

            $count3++;
        }

        $count4 = 1;
        $marginArrResult = '';

        foreach($marginArr as $value){
            $SQLCeramic = "
            SELECT labs_id
            FROM labs_history
            WHERE labs_id = '{$labs_id}'
            AND form_type = '{$form_type}'
            AND title = '{$value}'
            AND title_group = 'MARGIN'
            ";
            $resultCeramic = $db->sql_query($SQLCeramic);
            $checkedStatus = '';
            while($rowCeramic = $db->sql_fetchrow($resultCeramic)){
                $checkedStatus   = "checked = checked";
            }

            $marginArrResult .= "
            <div class='type-check ym-fbox-check'>
                <input type='checkbox' id='Margin_Porcelain_{$count4}' value='{$value}' name='Margin_Porcelain[]' {$checkedStatus}>
                <label for='Margin_Porcelain_{$count4}'>{$value}</label>
            </div>
            ";

            $count4++;
        }

        $ponticArr = array('RIDGELAP'
                          ,'SANITARY'
                          ,'SADDLE');

        $count5 = 1;
        $ponticArrResult = '';

        foreach($ponticArr as $value){
            $SQLCeramic = "
            SELECT labs_id
            FROM labs_history
            WHERE labs_id = '{$labs_id}'
            AND form_type = '{$form_type}'
            AND title = '{$value}'
            AND title_group = 'PONTIC DESIGN'
            ";
            $resultCeramic = $db->sql_query($SQLCeramic);
            $checkedStatus = '';
            while($rowCeramic = $db->sql_fetchrow($resultCeramic)){
                $checkedStatus   = "checked = checked";
            }

            $ponticArrResult .= "
            <div class='type-check ym-fbox-check'>
                <input type='checkbox' id='pontic_{$count5}' value='{$value}' name='pontic[]' {$checkedStatus}>
                <label for='pontic_{$count5}'>{$value}</label>
            </div>
            ";

            $count5++;
        }

        $proximalArr = array('NORMAL'
                          ,'BROAD'
                          ,'POINT');

        $count6 = 1;
        $proximalArrResult = '';

        foreach($proximalArr as $value){
            $SQLCeramic = "
            SELECT labs_id
            FROM labs_history
            WHERE labs_id = '{$labs_id}'
            AND form_type = '{$form_type}'
            AND title = '{$value}'
            AND title_group = 'PROXIMAL CONTACT'
            ";
            $resultCeramic = $db->sql_query($SQLCeramic);
            $checkedStatus = '';
            while($rowCeramic = $db->sql_fetchrow($resultCeramic)){
                $checkedStatus   = "checked = checked";
            }

            $proximalArrResult .= "
            <div class='type-check ym-fbox-check'>
                <input type='checkbox' id='proximal_{$count6}' value='{$value}' name='proximal[]' {$checkedStatus}>
                <label for='proximal_{$count6}'>{$value}</label>
            </div>
            ";

            $count6++;
        }

        $textCheckBox = "
        <div class='form-row-wrapper checkboxGroupDiv'>
            <div class=''>
                <label for='fld_porcelain_bonded'>PORCELAIN BONDED</label>
            </div>
            <div class='rightCol'>
                {$porcelainArrResult}
                <input type='hidden' name='title_group_porcelain' value='PORCELAIN BONDED'/>
            </div>
        </div>
        <div class='form-row-wrapper checkboxGroupDiv'>
            <div class=''>
                <label for='fld_full_porcelain'>FULL PORCELAIN (Emax)</label>
            </div>
            <div class='rightCol'>
                {$fullPorcelainArrResult}
                <input type='hidden' name='title_group_fullporcelain' value='FULL PORCELAIN (Emax)'/>
            </div>
        </div>
        <div class='form-row-wrapper checkboxGroupDiv'>
            <div class=''>
                <label for='fld_full_Metal'>FULL METAL</label>
            </div>
            <div class='rightCol'>
                {$fullMetalArrResult}
                <input type='hidden' name='title_group_full_Metal' value='FULL METAL'/>
            </div>
        </div>
        ";

        $textCheckBox2 = "
        <div class='form-row-wrapper checkboxGroupDiv'>
            <div class=''>
                <label for='fld_Margin_Porcelain'>MARGIN</label>
            </div>
            <div class='rightCol'>
                {$marginArrResult}
                <input type='hidden' name='title_group_Margin' value='MARGIN'/>
            </div>
        </div>

        <div class='form-row-wrapper checkboxGroupDiv'>
            <div class=''>
                <label for='fld_Pontic'>PONTIC DESIGN</label>
            </div>
            <div class='rightCol'>
                {$ponticArrResult}
                <input type='hidden' name='title_group_Pontic' value='PONTIC DESIGN'/>
            </div>
        </div>
        <div class='form-row-wrapper checkboxGroupDiv'>
            <div class=''>
                <label for='fld_Proximal'>PROXIMAL CONTACT</label>
            </div>
            <div class='rightCol'>
                {$proximalArrResult}
                <input type='hidden' name='title_group_Proximal' value='PROXIMAL CONTACT'/>
            </div>
        </div>
        ";


        $formAction = 'index.php?module=hms_patientVisit&_spAction=addCeramicFormRecord&showHTML=0';
        $text = "
        <div id='' class=''>
            <form id='AddCeramicFormDetail' class='yform columnar' method='post' action='{$formAction}'>
                 <table class='thinlist acrylicDentureDate'>
                    <tr>
                        <td>{$formObj->getTBRow('Amount', 'amount', $rowlabs['amount'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getDateRow('Date Sent', 'date_sent', $rowlabs['date_sent'])}</td>
                        <td>{$formObj->getDateRow('Date Received', 'date_received', $rowlabs['date_received'])}</td>
                        <td>{$formObj->getDateRow('Date Due', 'date_due', $rowlabs['date_due'])}</td>
                    </tr>
                </table>
                {$formObj->getFieldSetWrapped('TYPE OF WORK', $textCheckBox)}
                {$formObj->getFieldSetWrapped('', $textCheckBox2)}
                <div class='toothSelectCheckbox2 newLineFornext'>
                        {$toothlist2ArrCheckbox}
                </div>
                <div class='toothSelectCheckbox3 newLineFornext'>
                        {$toothlist3ArrCheckbox}
                </div>
                <input type='hidden' id='fld_tooth_form_type' name='form_type' value='{$form_type}' />
                <input type='hidden' name='labs_id' id='fld_labs_id' value='{$labs_id}' />
                <input type='hidden' id='patient_visit_id' name='patient_visit_id' value='{$patient_visit_id}'>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddOrthodonticForm(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $chromeArr = array('UPPER', 'LOWER');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $labs_id          = $fn->getReqParam('labs_id');
        $form_type        = 'Orthodontic';

        $rowlabs = $fn->getRecordRowByID('labs', 'labs_id', $labs_id);

        $toothlist2ArrCheckbox = $this->getToothlistFirst($patient_visit_id, $form_type, $labs_id);
        $toothlist3ArrCheckbox = $this->getToothlistSecond($patient_visit_id, $form_type, $labs_id);

        $count = 1;
        $chromeArrCheckbox = '';

        foreach($chromeArr as $value){
            $SQLchrome = "
            SELECT labs_id
            FROM labs_history
            WHERE labs_id = '{$labs_id}'
            AND form_type = '{$form_type}'
            AND title = '{$value}'
            ";
            $resultchrome = $db->sql_query($SQLchrome);
            $checkedStatus = '';
            while($rowchrome = $db->sql_fetchrow($resultchrome)){
                $checkedStatus   = "checked = checked";
            }

            $chromeArrCheckbox .= "
            <div class='type-check ym-fbox-check'>
                <input type='checkbox' id='chrome_type_{$count}' value='{$value}' name='chrome_type[]' {$checkedStatus}>
                <label for='chrome_type_{$count}'>{$value}</label>
            </div>
            ";

            $count++;
        }

        $textCheckBox = "
        <div class='form-row-wrapper checkboxGroupDiv'>
            <div class=''>
                <label for='fld_chrome'>CHROME COBALT</label>
            </div>
            <div class='rightCol'>
                {$chromeArrCheckbox}
                <input type='hidden' name='title_group_psv' value='CHROME COBALT'/>
            </div>
        </div>
        ";


        $formAction = 'index.php?module=hms_patientVisit&_spAction=addChromeFormRecord&showHTML=0';
        $text = "
        <div id='' class=''>
            <form id='ChromeFormDetail' class='yform columnar' method='post' action='{$formAction}'>
                <table class='thinlist acrylicDentureDate'>
                    <tr>
                        <td>{$formObj->getTBRow('Amount', 'amount', $rowlabs['amount'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getDateRow('Date Sent', 'date_sent', $rowlabs['date_sent'])}</td>
                        <td>{$formObj->getDateRow('Date Received', 'date_received', $rowlabs['date_received'])}</td>
                        <td>{$formObj->getDateRow('Date Due', 'date_due', $rowlabs['date_due'])}</td>
                    </tr>
                </table>
                {$textCheckBox}
                <div class='toothSelectCheckbox2 newLineFornext'>
                        {$toothlist2ArrCheckbox}
                </div>
                <div class='toothSelectCheckbox3 newLineFornext'>
                        {$toothlist3ArrCheckbox}
                </div>
                <input type='hidden' id='fld_tooth_form_type' name='form_type' value='{$form_type}' />
                <input type='hidden' name='labs_id' id='fld_labs_id' value='{$labs_id}' />
                <input type='hidden' id='patient_visit_id' name='patient_visit_id' value='{$patient_visit_id}'>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getAcrylicDentureForm(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $dentureTypeArr = array('NORMAL'
                               ,'EXPRESS'
                               ,'REWORK'
                               ,'OVER DENTURE'
                               ,'SPECIAL TRAYS'
                               ,'MOUTHGUARD'
                               ,'BLEACHING TRAY'
                               ,'NIGHTGUARD');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $labs_id          = $fn->getReqParam('labs_id');
        $form_type        = 'Acrylic';

        $rowlabs = $fn->getRecordRowByID('labs', 'labs_id', $labs_id);

        $toothlist2ArrCheckbox = $this->getToothlistFirst($patient_visit_id, $form_type, $labs_id);
        $toothlist3ArrCheckbox = $this->getToothlistSecond($patient_visit_id, $form_type, $labs_id);

        $count = 1;
        $dentureTypeArrCheckbox = '';

        foreach($dentureTypeArr as $value){
            $SQLDenture = "
            SELECT labs_id
            FROM labs_history
            WHERE labs_id = '{$labs_id}'
            AND category = '{$form_type}'
            AND title = '{$value}'
            ";
            $resultDenture = $db->sql_query($SQLDenture);
            $checkedStatus = '';
            while($rowDenture = $db->sql_fetchrow($resultDenture)){
                $checkedStatus   = "checked = checked";
            }

            $dentureTypeArrCheckbox .= "
            <div class='type-check ym-fbox-check'>
                <input type='checkbox' id='denture_type_{$count}' value='{$value}' name='denture_type[]' {$checkedStatus}>
                <label for='denture_type_{$count}'>{$value}</label>
            </div>
            ";

            $count++;
        }


        $formAction = 'index.php?module=hms_patientVisit&_spAction=addAcrylicDentureFormRecord&showHTML=0';
        $text = "
        <div id='' class=''>
            <form id='acrylicDentureForm' class='yform columnar' method='post' action='{$formAction}'>
                <table class='thinlist acrylicDentureDate'>
                    <tr>
                        <td>{$formObj->getTBRow('Amount', 'amount', $rowlabs['amount'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getDateRow('Date Sent', 'date_sent', $rowlabs['date_sent'])}</td>
                        <td>{$formObj->getDateRow('Date Received', 'date_received', $rowlabs['date_received'])}</td>
                        <td>{$formObj->getDateRow('Date Due', 'date_due', $rowlabs['date_due'])}</td>
                    </tr>
                </table>
                {$dentureTypeArrCheckbox}
                <div class='toothSelectCheckbox2 newLineFornext'>
                        {$toothlist2ArrCheckbox}
                </div>
                <div class='toothSelectCheckbox3 newLineFornext'>
                        {$toothlist3ArrCheckbox}
                </div>
                <input type='hidden' id='fld_tooth_form_type' name='form_type' value='{$form_type}' />
                <input type='hidden' name='labs_id' id='fld_labs_id' value='{$labs_id}' />
                <input type='hidden' id='patient_visit_id' name='patient_visit_id' value='{$patient_visit_id}'>
            </form>
        </div>
        ";

        return $text;
    }


    /**
     *
     */
    function getLabPortalDisplayOld($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";

        $SQL = "
        SELECT l.*
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
        FROM lab_visit l
        LEFT JOIN (employee e) ON (e.employee_id = l.employee_id)
        WHERE l.patient_visit_id = {$patient_visit_id}
        ORDER BY l.lab_visit_id
        ";
        $result   = $db->sql_query($SQL);

        while ($rowMV = $db->sql_fetchrow($result)) {
            $editURL = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editLabRecord&showHTML=0&patient_visit_id={$rowMV['patient_visit_id']}&lab_visit_id={$rowMV['lab_visit_id']}";
            $editRow = "<td><a href='{$editURL}' id='editLabRecord' patient_visit_id={$rowMV['patient_visit_id']}>Edit</a></td>";
            $rows .= "
            <tr>
                <td>{$rowMV['title']}</td>
                <td>{$rowMV['notes']}</td>
                <td>{$rowMV['employee_name']}</td>
                {$editRow}
                <td></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Test Name</th>
        <th>Notes/Values</th>
        <th>Dr</th>
        <th>Edit</th>
        <th>Delete</th>
        </tr>
        ";

        $text = "
        <div id='' class='doctorDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='doctorPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMedicalHistoryDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=medicalHistorySubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

        $MedHisArray = array(
                            'Cardiac Comp'
                           ,'Hypertension'
                           ,'Blood Disorders'
                           ,'Diabetes'
                           ,'Jaundice'
                           ,'Pregnant'
                           );

        $SQL = "
        SELECT m.title
        FROM medical_history_information m
        WHERE m.patient_visit_id = {$patient_visit_id}
        AND m.status = 'Current'
        ";
        $result   = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);
        $numRowsMV = $db->sql_numrows($result);

        $SQL1 = "
        SELECT m.title, m.others
        FROM medical_history_information m
        WHERE m.patient_visit_id = {$patient_visit_id}
        AND m.status = 'Current'
        ";
        $result1   = $db->sql_query($SQL1);
        $row = $db->sql_fetchrow($result1);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patientInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id']);

        $SQLChk = "
        SELECT patient_visit_id
        FROM patient_visit
        WHERE patient_visit_id < {$patient_visit_id}
        AND patient_information_id = {$patientVisitRec['patient_information_id']}
        ORDER BY patient_visit_id DESC
        ";
        $resultChk   = $db->sql_query($SQLChk);
        $rowChk = $db->sql_fetchrow($resultChk);
        $numRowsChk = $db->sql_numrows($resultChk);

        if($numRowsChk > 0 && $numRowsMV == 0){
            $SQL = "
            SELECT m.title
            FROM medical_history_information m
            WHERE m.patient_visit_id = {$rowChk['patient_visit_id']}
            AND m.status = 'Current'
            ";
            $result   = $db->sql_query($SQL);
            $dataArray = $dbUtil->getResultsetAsArrayForForm($result);
        }

        $text = "
        <form></form>
        <div id='' class=''>
            <form id='portalForm_medHisDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='floatbox'>
                    <div class='medHisDisplay'>{$formObj->getCheckBoxArrRowByArr('', 'title', $MedHisArray, $dataArray)}</div>
                    <div class='float_left'>
                        {$formObj->getTARow('Others', 'others', $patientVisitRec['other_medical_history'])}
                    </div>
                    <div class='float_left'>
                        {$formObj->getTARow('Allergies', 'allergies', $patientInfoRec['alergies'])}
                    </div>
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSummaryPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=summaryPortalSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patientInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id']);

        $text = "
        <div id='' class=''>
            <form id='portalForm_summaryDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button floatbox'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='c33l'>
                    {$formObj->getTARow('Complain', 'complain', $patientVisitRec['complain'])}
                </div>
                <div class='c33l'>
                    {$formObj->getTARow('Treatment Summary', 'treatment_summary', $patientVisitRec['treatment_summary'])}
                </div>
                <div class='c33l'>
                    {$formObj->getTARow('Past Medical History', 'past_medical_history', $patientInfoRec['past_medical_history'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMedicalCertficatePortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=medicalCetificatePortalSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $urlPrint  = "index.php?_topRm=main&module=hms_patientVisit&_spAction=printMedicalCertificateRecord&patient_visit_id={$patient_visit_id}&showHTML=0";

        $text = "
        <div id='' class=''>
            <form id='portalForm_medicalCertificateDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button floatbox'>
                    <input class='button btn-success float_left' type='submit' value='Save' name='portalForm' />

                    <div class='ml20 float_left'>
                        <a href='{$urlPrint}' target='blank' class='btn btn-info' id='printMedCertificate'>
                            Print Certificate
                        </a>
                    </div>
                </div>
                <div class='c50l'>
                    <div class='c33l'>
                        {$formObj->getTBRow('No Of Days', 'no_of_days', $patientVisitRec['no_of_days'])}
                    </div>
                    <div class='c33l'>
                        {$formObj->getDateRow('Resume Duty On', 'resume_duty_on', $patientVisitRec['resume_duty_on'])}
                    </div>
                    <div class='c33l'>
                        {$formObj->getDateRow('Certificate Date', 'medical_certficate_date', $patientVisitRec['medical_certficate_date'])}
                    </div>
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getOralHygienicDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=oralHygienicySubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

        $oralHygienicArray = array(
                            'Brushing'
                           ,'Flossing'
                           ,'Mouth Wash'
                           );

        $SQL = "
        SELECT o.title
        FROM oral_hygienic_visit o
        WHERE o.patient_visit_id = {$patient_visit_id}
        AND o.status = 'Current'
        ";
        $result   = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);

        $SQL1 = "
        SELECT o.title, o.others
        FROM oral_hygienic_visit o
        WHERE o.patient_visit_id = {$patient_visit_id}
        AND o.status = 'Current'
        ";
        $result1   = $db->sql_query($SQL1);
        $row = $db->sql_fetchrow($result1);
        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $text = "
        <b>Oral Hygienic</b>
        <div id='' class='oralHygienic mr10'>
            <form id='portalForm_oralHygienic' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='oralHygienicDisplay'>{$formObj->getCheckBoxArrRowByArr('', 'title', $oralHygienicArray, $dataArray)}</div>
                <div class=''>
                    {$formObj->getTARow('Others', 'others', $patientVisitRec['other_oral_hygienic'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getHabitsDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=habitsSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

        $habitsArray = array(
                            'Smoking'
                           ,'Thumb Sucking'
                           ,'Alcohol Consumption'
                           ,'Bruxism'
                           ,'Bottle Feeding'
                           ,'Beatle Nut/Beatle Leaf Chewing'
                           );
        $SQL = "
        SELECT h.title
        FROM habits_information h
        WHERE h.patient_visit_id = {$patient_visit_id}
        AND h.status = 'Current'
        ";
        $result   = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);

        $SQL1 = "
        SELECT h.title, h.others
        FROM habits_information h
        WHERE h.patient_visit_id = {$patient_visit_id}
        AND h.status = 'Current'
        ";
        $result1   = $db->sql_query($SQL1);
        $row = $db->sql_fetchrow($result1);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patientInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id']);

        $text = "
        <b>Habits</b>
        <div id='' class='habits mr10'>
            <form id='portalForm_habits' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='habitsDisplay'>{$formObj->getCheckBoxArrRowByArr('', 'title', $habitsArray, $dataArray)}</div>
                <div class=''>
                    {$formObj->getTARow('Others', 'others', $patientInfoRec['other_habits_history'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getIntraOralExaminationDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=intraOralExamSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";
        $count = 0;

        $intraOralArray = array(
                            'Lips'
                           ,'Tongue'
                           ,'Floor of the Mouth'
                           ,'Buccol Mucosa'
                           ,'Palate'
                           ,'Pharynx'
                           );

        foreach($intraOralArray as $value){
            $checkedNormal = '';
            $checkedAbnormal = '';

            $SQL = "
            SELECT i.title, i.title_status, i.remarks
            FROM intra_oral_information i
            WHERE i.patient_visit_id = {$patient_visit_id}
            AND i.title = '{$value}'
            ";
            $result   = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);

            if($row['title'] != ''){
                if($row['title_status'] == 'normal'){
                    $checkedNormal = 'checked="checked"';
                } else {
                    $checkedAbnormal = 'checked="checked"';
                }
            }

            $rows .= "
            <div class='type-check ym-fbox-check'>
                <label for='title_{$count}'>{$value}</label>
                <input class='normalCheck' type='radio' id='title_{$count}' {$checkedNormal} value='{$value}_normal_{$count}' name='title_{$count}[]'>
                <input class='abnormalCheck' type='radio' id='title_{$count}' {$checkedAbnormal} value='{$value}_abnormal_{$count}' name='title_{$count}[]'>
                <input type='text' value='{$row['remarks']}' id='fld_remarks' class='text' name='remarks[]'>
            </div>
            ";
            $count++;
        }

        $SQL1 = "
        SELECT i.title, i.title_status, i.remarks, i.others
        FROM intra_oral_information i
        WHERE i.patient_visit_id = {$patient_visit_id}
        ";
        $result1   = $db->sql_query($SQL1);
        $row1 = $db->sql_fetchrow($result1);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $text = "
        <b>Intra Oral Examination</b>
        <div id='' class='examination1 habits mr10'>
            <form id='portalForm_intraOral' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='type-check'>
                    <label></label>
                    Normal &nbsp; Abnormal &nbsp; Remarks
                </div>
                {$rows}
                <div class=''>
                    {$formObj->getTARow('Others', 'others', $patientVisitRec['other_intra_oral_exam'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
                <input type='hidden' name='count' value='{$count}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getExtraOralExaminationDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=extraOralExamSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";
        $count = 0;

        $intraOralArray = array(
                            'Face'
                           ,'Lips'
                           ,'Mouth Opening'
                           ,'TM Joint'
                           ,'Lymph Nodes'
                           );

        foreach($intraOralArray as $value){
            $checkedNormal = '';
            $checkedAbnormal = '';

            $SQL = "
            SELECT i.title, i.title_status, i.remarks
            FROM extra_oral_information i
            WHERE i.patient_visit_id = {$patient_visit_id}
            AND i.status = 'Current'
            AND i.title = '{$value}'
            ";
            $result   = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);

            if($row['title'] != ''){
                if($row['title_status'] == 'normal'){
                    $checkedNormal = 'checked="checked"';
                } else {
                    $checkedAbnormal = 'checked="checked"';
                }
            }

            $rows .= "
            <div class='type-check ym-fbox-check'>
                <label for='title_{$count}'>{$value}</label>
                <input class='normalCheck' type='radio' id='title_{$count}' {$checkedNormal} value='{$value}_normal_{$count}' name='title_{$count}[]'>
                <input class='abnormalCheck' type='radio' id='title_{$count}' {$checkedAbnormal} value='{$value}_abnormal_{$count}' name='title_{$count}[]'>
                <input type='text' value='{$row['remarks']}' id='fld_remarks' class='text' name='remarks[]'>
            </div>
            ";
            $count++;
        }

        $SQL1 = "
        SELECT i.title, i.title_status, i.remarks, i.others
        FROM extra_oral_information i
        WHERE i.patient_visit_id = {$patient_visit_id}
        AND i.status = 'Current'
        ";
        $result1   = $db->sql_query($SQL1);
        $row1 = $db->sql_fetchrow($result1);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $text = "
        <b>Extra Oral Examination</b>
        <div id='' class='examination1 habits mr10'>
            <form id='portalForm_extraOral' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='type-check'>
                    <label></label>
                    Normal &nbsp; Abnormal &nbsp; Remarks
                </div>
                {$rows}
                <div class=''>
                    {$formObj->getTARow('Others', 'others', $patientVisitRec['other_extra_oral_exam'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
                <input type='hidden' name='count' value='{$count}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPeridontiumDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=peridontiumSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";
        $count = 0;

        $intraOralArray = array(
                            'Gingiva'
                           ,'Bleeding on Probing Periodontal'
                           ,'Pocket Mobility teeth'
                           ,'Furnation Involvement'
                           ,'Gigival Recession'
                           );

        foreach($intraOralArray as $value){
            $checkedNormal = '';
            $checkedAbnormal = '';

            $SQL = "
            SELECT i.title, i.title_status, i.remarks
            FROM peridontium_information i
            WHERE i.patient_visit_id = {$patient_visit_id}
            AND i.status = 'Current'
            AND i.title = '{$value}'
            ";
            $result   = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);

            if($row['title'] != ''){
                if($row['title_status'] == 'normal'){
                    $checkedNormal = 'checked="checked"';
                } else {
                    $checkedAbnormal = 'checked="checked"';
                }
            }

            $rows .= "
            <div class='type-check ym-fbox-check'>
                <label for='title_{$count}'>{$value}</label>
                <input class='normalCheck' type='radio' id='title_{$count}' {$checkedNormal} value='{$value}_normal_{$count}' name='title_{$count}[]'>
                <input class='abnormalCheck' type='radio' id='title_{$count}' {$checkedAbnormal} value='{$value}_abnormal_{$count}' name='title_{$count}[]'>
                <input type='text' value='{$row['remarks']}' id='fld_remarks' class='text' name='remarks[]'>
            </div>
            ";
            $count++;
        }

        $SQL1 = "
        SELECT i.title, i.title_status, i.remarks, i.others
        FROM peridontium_information i
        WHERE i.patient_visit_id = {$patient_visit_id}
        AND i.status = 'Current'
        ";
        $result1   = $db->sql_query($SQL1);
        $row1 = $db->sql_fetchrow($result1);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $text = "
        <b>Peridontium</b>
        <div id='' class='examination1 habits mr10'>
            <form id='portalForm_peridontium' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='type-check'>
                    <label></label>
                    Normal &nbsp; Abnormal &nbsp; Remarks
                </div>
                {$rows}
                <div class=''>
                    {$formObj->getTARow('Others', 'others', $patientVisitRec['other_peridontium'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
                <input type='hidden' name='count' value='{$count}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
     function getAddDoctorRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addDoctorRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $sqlDoctor = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
        FROM employee e
        WHERE position IN ('Doctor', 'Nurse')
        ORDER BY e.first_name
        ";

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor)}
            {$formObj->getTBRow('Consulting Fees', 'consultation_fees')}
            {$formObj->getTBRow('Room', 'consultation_room')}
            {$formObj->getTARow('Notes', 'notes')}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getAddTreatmentRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addTreatmentRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('treatmentCategory', 'value ASC');

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Code', 'treatment_code')}
            {$formObj->getTBRow('Title', 'treatment_title')}
            {$formObj->getDDRowBySQL('Category', 'category', $sqlCategory, '', $expVl)}
            {$formObj->getTBRow('Fees', 'fees')}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getAddDiagnosisRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addDiagnosisRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Code', 'diagnosis_code')}
            {$formObj->getTBRow('Title', 'diagnosis_title')}
            {$formObj->getTBRow('Fees', 'fees')}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getLabsSupplierJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $supplier_category   = $fn->getReqParam('supplier_category');

        $json  = array();

        if ($supplier_category == ""){
            $json[] = array("value" => "", "caption" => "Please Select");
            return json_encode($json);
        }

        $SQL = "
        SELECT labs_supplier_id
              ,title
        FROM labs_supplier
        WHERE category = '{$supplier_category}'
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['labs_supplier_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }

    /**
     *
     */
     function getAddLabsRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $expVl = array('sqlType' => 'OneField');
        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addLabsRecordSubmit&showHTML=0";
        $patient_visit_id       = $fn->getReqParam('patient_visit_id');
        $patient_information_id = $fn->getReqParam('patient_information_id');
        $sqlCategory = $fn->getValueListSQL('labSupplierCategory');

        /*$labsModule = $fn->getReqParam('labsModule');
        $categoryValue = '';
        $sqlSupplier  = '';
        if($labsModule == 1){
            $supplier = $fn->getReqParam('supplier');
            $categoryValue = $supplier;
            $expVl = array('disabled' =>  true,
                            'sqlType' => 'OneField');

            $sqlSupplier = "
            SELECT labs_supplier_id
                  ,title
            FROM labs_supplier
            WHERE category = '{$supplier}'
            ORDER BY title
            ";
        }*/

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Category', 'supplier_category', $sqlCategory, '', $expVl)}
            {$formObj->getDDRowBySQL('Supplier', 'supplier_id', '', '')}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            <input type='hidden' name='patient_information_id' value='{$patient_information_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditLabsRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $labs_id          = $fn->getReqParam('labs_id');
        $rows = '';

        $SQL = "
        SELECT  l.supplier_category
               ,l.labs_id
               ,l.patient_visit_id
               ,l.supplier_id
        FROM labs l
        WHERE l.patient_visit_id = {$patient_visit_id}
        AND labs_id = {$labs_id}
        ";
        $result   = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $sqlSupplier = "
        SELECT labs_supplier_id
              ,title
        FROM labs_supplier
        WHERE category = '{$row['supplier_category']}'
        ORDER BY title
        ";

        $expVl  = array('sqlType' => 'OneField',
                        'disabled' => true);
        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editLabsRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $sqlCategory = $fn->getValueListSQL('labSupplierCategory');

        $text = "
        <form id='EditLabsRecordportalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Category', 'supplier_category', $sqlCategory, $row['supplier_category'], $expVl)}
            {$formObj->getDDRowBySQL('Supplier', 'supplier_id', $sqlSupplier, $row['supplier_id'])}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            <input type='hidden' name='labs_id' value='{$labs_id}' />
        </form>
        ";

        return $text;
    }


    /**
     *
     */
     function getEditDoctorRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editDoctorRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $employee_visit_id = $fn->getReqParam('employee_visit_id');

        $sqlDoctor = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
        FROM employee e
        WHERE position IN ('Doctor', 'Nurse')
        ORDER BY e.employee_id
        ";

        $SQL = "
        SELECT ev.*
        FROM employee_visit ev
        WHERE ev.employee_visit_id = {$employee_visit_id}
        ";
        $result   = $db->sql_query($SQL);
        $rowEV = $db->sql_fetchrow($result);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor, $rowEV['employee_id'])}
            {$formObj->getTBRow('Consulting Fees', 'consultation_fees', $rowEV['consultation_fees'])}
            {$formObj->getTBRow('Room', 'consultation_room', $rowEV['consultation_room'])}
            {$formObj->getTARow('Notes', 'notes', $rowEV['notes'])}
            <input type='hidden' name='employee_visit_id' value='{$employee_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getAddLabRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addLabRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $sqlDoctor = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
        FROM employee e
        WHERE position IN ('Doctor', 'Nurse')
        ORDER BY e.employee_id
        ";

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Test Name', 'title')}
            {$formObj->getTARow('Notes', 'notes')}
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor)}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditLabRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editLabRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $lab_visit_id = $fn->getReqParam('lab_visit_id');

        $sqlDoctor = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
        FROM employee e
        WHERE position IN ('Doctor', 'Nurse')
        ORDER BY e.employee_id
        ";

        $SQL = "
        SELECT *
        FROM lab_visit
        WHERE lab_visit_id = {$lab_visit_id}
        ";
        $result   = $db->sql_query($SQL);
        $rowLV = $db->sql_fetchrow($result);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Medicine', 'title', $rowLV['title'])}
            {$formObj->getTARow('Notes', 'notes', $rowLV['notes'])}
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor, $rowLV['employee_id'])}
            <input type='hidden' name='lab_visit_id' value='{$lab_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     */
     function getAddPatientRecord() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expVl = array('sqlType' => 'OneField');
        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addPatientRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $sqlBillType    = $fn->getValueListSQL('billType');
        $sqlGender      = $fn->getValueListSQL('gender');

        $row = '';
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $billType   = $fn->getReqParam('bill_type');

        $companyDetailsHide = '';
        if($billType == '' || $billType == 'Individual'){
            $companyDetailsHide = 'companyDetailsHide';
            $sqlCompany = '';
        }

        $sqlCompany = "
        SELECT company_id
               ,company_name
        FROM company
        WHERE category = '{$billType}'
        ORDER BY company_name
        ";


        $fieldsetHideItems = "
        <table>
            <tr>
                <th colspan='4'>Address Details</th>
            </tr>

            <tr>
                <td width='25%'>{$formObj->getTBRow('Address Street', 'address_street')}</td>
                <td width='25%'>{$formObj->getTBRow('Address Area', 'address_area')}</td>
                <td width='25%'>{$formObj->getTBRow('Address City', 'address_city')}</td>
                <td width='25%'>{$formObj->getTBRow('Address Code', 'address_code')}</td>
            </tr>
            <tr>
                <td width='25%'>{$formObj->getDDRowBySQL('Address Country', 'address_country', $sqlCountry)}</td>
            </tr>
        </table>
        ";

        $fieldsetHide = "
        <div class = 'linkPortalWrapper'>
            <div expanded='1' class='header'>
               <a id='displayText' href='#'>Show More Fields (+)</a>
            </div>
            <div id='toggleText' style='display: none'>
                    {$formObj->getFieldSetWrapped('', $fieldsetHideItems)}
            </div>
        </div>
        ";


        $text = "
        <form id='portalForm' class='yform columnar qucikaddPatientForm' method='post' action='{$formAction}'>
            <table class='thinlist'>
                <tr>
                    <td>{$formObj->getTBRow('First Name*', 'first_name')}</td>
                    <td>{$formObj->getTBRow('Middle Name', 'middle_name')}</td>
                    <td>{$formObj->getTBRow('Last Name', 'last_name')}</td>
                    <td>{$formObj->getTBRow('NRIC*', 'nric')}</td>
                </tr>
                <tr>
                    <td>
                        {$formObj->getDateRow('DOB (YYYY-MM-DD)', 'dob', '', array('yearStart' => 1950, 'yearEnd' => date('Y') + 10))}
                    </td>
                    <td>{$formObj->getTBRow('Phone', 'phone')}</td>
                    <td>{$formObj->getTBRow('Email', 'email')}</td>
                    <td>{$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, '', $expVl)}</td>
                </tr>
                <tr>
                    <td>{$formObj->getTBRow('Passport No', 'registration_no')}</td>
                    <td>{$formObj->getDateRow('First Visit On (YYYY-MM-DD)', 'first_admit', '')}</td>
                    <td>{$formObj->getDDRowBySQL('Bill Type*', 'bill_type', $sqlBillType, 'Individual', $expVl)}</td>
                    <td class='companyDetailsTr {$companyDetailsHide}'>{$formObj->getDDRowBySQL('', 'company_id', $sqlCompany,'')}</td>
                </tr>
            </table>
            {$fieldsetHide}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getCompanyNameJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $company_category   = $fn->getReqParam('company_category');

        $json  = array();

        $SQL = "
        SELECT company_id
              ,company_name
        FROM company
        WHERE category = '{$company_category}'
        ORDER BY company_name
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['company_id'], "caption" => $row['company_name']);
        }

        return json_encode($json);
    }
    /**
     *
     */
    function getTreatmentPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $searchTreatment   = $fn->getReqParam('searchTreatment');
        $TreatmentCategory = $fn->getReqParam('TreatmentCategory');

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=treatmentRecordSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";
        $inputRowSelected = "";

        $followUp = array(
                       '1 week'    => 'One week later'
                      ,'2 weeks'   => 'Two weeks later'
                      ,'3 weeks'   => 'Three weeks later'
                      ,'4 weeks'   => 'Four weeks later'
                      ,'5 weeks'   => 'Five weeks later'
                      ,'6 weeks'   => 'Six weeks later'
                      ,'2 months'  => 'Two months later'
                      ,'3 months'  => 'Three months later'
                      ,'6 months'  => 'Six months later'
                      );
        $expArr = array('useKey' => 1);

        $whereCondition = '';
        $searBoxText   = '';
        if($searchTreatment != ''){
            $whereCondition = "WHERE t.title LIKE '%{$searchTreatment}%' ";
            $searBoxText = $searchTreatment;
        }

        if($TreatmentCategory != ''){
            $whereCondition = "WHERE t.category LIKE '%{$TreatmentCategory}%' ";

            $SQLTreatment = "
            (SELECT t.treatment_id
                  ,t.title
                  ,t.fees
            FROM treatment t
            LEFT JOIN (treatment_visit tv) ON (tv.treatment_id = t.treatment_id)
            WHERE patient_visit_id = {$patient_visit_id}
            )
            UNION
            (SELECT t.treatment_id
                  ,t.title
                  ,t.fees
            FROM treatment t
            {$whereCondition}
            )
            ";
        }else{
            $SQLTreatment = "
            SELECT t.treatment_id
                  ,t.title
                  ,t.fees
            FROM treatment t
            LEFT JOIN (treatment_visit tv) ON (tv.treatment_id = t.treatment_id)
            WHERE patient_visit_id = {$patient_visit_id}
            ";
        }

        $result   = $db->sql_query($SQLTreatment);
        $count = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $treatVisitRec = $fn->getRecordByCondition('treatment_visit', "treatment_id = '{$row['treatment_id']}' AND patient_visit_id = '{$patient_visit_id}'");

            if($treatVisitRec['treatment_visit_id'] != ''){
                $checked = "checked='checked'";
                $checkedCheckboxClass = 'checkedCheckBoxTreatment';
                $class ="";
            } else {
                $checked = '';
                $class ="displayNone";
                $checkedCheckboxClass = '';
            }

            $addNoteUrl = "index.php?module=hms_patientVisit&_spAction=addNoteTreatment&treatment_visit_id={$treatVisitRec['treatment_visit_id']}&showHTML=0";

                        //<input class='button' name='treatment_status[]' value='Current' />

            if($treatVisitRec['notes'] != ''){
                $notes = 'View Notes';
            }else {
                $notes = 'Add Notes';
            }

            if($treatVisitRec['fees'] != ''){
                $fees = $treatVisitRec['fees'];
            }else {
                $fees = $row['fees'];
            }

            if($treatVisitRec['status'] != ''){
                $status = $treatVisitRec['status'];
            }else {
                $status = 'Current';
            }

            if($treatVisitRec['follow_up_date'] != ''){
                $future_date = $treatVisitRec['follow_up_date'];
                $future_value = $treatVisitRec['follow_up_value'];
                $classDate ="";
            }else {
                $future_date = '';
                $future_value = '';
                $classDate ="displayNone";
            }

            //if($treatVisitRec['treatment_visit_id'] != ''){
            $inputRowSelected .= "
            <div class='c33l'>
                <div class='type-check treatmentBox {$checkedCheckboxClass}'>
                    <input id='treatment_id_{$row['treatment_id']}' {$checked} class='treatment_id' type='checkbox' name='treatmentId[]' value='{$row['treatment_id']}_{$count}'>
                    <label for='treatment_id_{$row['treatment_id']}'>{$row['title']}</label>
                    <div class='hideTreatmentDetails_{$row['treatment_id']}_{$count} {$class} treatmentNotes mt20'>
                        <input type='text' value='{$fees}' id='fld_fees' class='text mb20' name='fees_{$row['treatment_id']}[]'>
                        <input class='button treatmentStatus mb10' name='treatment_status[]' value='{$status}'/>
                        <div class='{$classDate} followUpDate'>
                            {$formObj->getDropDownRowByArray('', 'future_value_'.$row['treatment_id'], $followUp, $future_value, $expArr)}
                            {$formObj->getDateRow('(YYYY-MM-DD)', 'future_date_'.$row['treatment_id'], $future_date)}
                        </div>
                        <div><u><a href='#' class='addNoteTreatment'>{$notes}</a></u></div>
                        <div class='hideNotes'>
                            <div class='type-text ym-fbox-text row_notes'>
                                <textarea id='fld_notes' name='notes[]' >{$treatVisitRec['notes']}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ";
            /*}else{
                $SQLTreatment = "
                SELECT t.treatment_id
                      ,t.title
                      ,t.fees
                FROM treatment t
                WHERE t.treatment_id = {$row['treatment_id']}
                {$whereCondition}
                ORDER BY t.title
                ";
                $resultTreatment = $db->sql_query($SQLTreatment);
                $rowTreatment    = $db->sql_fetchrow($resultTreatment);

                if($rowTreatment['title'] != ''){
                    $inputRow .= "
                    <div class='c33l'>
                        <div class='type-check treatmentBox {$checkedCheckboxClass}'>
                            <input id='treatment_id_{$rowTreatment['treatment_id']}' {$checked} class='treatment_id' type='checkbox' name='treatmentId[]' value='{$rowTreatment['treatment_id']}_{$count}'>
                            <label for='treatment_id_{$rowTreatment['treatment_id']}'>{$rowTreatment['title']}</label>
                            <div class='hideTreatmentDetails_{$rowTreatment['treatment_id']}_{$count} {$class} treatmentNotes mt20'>
                                <input type='text' value='{$fees}' id='fld_fees' class='text mb20' name='fees_{$rowTreatment['treatment_id']}[]'>
                                <input class='button treatmentStatus mb10' name='treatment_status[]' value='{$status}'/>
                                <div class='{$classDate} followUpDate'>
                                    {$formObj->getDropDownRowByArray('', 'future_value_'.$rowTreatment['treatment_id'], $followUp, $future_value, $expArr)}
                                    {$formObj->getDateRow('(YYYY-MM-DD)', 'future_date_'.$rowTreatment['treatment_id'], $future_date)}
                                </div>
                                <div><u><a href='#' class='addNoteTreatment'>{$notes}</a></u></div>
                                <div class='hideNotes'>
                                    <div class='type-text ym-fbox-text row_notes'>
                                        <textarea id='fld_notes' name='notes[]' >{$treatVisitRec['notes']}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    ";
                }
            }*/

            $count ++;
        }

        $formActionAddTreatement = "index.php?module=hms_patientVisit&_spAction=addTreatmentRecord&patient_visit_id={$patient_visit_id}&showHTML=0";

        /*
        <div>
            <input class='treatmentSearchAuto' rel='pptxt: Search Treatment' type='text' value='{$searBoxText}' name='Treatment Search' />
            <a class='followupToggleSearchiCon'></a>
        </div>
        */
        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('treatmentCategory', 'value ASC');

        $text = "
        <form></form>
        <div id='' class='treatmentDisplay'>
            <form id='portalForm_treatmentDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='floatbox'>
                    <div class='type-button float_left'>
                        <input class='button' type='submit' value='Save' name='portalForm' />
                    </div>
                    <div class='mt10 float_right'>
                        <a href='{$formActionAddTreatement}' class='btn btn-info' id='addTreatmentRecord'>
                            Add Record
                        </a>
                    </div>
                </div>
                <div class='treatmentCategoryFilter'>
                    <select name='treatmentCategory' >
                        <option value=''>Select Treatment Category</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlCategory, $TreatmentCategory)}
                    </select>
                </div>
                <div class='floatbox treatmentDisplayList'>{$inputRowSelected}{$inputRow}</div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddNoteTreatment() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $treatment_visit_id = $fn->getReqParam('treatment_visit_id');

        $formAction = "index.php?module=hms_patientVisit&_spAction=addNoteTreatmentSubmit&showHTML=0";
        $treatmentVisitRec = $fn->getRecordRowByID('treatment_visit', 'treatment_visit_id', $treatment_visit_id);

        $text = "
        <form id='portalForm' class='yform columnar addNoteForm' method='post' action='{$formAction}'>
            {$formObj->getTARow('Notes', 'notes', $treatmentVisitRec['notes'])}
            <input type='hidden' name='treatment_visit_id' value='{$treatment_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     */
    function getDiagnosisPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $searchDiagnosis = $fn->getReqParam('searchDiagnosis');

        $formAction = "index.php?module=hms_patientVisit&_spAction=diagnosisRecordSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";
        $inputRowSelected = "";

        $whereCondition = '';
        $searBoxText   = '';
        if($searchDiagnosis != ''){
            $whereCondition = "WHERE d.title LIKE '%{$searchDiagnosis}%' ";
            $searBoxText = $searchDiagnosis;
        }

        $SQLDiagnosis = "
        SELECT d.diagnosis_id
              ,d.title
              ,d.fees
        FROM diagnosis d
        LEFT JOIN (diagnosis_visit dv) ON (dv.diagnosis_id = d.diagnosis_id)
        WHERE patient_visit_id = '{$patient_visit_id}'
        UNION
        SELECT d.diagnosis_id
              ,d.title
              ,d.fees
        FROM diagnosis d
        {$whereCondition}
        ";
        $result   = $db->sql_query($SQLDiagnosis);
        $count = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $diagnosisVisitRec = $fn->getRecordByCondition('diagnosis_visit', "diagnosis_id = '{$row['diagnosis_id']}' AND patient_visit_id = '{$patient_visit_id}'");

            if($diagnosisVisitRec['diagnosis_visit_id'] != ''){
                $checked = "checked='checked'";
                $checkedCheckboxClass = 'checkedCheckBoxTreatment';
                $class ="";


            } else {
                $checked = '';
                $class ="displayNone";
                $checkedCheckboxClass = '';

                /*$SQLDiagnosis = "
                SELECT d.diagnosis_id
                      ,d.title
                      ,d.fees
                FROM diagnosis d
                WHERE d.diagnosis_id = {$row['diagnosis_id']}
                {$whereCondition}
                ORDER BY d.title
                ";
                $resultDiagnosis = $db->sql_query($SQLDiagnosis);
                $rowDiagnosis    = $db->sql_fetchrow($resultDiagnosis);

                if($rowDiagnosis['title'] != ''){
                    $inputRow .= "
                    <div class='c33l'>
                    <div class='type-check diagnosisBox {$checkedCheckboxClass}'>
                        <input id='diagnosis_id_{$rowDiagnosis['diagnosis_id']}' {$checked} class='diagnosis_id' type='checkbox' name='diagnosisId[]' value='{$rowDiagnosis['diagnosis_id']}_{$count}'>
                        <label for='diagnosis_id_{$rowDiagnosis['diagnosis_id']}'>{$rowDiagnosis['title']}</label>
                    </div>
                    </div>
                    ";

                }*/
            }

            $inputRowSelected .= "
            <div class='c33l'>
            <div class='type-check diagnosisBox {$checkedCheckboxClass}'>
                <input id='diagnosis_id_{$row['diagnosis_id']}' {$checked} class='diagnosis_id' type='checkbox' name='diagnosisId[]' value='{$row['diagnosis_id']}_{$count}'>
                <label for='diagnosis_id_{$row['diagnosis_id']}'>{$row['title']}</label>
            </div>
            </div>
            ";
            $count ++;
        }

        $formActionAddDiagnosis = "index.php?module=hms_patientVisit&_spAction=addDiagnosisRecord&patient_visit_id={$row['patient_visit_id']}&showHTML=0";

        $text = "
        <form></form>
        <div id='' class='diagnosisDisplay'>
            <form id='portalForm_diagnosisDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button float_left'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='mt10 float_right'>
                    <a href='{$formActionAddDiagnosis}' class='btn btn-info' id='addDiagnosisRecord'>
                        Add Record
                    </a>
                </div>
                <div>
                    <input class='diagnosisSearchAuto' rel='pptxt: Search Diagnosis' type='text'  name='Diagnosis Search' />
                    <a class='followupToggleSearchiCon'></a>
                </div>
                <div class='floatbox diagnosisDisplayList'>{$inputRowSelected}{$inputRow}</div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');


        $record_id = $fn->getIssetParam($row, 'patient_visit_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_patientVisit', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $statusArray = array(
            "status"
           ,"New"
           ,"Visited Dr"
           ,"Bill Due"
           ,"Closed"
           ,"On Hold"
           ,"Cancelled"
        );
        $billType   = $fn->getReqParam('bill_type');
        $sqlBillType    = $fn->getValueListSQL('billType');

        $text = "
        <td>
            <select name='bill_type'>
                <option value=''>Bill Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlBillType, $billType)}
           </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($statusArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getSelectDoctorDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $appointment_id         = $fn->getReqParam('appointment_id');

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=createVisitRecordSubmit&showHTML=0";

        $sqlEmployee = "
        SELECT employee_id
              ,CONCAT_WS(' ', first_name, middle_name, last_name) AS employee_name
        FROM employee
        WHERE position IN ('Doctor', 'Nurse')
        ORDER BY employee_name
        ";

        $text = "
        <form id='portalFormPatientVisitCreate' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDDRowBySQL('Choose Dr/Nurse', 'employee_id', $sqlEmployee,'')}
                <input type='hidden' name='patient_information_id' value='{$patient_information_id}'>
                <input type='hidden' name='appointment_id' value='{$appointment_id}'>
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getPerioChartForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $text = '';
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $form_type        = 'Perio Chart';

        $toothlist2ArrCheckbox = $this->getToothlistFirst($patient_visit_id, $form_type);
        $toothlist3ArrCheckbox = $this->getToothlistSecond($patient_visit_id, $form_type);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action=''>
            <div class='toothSelectCheckbox2 newLineFornext'>
                    {$toothlist2ArrCheckbox}
            </div>
            <div class='toothSelectCheckbox3 newLineFornext'>
                    {$toothlist3ArrCheckbox}
            </div>
            <input type='hidden' id='patient_visit_id' name='patient_visit_id' value='{$patient_visit_id}'>
            <input type='hidden' id='fld_tooth_form_type' value='{$form_type}' />
            <div id='dialog-confirm'></div>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getToothlistFirst($patient_visit_id = '', $form_type = '', $labs_id = ''){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        if($form_type == ''){
            $form_type = $fn->getReqParam('tooth_form_type');
        }

        $appendSql = '';
        if($labs_id == ''){
            $labs_id = $fn->getReqParam('labs_id');
            if($labs_id != ''){
                $appendSql = "AND labs_id = '{$labs_id}'";
            }
        }else{
            $appendSql = "AND labs_id = '{$labs_id}'";
        }

        $toothlist2Arr = array('TL8','TL7','TL6','TL5','TL4','TL3','TL2','TL1','TR1','TR2','TR3','TR4','TR5','TR6','TR7','TR8');

        $count = 1;
        $toothlist2ArrCheckbox = '';

        foreach($toothlist2Arr as $value){
            $SQLPerio = "
            SELECT tooth_id
                   ,symbol
                   ,tooth_top
                   ,tooth_left
                   ,tooth_right
                   ,tooth_bottom
                   ,tooth_center
                   ,symbol
            FROM visit_perio_chart
            WHERE patient_visit_id = {$patient_visit_id}
            AND category = '{$form_type}'
            AND tooth_id = '{$value}'
            {$appendSql}
            ";
            $resultPerio = $db->sql_query($SQLPerio);
            $selected_toothList    = '';
            $imageTag              = '';
            $imagepath             = '';
            $checkedStatus         = '';
            $checkedStatusTop      = '';
            $checkedStatusLeft     = '';
            $checkedStatusRight    = '';
            $checkedStatusCenter   = '';
            $checkedStatusBottom   = '';
            $disableCheckboxTop    = '';
            $disableCheckboxLeft   = '';
            $disableCheckboxRight  = '';
            $disableCheckboxCenter = '';
            $disableCheckboxBottom = '';
            $disableCheckbox       = '';
            $symbolName            = '';
            $editClass             = '';
            $tooth_id              = '';
            while($rowPerio = $db->sql_fetchrow($resultPerio)){
                $imagepath = $cpCfg['cp.localPath']."images/SelectedPerioSymbols/{$rowPerio['symbol']}.png";
                $imageTag  = "<img class='selectedToothSymbol {$rowPerio['symbol']}' tooth_id='{$rowPerio['tooth_id']}' patient_visit_id='{$patient_visit_id}' src='{$imagepath}'/>";

                if($rowPerio['tooth_top'] == '1'){
                    $checkedStatusTop   = "checked = checked";
                    $disableCheckboxTop = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                if($rowPerio['tooth_left'] == '1'){
                    $checkedStatusLeft   = "checked = checked";
                    $disableCheckboxLeft = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                if($rowPerio['tooth_right'] == '1'){
                    $checkedStatusRight   = "checked = checked";
                    $disableCheckboxRight = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                if($rowPerio['tooth_center'] == '1'){
                    $checkedStatusCenter   = "checked = checked";
                    $disableCheckboxCenter = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                if($rowPerio['tooth_bottom'] == '1'){
                    $checkedStatusBottom   = "checked = checked";
                    $disableCheckboxBottom = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                $symbolName = $rowPerio['symbol'];
                $tooth_id = $rowPerio['tooth_id'];

            }

            $toothlist2ArrCheckbox .= "
            <div class='float_left'>
                <table border='1' class='thinlist'>
                    <tr>
                        <td colspan='3' height='25' class='txtCenter'>
                            <b>{$symbolName}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='3' class='txtCenter'>
                            <div class='tooth_PerioTop'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth2Top_{$count}' Checkbox_ID='{$count}' tooth_part='Top' {$checkedStatusTop} {$disableCheckboxTop} value='{$value}' name='selected_tooth2[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Top' for='selected_tooth2Top_{$count}'></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class='tooth_PerioLeft'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth2Left_{$count}' Checkbox_ID='{$count}' tooth_part='Left' {$checkedStatusLeft} {$disableCheckboxLeft} value='{$value}' name='selected_tooth2[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Left' for='selected_tooth2Left_{$count}'></label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class='tooth_PerioCenter'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth2Center_{$count}' Checkbox_ID='{$count}' tooth_part='Center' {$checkedStatusCenter} {$disableCheckboxCenter} value='{$value}' name='selected_tooth2[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Center' for='selected_tooth2Center_{$count}'></label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class='tooth_PerioRight'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth2Right_{$count}' Checkbox_ID='{$count}' tooth_part='Right' {$checkedStatusRight} {$disableCheckboxRight} value='{$value}' name='selected_tooth2[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Right' for='selected_tooth2Right_{$count}'></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='3' class='txtCenter'>
                            <div class='tooth_PerioBottom'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth2Bottom_{$count}' Checkbox_ID='{$count}' tooth_part='Bottom' {$checkedStatusBottom} {$disableCheckboxBottom} value='{$value}' name='selected_tooth2[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Bottom' for='selected_tooth2Bottom_{$count}'></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='3' class='txtCenter'>
                            <b>{$value}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='3' height='20' class='txtCenter'>
                        </td>
                    </tr>
                </table>
            </div>
            ";

            /*$toothlist2ArrCheckbox .= "
            <div class='type-check ym-fbox-check'>
                {$imageTag}
                <input type='checkbox' id='selected_tooth2_{$count}' Checkbox_ID='{$count}' {$checkedStatus} {$disableCheckbox} value='{$value}' name='selected_tooth2[]'>
                <label for='selected_tooth2_{$count}'>{$value}</label>
            </div>
            ";*/

            $count++;
        }

        return $toothlist2ArrCheckbox;

    }

    /**
     *
     */
    function getToothlistFirst1($patient_visit_id = '', $form_type = '', $labs_id = ''){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        if($form_type == ''){
            $form_type = $fn->getReqParam('tooth_form_type');
        }

        $appendSql = '';
        if($labs_id == ''){
            $labs_id = $fn->getReqParam('labs_id');
            if($labs_id != ''){
                $appendSql = "AND labs_id = '{$labs_id}'";
            }
        }else{
            $appendSql = "AND labs_id = '{$labs_id}'";
        }

        $toothlist2Arr = array('TL8','TL7','TL6','TL5','TL4','TL3','TL2','TL1','TR1','TR2','TR3','TR4','TR5','TR6','TR7','TR8');

        $count = 1;
        $toothlist2ArrCheckbox = '';

        foreach($toothlist2Arr as $value){
            $SQLPerio = "
            SELECT tooth_id
                   ,symbol
            FROM visit_perio_chart
            WHERE patient_visit_id = {$patient_visit_id}
            AND category = '{$form_type}'
            AND tooth_id = '{$value}'
            {$appendSql}
            ";
            $resultPerio = $db->sql_query($SQLPerio);
            $selected_toothList = '';
            $imageTag = '';
            $imagepath = '';
            $checkedStatus = '';
            $disableCheckbox = '';
            while($rowPerio = $db->sql_fetchrow($resultPerio)){
                $imagepath = $cpCfg['cp.localPath']."images/SelectedPerioSymbols/{$rowPerio['symbol']}.png";
                $imageTag  = "<img class='selectedToothSymbol {$rowPerio['symbol']}' tooth_id='{$rowPerio['tooth_id']}' patient_visit_id='{$patient_visit_id}' src='{$imagepath}'/>";

                $checkedStatus   = "checked = checked";
                $disableCheckbox = "disabled = 1";
            }

            $toothlist2ArrCheckbox .= "
            <div class='type-check ym-fbox-check'>
                {$imageTag}
                <input type='checkbox' id='selected_tooth2_{$count}' Checkbox_ID='{$count}' {$checkedStatus} {$disableCheckbox} value='{$value}' name='selected_tooth2[]'>
                <label for='selected_tooth2_{$count}'>{$value}</label>
            </div>
            ";

            $count++;
        }

        return $toothlist2ArrCheckbox;

    }


    /**
     *
     */
    function getToothlistSecond($patient_visit_id = '', $form_type = '', $labs_id = ''){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        if($form_type == ''){
            $form_type = $fn->getReqParam('tooth_form_type');
        }

        $appendSql = '';
        if($labs_id == ''){
            $labs_id = $fn->getReqParam('labs_id');
            if($labs_id != ''){
                $appendSql = "AND labs_id = '{$labs_id}'";
            }
        }

        $toothlist3Arr = array('BL8','BL7','BL6','BL5','BL4','BL3','BL2','BL1','BR1','BR2','BR3','BR4','BR5','BR6','BR7','BR8');

        $count = 1;
        $toothlist3ArrCheckbox = '';

        foreach($toothlist3Arr as $value){
            $SQLPerio = "
            SELECT tooth_id
                   ,symbol
                   ,tooth_top
                   ,tooth_left
                   ,tooth_right
                   ,tooth_bottom
                   ,tooth_center
                   ,symbol
            FROM visit_perio_chart
            WHERE patient_visit_id = {$patient_visit_id}
            AND category = '{$form_type}'
            AND tooth_id = '{$value}'
            {$appendSql}
            ";
            $resultPerio = $db->sql_query($SQLPerio);
            $selected_toothList    = '';
            $imageTag              = '';
            $imagepath             = '';
            $checkedStatus         = '';
            $checkedStatusTop      = '';
            $checkedStatusLeft     = '';
            $checkedStatusRight    = '';
            $checkedStatusCenter   = '';
            $checkedStatusBottom   = '';
            $disableCheckboxTop    = '';
            $disableCheckboxLeft   = '';
            $disableCheckboxRight  = '';
            $disableCheckboxCenter = '';
            $disableCheckboxBottom = '';
            $disableCheckbox       = '';
            $symbolName            = '';
            $editClass             = '';
            $tooth_id              = '';
            while($rowPerio = $db->sql_fetchrow($resultPerio)){
                $imagepath = $cpCfg['cp.localPath']."images/SelectedPerioSymbols/{$rowPerio['symbol']}.png";
                $imageTag  = "<img class='selectedToothSymbol {$rowPerio['symbol']}' tooth_id='{$rowPerio['tooth_id']}' patient_visit_id='{$patient_visit_id}' src='{$imagepath}'/>";

                if($rowPerio['tooth_top'] == '1'){
                    $checkedStatusTop   = "checked = checked";
                    $disableCheckboxTop = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                if($rowPerio['tooth_left'] == '1'){
                    $checkedStatusLeft   = "checked = checked";
                    $disableCheckboxLeft = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                if($rowPerio['tooth_right'] == '1'){
                    $checkedStatusRight   = "checked = checked";
                    $disableCheckboxRight = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                if($rowPerio['tooth_center'] == '1'){
                    $checkedStatusCenter   = "checked = checked";
                    $disableCheckboxCenter = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                if($rowPerio['tooth_bottom'] == '1'){
                    $checkedStatusBottom   = "checked = checked";
                    $disableCheckboxBottom = "disabled = 1";
                    $editClass = "selectedToothSymbolEdit";
                }

                $symbolName = $rowPerio['symbol'];
                $tooth_id = $rowPerio['tooth_id'];
            }

            /*$toothlist3ArrCheckbox .= "
            <div class='type-check ym-fbox-check'>
                {$imageTag}
                <input type='checkbox' id='selected_tooth3_{$count}' Checkbox_ID='{$count}' {$checkedStatus} {$disableCheckbox} value='{$value}' name='selected_tooth3[]'>
                <label for='selected_tooth3_{$count}'>{$value}</label>
            </div>
            ";*/

            $toothlist3ArrCheckbox .= "
            <div class='float_left'>
                <table border='1' class='thinlist'>
                    <tr>
                        <td colspan='3' height='25' class='txtCenter'>
                            <b>{$symbolName}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='3' class='txtCenter'>
                            <div class='tooth_PerioTop'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth3Top_{$count}' Checkbox_ID='{$count}' tooth_part='Top' {$checkedStatusTop} {$disableCheckboxTop} value='{$value}' name='selected_tooth3[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Top' for='selected_tooth3Top_{$count}'></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class='tooth_PerioLeft'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth3Left_{$count}' Checkbox_ID='{$count}' tooth_part='Left' {$checkedStatusLeft} {$disableCheckboxLeft} value='{$value}' name='selected_tooth3[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Left' for='selected_tooth3Left_{$count}'></label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class='tooth_PerioCenter'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth3Center_{$count}' Checkbox_ID='{$count}' tooth_part='Center' {$checkedStatusCenter} {$disableCheckboxCenter} value='{$value}' name='selected_tooth3[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Center' for='selected_tooth3Center_{$count}'></label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class='tooth_PerioRight'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth3Right_{$count}' Checkbox_ID='{$count}' tooth_part='Right' {$checkedStatusRight} {$disableCheckboxRight} value='{$value}' name='selected_tooth3[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Right' for='selected_tooth3Right_{$count}'></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='3' class='txtCenter'>
                            <div class='tooth_PerioBottom'>
                                <div class='ym-fbox-check1'>
                                    <input type='checkbox' id='selected_tooth3Bottom_{$count}' Checkbox_ID='{$count}' tooth_part='Bottom' {$checkedStatusBottom} {$disableCheckboxBottom} value='{$value}' name='selected_tooth3[]'>
                                    <label class='{$editClass}' tooth_id='{$tooth_id}' Checkbox_ID='{$count}' patient_visit_id='{$patient_visit_id}' tooth_part='Bottom' for='selected_tooth3Bottom_{$count}'></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='3' class='txtCenter'>
                            <b>{$value}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='3' height='20' class='txtCenter'>
                        </td>
                    </tr>
                </table>
            </div>
            ";

            $count++;
        }

        return $toothlist3ArrCheckbox;

    }

    /**
     *
     */
    function getPerioChartSymbols(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';
        $tooth_id = $fn->getReqParam('tooth_id');
        $tooth_part = $fn->getReqParam('tooth_part');
        $Checkbox_ID = $fn->getReqParam('checboxid');

        $SymbollistArr = array('Cavity',
                               'Filling',
                               'Crown',
                               'Bridge',
                               'Tooth Missing',
                               'Root Present',
                               'Root Filling',
                               'Pulp Non Vital',
                               'Fracture',
                               'Deep Fissure',
                               'Observed',
                               'Tooth Present and Sound',
                               'Tooth Requiring Extraction',
                               'Tooth Recently Extracted',
                               'Artifical Tooth',
                               'Implant');

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action=''>
            <div class='selectedTooth'>Selected Tooth: {$tooth_id}</div>
            <div class='SymbolSelectRadio'>
                {$formObj->getCheckBoxArrRowByArr('', 'selected_Symbols', $SymbollistArr,'')}
            </div>
            <input type='hidden' id='tooth_id' value='{$tooth_id}'>
            <input type='hidden' id='Checkbox_ID' value='{$Checkbox_ID}'>
            <input type='hidden' id='tooth_part' value='{$tooth_part}'>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getSummaryInOrder() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $order_id = $fn->getReqParam('order_id');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND o.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT o.*
              ,(SELECT SUM(round((oi.unit_price * oi.qty),2))
               FROM order_item oi
               WHERE oi.order_id = {$order_id}
               ) AS order_amount
              ,(SELECT SUM(i.invoice_amount) FROM invoice i
                WHERE i.order_id = o.order_id
                AND i.status != 'Cancelled'
                ) AS invoice_amount
              ,(SELECT SUM(r.amount)
                FROM receipt r
                WHERE o.order_id = r.order_id
                AND r.receipt_status != 'Cancelled'
                )AS receipt_amount
              ,(SELECT  SUM(oi.unit_price)
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type = 'Doctor/Nurse'
                )AS consultation_fees
              ,(SELECT  SUM(oi.unit_price) AS Amount
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type != ''
                )AS Total_Amount
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                LEFT JOIN `invoice` i ON (i.order_id = {$order_id})
                WHERE invHist.related_invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                AND i.status != 'Cancelled'
                ) as Amount_Paid
        FROM `order`o
        WHERE o.order_id = {$order_id}
        {$appendSql}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $orderAmt   = number_format(round($row['order_amount']), 2);
        $invoiceAmt = number_format($row['invoice_amount'] ,2);
        $receiptAmt = number_format($row['receipt_amount'] ,2);

        $total_invoice_amount = 0;
        if($row['invoice_amount'] != ''){
            $total_invoice_amount = $row['invoice_amount'] - $row['discount'];
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
        }else{
            $total_invoice_amount = $row['invoice_amount'];
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
        }

        $total_invoice_amount = number_format($total_invoice_amount, 2);

        $outstandingInvoiceAmt = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);
        $overallBalanceAmt     = number_format($row['order_amount'] - $row['receipt_amount'], 2);

        $order_items_Details = '';

        $Lab = '';
        $SQLOrderItem = "
        SELECT  record_type
               ,SUM(unit_price) AS Amount
               ,SUM(unit_price*qty) AS QTY_AMOUNT
        FROM order_item
        WHERE order_id = {$row['order_id']}
        AND record_type != ''
        GROUP BY record_type
        ORDER BY record_type ASC
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

        $Sub_Total = 0;
        if($numRowsOrderItem > 0){
            $count = 1;
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id = {$row['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                if($rowOrderItem['record_type'] == 'Doctor/Nurse'){
                    $rowOrderItem['record_type'] = 'Consultation Fees';
                }


                if($rowOrderItem['record_type'] == 'Inventory'){
                    $rowOrderItem['record_type'] = 'Medicines and Other Charges';
                    $rowOrderItem['Amount'] = $rowOrderItem['QTY_AMOUNT'];
                }

                $Lab .= "<tr>
                            <td><b>{$rowOrderItem['record_type']}</b>
                            <ol>
                        ";


                if($numRowsList > 0){
                    while($rowList    = $db->sql_fetchrow($resultList)){
                        if($rowOrderItem['record_type'] != 'Consultation Fees'){
                            $Lab .= "<li>{$rowList['item_title']}</li>";
                        }
                    }
                }

                $Lab .="</ol></td>
                                <td class='txtRight'>{$rowOrderItem['Amount']}</td>
                            </tr>";

                $Sub_Total += $rowOrderItem['Amount'];

                $count++;
            }
        }

        $order_items_Details .="{$Lab}";
        $total_amount = number_format($Sub_Total - $row['discount'], 2);
        $Sub_Total    = number_format($Sub_Total, 2);
        $discount     = number_format($row['discount'], 2);

        $rows = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <table class='thinlist'>
                        <tr>
                            <th>Total Amount: {$total_invoice_amount}</th>
                            <th>Amount Paid: {$invoiced_Paid_Amount}</th>
                            <th>Amount Due: {$balance_Amount}</th>
                        </tr>
                    </table>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tr>
                            <th>Description</th>
                            <th class='txtRight'>Amount</th>
                        </tr>
                        {$order_items_Details}
                        <tr>
                            <th>Sub Total</th>
                            <th class='txtRight'>{$Sub_Total}</th>
                        </tr>
                        <tr>
                            <th>Discount</th>
                            <th class='txtRight'>{$discount}</th>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <th class='txtRight'>{$total_amount}</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        ";

        $text = "
        {$rows}
        ";

        return $text;

    }

    /**
     *
     */
    function getViewSummaryLabs() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $order_id = $fn->getReqParam('order_id');
        $labs_id  = $fn->getReqParam('labs_id');

        $SQLReceipt = "
        SELECT date
              ,amount
        FROM payments_receipt
        WHERE order_id = {$order_id}
        AND  labs_id = {$labs_id}
        AND receipt_status != 'Cancelled'
        ";
        $resultReceipt = $db->sql_query($SQLReceipt);
        $numRowsReceipt = $db->sql_numrows($resultReceipt);
        $rows = '';
        if($numRowsReceipt > 0){
            $count = 1;
            while($rowReceipt  = $db->sql_fetchrow($resultReceipt)){
                $amount = number_format($rowReceipt['amount'], 2);
                $rows .= "
                <tr>
                    <td>{$rowReceipt['date']}</td>
                    <td class='txtRight'>{$amount}</td>
                </tr>
                ";
            }
        }

        $SQL = "
        SELECT l.*
              ,(SELECT SUM(i.payments_amount) FROM payments i
                WHERE i.labs_id = l.labs_id
                AND i.status != 'Cancelled'
                ) AS invoice_amount
              ,(SELECT SUM(r.amount)
                FROM payments_receipt r
                WHERE r.labs_id = l.labs_id
                AND r.receipt_status != 'Cancelled'
                )AS receipt_amount
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM payments_receipt_history invHist
                LEFT JOIN payments_receipt r ON (r.payments_receipt_id = invHist.payments_receipt_id)
                LEFT JOIN `payments` i ON (i.labs_id = {$labs_id})
                WHERE invHist.payments_id =  i.payments_id
                AND r.receipt_status != 'Cancelled'
                AND i.status != 'Cancelled'
                ) as Amount_Paid
        FROM `labs` l
        WHERE l.labs_id = {$labs_id}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $total_invoice_amount = 0;
        if($row['invoice_amount'] != ''){
            $total_invoice_amount = $row['invoice_amount'];
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
        }else{
            $total_invoice_amount = $row['invoice_amount'];
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
        }

        $total_invoice_amount = number_format($total_invoice_amount, 2);
        $overallBalanceAmt    = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);

        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class='txtRight'>Amount</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        <br/>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th colspan = '2'>Summary</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Amount payable</td>
                    <td class='txtRight'>{$total_invoice_amount}</td>
                </tr>
                <tr>
                    <td>Amount paid</td>
                    <td class='txtRight'>{$invoiced_Paid_Amount}</td>
                </tr>
                <tr>
                    <td>Outstanding Amount</td>
                    <td class='txtRight'>{$overallBalanceAmt}</td>
                </tr>
            </tbody>
        </table>
        ";

        return $text;
    }

}