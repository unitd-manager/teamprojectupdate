<?
class CP_Admin_Modules_Labsg_Appointment_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT a.patient_information_id
              ,p.name
              ,p.nric
              ,p.mobile
              ,a.check_up_date
              ,a.check_up_time
              ,a.appointment_id
        FROM appointment a
        LEFT JOIN (patient_information p) ON (p.patient_information_id = a.patient_information_id)
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
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_flat');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_street');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_town');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_state');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_country');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'supplier_type');
        $fa = $fn->addToFieldsArray($fa, 'customer_type');
        $fa = $fn->addToFieldsArray($fa, 'mark_up_percentage');
        $fa = $fn->addToFieldsArray($fa, 'cst_no');
        $fa = $fn->addToFieldsArray($fa, 'tin_no');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'company_id'      => $phpExcel->getFldObj('Company ID')
             ,'company_name'    => $phpExcel->getFldObj('Company Name')
             ,'category'        => $phpExcel->getFldObj('Category')
             ,'company_size'    => $phpExcel->getFldObj('Company Size')
             ,'industry'        => $phpExcel->getFldObj('Industry')
             ,'source'          => $phpExcel->getFldObj('Source')
             ,'website'         => $phpExcel->getFldObj('Website')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')

             ,'address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'address_street'  => $phpExcel->getFldObj('Address Street')
             ,'address_town'    => $phpExcel->getFldObj('Address Town')
             ,'address_state'   => $phpExcel->getFldObj('Address State')
             ,'address_country' => $phpExcel->getFldObj('Address Country')

             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comment_by'      => $phpExcel->getFldObj('Comment By')
             ,'notes'           => $phpExcel->getFldObj('Notes')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'      => $phpExcel->getImportFldObj('Product Code')
             ,'title'             => $phpExcel->getImportFldObj('Title')
             ,'description_short' => $phpExcel->getImportFldObj('Short Description')
             ,'description'       => $phpExcel->getImportFldObj('Description')
             ,'picture'           => $phpExcel->getImportFldObj('Picture Ref')
             ,'published'         => $phpExcel->getImportFldObj('Published')
             ,'category_id'       => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'   => $phpExcel->getImportFldObj('Sub Category')
        );

        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Product');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        /****************************************/
        $config = array(
             'module'              => 'labsg_company'
            ,'matchFieldArr'       => array('product_code')
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($product_id, $fa) {
        $media = Zend_Registry::get('media');

        if ($fa['picture'] != ''){
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            $media->model->createMedia('ecommerce_product', 'picture', $product_id, $exp);
        }
    }
    /**
     *
     */
    function getlabsgCompanylabsgContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }
    /**
     *
     */
    function getlabsgCompanylabsgDiscountLinkSQL($id) {

        return "
        SELECT d.discount_id
              ,pg.title
              ,c.title AS category_title
              ,d.margin
              ,d.discount_percent
        FROM discount d
        LEFT JOIN (product_group pg) ON (d.product_group_id = pg.product_group_id)
        LEFT JOIN (category c) ON (d.category_id = c.category_id)
        WHERE d.company_id = {$id}
        ORDER BY pg.sort_order
        ";
    }

    /**
     *
     */
    function getlabsgCompanylabsgCompanyGroupLinkSQL1($id) {

        return "
        SELECT a.company_id
              ,a.company_name
              ,a.status
        FROM company_group b, company a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
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

        $jsonArray = array();

        $appendSql = "";
        if($doctor_id != ''){
           $appendSql ="WHERE dr_Linked = {$doctor_id}";
        }

        $SQL = "
        SELECT  a.appointment_id
               ,a.check_up_time
               ,a.check_up_date
               ,a.dr_Linked
               ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS Patient_Name
               ,a.status
        FROM appointment a
        LEFT JOIN (patient_information p) ON (p.patient_information_id = a.patient_information_id)
       ";

        //$SQL = $this->getSQL();
        $result  = $db->sql_query($SQL);

        $title = '';
        while ($row = $db->sql_fetchrow($result)) {
             $eventStartdate    = $row['check_up_date'] .' ' . $row['check_up_time'];
             $eventEnddate      = $row['check_up_date'] .' ' . $row['check_up_time'];
             $appointmentLink   = "index.php?module=labsg_appointment&_spAction=appointmentDetails&appointment_id={$row['appointment_id']}&showHTML=0";
             $patient_name      = "<a class='evenDetails' href='{$appointmentLink}'>".$row['Patient_Name']."</a>";
             $cancelAppointment = "<a class='cancelAppointment cancelAppointmentOnEvent' appointment_id={$row['appointment_id']}>Cancel</a>";

            $SQLPatientVisit = "
            SELECT pv.appointment_id
                  ,pv.patient_visit_id
                  ,pv.status
            FROM patient_visit pv
            WHERE appointment_id = {$row['appointment_id']}
            ";
            $resultPatientVisit  = $db->sql_query($SQLPatientVisit);
            $numRowsPatientVisit = $db->sql_numrows($resultPatientVisit);
            $rowPatientVisit     = $db->sql_fetchrow($resultPatientVisit);

            $createVisit       = "<a class='createVisit' appointment_id='{$row['appointment_id']}'> Create Visit </a>";

            if($numRowsPatientVisit > 0){
                $patientVisitLink = "index.php?_topRm=main&module=labsg_patientVisit&_action=edit&patient_visit_id={$rowPatientVisit['patient_visit_id']}";
                $createVisit = "<a class = 'viewVisitRecord' href='{$patientVisitLink}'><u>View Record</u></a>
                ";
            }

             $backgroundColor = '#0048FF';
             if($row['status'] == 'Cancelled'){
                $backgroundColor = '#808080';
                $cancelAppointment = "<font class='cancelledAppointment'>Cancelled</font>";
                //$patient_name      = "<font class='cancelledAppointmentDetails'>Patient Name: ".$row['name']."</font>";
                //$doctor_name       = "<font class='cancelledAppointmentDetails'>Doctor Name: ".$row['employee_name']."</font>";
             }

             $buildjson = array(
              'title'             => $title
             ,'patient_name'      => $patient_name
             ,'start'             => $eventStartdate
             ,'end'               => $eventEnddate
             ,'allDay'            => false
             //,'url'               => $appointmentLink
             ,'backgroundColor'   => $backgroundColor
             ,'borderColor'       => $backgroundColor
             ,'appointment_id'    => $row['appointment_id']
             ,'cancelAppointment' => $cancelAppointment
             ,'createVisit'       => $createVisit
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
    function getAppointmentDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $expNoEdit = array('disabled' => true);

        $appointment_id  = $fn->getReqParam('appointment_id');
        $visitDetailsRow = $this->getAppointmentDetailsRow($appointment_id);
        $rowAppointment  = $fn->getRecordRowByID('appointment', 'appointment_id', $appointment_id);

        $sqlEmployee = "
        SELECT employee_id
              ,employee_name
        FROM employee
        ORDER BY employee_name
        ";

        $appointmentDetails = "
        <div id='appointmentDetails1'>
            <input type='hidden' id='appointment_id_appoint' value='{$appointment_id}' />
            <div  class='appointmentvisitScroll1'>
                <table class='thinlist'>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th class= 'txtCenter'>Visit</th>
                            <th>NRIC</th>
                            <th>Phone No</th>
                            <th>Email</th>
                            <th>Notes</th>
                            <th>Edit</th>
                            <th>Cancel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <div id='appointmentDetailsEvent'>
                            {$visitDetailsRow}
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
    function getAppointmentDetailsRow($appointment_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $visitDetailsRow = '';
        $sqlVisitDetails = "
        SELECT  CONCAT_WS('', p.first_name, p.middle_name, p.last_name) AS Patient_Name
               ,p.phone
               ,p.email
               ,p.nric
               ,a.patient_information_id
               ,a.check_up_date
               ,a.check_up_time
               ,a.status
               ,a.description
               ,a.appointment_id
        FROM appointment a
        LEFT JOIN (patient_information p) ON (p.patient_information_id = a.patient_information_id)
        WHERE a.appointment_id = '{$appointment_id}'
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
            <div class='button'>
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
                $createVisit       = "<b>{$rowVisitDetails['status']}</b>";
            }

            $visitDetailsRow .="
            <tr>
                <td>{$rowVisitDetails['Patient_Name']}</td>
                <td class= 'txtCenter'>{$createVisit}</td>
                <td>{$rowVisitDetails['nric']}</td>
                <td>{$rowVisitDetails['phone']}</td>
                <td>{$rowVisitDetails['email']}</td>
                <td id='notesAppointmentTd'>
                    {$this->getAppointmentNotes($rowVisitDetails['description'], $rowVisitDetails['appointment_id'])}
                </td>
                <td>
                    <a class='notesEditLink'>Click To Edit</a>
                </td>
                <td>{$cancelAppointment}</td>
            </tr>
            ";
        }

        return $visitDetailsRow;

    }

    /**
     *
     */
    function getAppointmentNotes($description = '', $appointment_id = ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $expNoEdit = array('isEditable' => 0);

        if($description == ''){
            $description = $fn->getReqParam('notes');
        }

        if($appointment_id == ''){
            $appointment_id = $fn->getReqParam('appointment_id');
        }

        $text = "
        <div class='notesAppointmentDefault'>
            {$formObj->getTARow('', 'description', $description, $expNoEdit)}
        </div>
        <div class='notesAppointment notesAppointmentdisable'>
            {$formObj->getTARow('', 'description', $description)}
            <input type='button' appointment_id = '{$appointment_id}' name='Update' class='appointmentNotesUpdate' value='Update'/>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getUpdateAppointmentNotes(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $appointment_id = $fn->getReqParam('appointment_id');
        $notes          = $fn->getReqParam('notes');

        $SQLUpdateNotes = "
        UPDATE appointment SET description = '{$notes}'
        WHERE appointment_id = {$appointment_id}
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
    function getAppointmentValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $check_up_date          = $fn->getPostParam('check_up_date');
        $check_up_time          = $fn->getPostParam('check_up_time');

        $validate->resetErrorArray();
        //$validate->validateData('employee_id', 'Please select Dr/Nurse');
        $validate->validateData('patient_name', 'Please select patient');
        $validate->validateData('check_up_date', 'Please select Date');
        $validate->validateData('check_up_time', 'Please select Time');

        if($patient_information_id != ''){
            $SQL = "
            SELECT patient_information_id
            FROM appointment
            WHERE patient_information_id = {$patient_information_id}
            AND check_up_date = '{$check_up_date}'
            AND check_up_time = '{$check_up_time}'
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
     */
    function getAddAppointmentFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAppointmentValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $employee_id            = $fn->getPostParam('employee_id');
        $check_up_date          = $fn->getPostParam('check_up_date');
        $check_up_time          = $fn->getPostParam('check_up_time');
        $description            = $fn->getPostParam('description');
        $cpSiteIdSession        = $fn->getSessionParam('cp_site_id');

        $fa = array();

        $fa['patient_information_id'] = $patient_information_id;
        $fa['check_up_date']          = $check_up_date;
        $fa['check_up_time']          = $check_up_time;
        $fa['status']                 = 'New';

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $fa['description']            = $description;
        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'appointment');
        $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     *
     */
    function getChangeAppointmentByDrag(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $appointment_id = $fn->getReqParam('appointment_id');
        $check_up_date  = $fn->getReqParam('check_up_date');
        $check_up_time  = $fn->getReqParam('check_up_time');
        $viewName       = $fn->getReqParam('viewName');

        $fa = array();

        if($viewName == 'month'){
            $fa['check_up_date']     = $check_up_date;
            $fa['modification_date'] = date("Y-m-d H:i:s");
            $fa['modified_by']       = $fn->getSessionParam('userName');
        }else{
            $fa['check_up_date']     = $check_up_date;
            $fa['check_up_time']     = $check_up_time;
            $fa['modification_date'] = date("Y-m-d H:i:s");
            $fa['modified_by']       = $fn->getSessionParam('userName');
        }

        $whereCondition  = "WHERE appointment_id = {$appointment_id}";
        $sqlUpdate       = $dbUtil->getUpdateSQLStringFromArray($fa, "appointment", $whereCondition);
        $resultUpdate    = $db->sql_query($sqlUpdate);

    }

    /**
     *
     *
     */
    function getCreateVisitRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $appointment_id = $fn->getReqParam('appointment_id');
        $rowAppointment = $fn->getRecordRowByID('appointment', 'appointment_id', $appointment_id);
        $currentDate    = date("Y-m-d");
        $currentTime    = date("H:i:s");
        $visit_code      = $fn->getSettingsValueByKey("nextPatientvisitCode");

        $fa = array();

        $fa['patient_information_id'] = $rowAppointment['patient_information_id'];
        $fa['status']                 = 'New';
        $fa['check_up_date']          = $currentDate;
        $fa['check_up_time']          = $currentTime;
        $fa['appointment_id']         = $appointment_id;
        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');
        $fa['visit_code']             = $visit_code;

        $insertVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'patient_visit');
        $resultVisitSQL   = $db->sql_query($insertVisitSQL);
        $patient_visit_id = $db->sql_nextid();

        //To update patient visit code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientvisitCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQL = "
        SELECT MAX(pq.queue_no) AS queue_no
        FROM patient_queue pq
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = pq.patient_visit_id)
        WHERE pq.check_up_date = '{$currentDate}'
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $queue_no = $row['queue_no'];

        if($queue_no == ''){
            $queue_no = 1;
        }else{
            $queue_no = $row['queue_no'] + 1;
        }

        $fa1 = array();

        $fa1['patient_information_id'] = $rowAppointment['patient_information_id'];
        $fa1['check_up_date']          = $currentDate;
        $fa1['check_up_time']          = $currentTime;
        $fa1['queue_no']               = $queue_no;
        $fa1['patient_visit_id']       = $patient_visit_id;
        $fa1['creation_date']          = date("Y-m-d H:i:s");
        $fa1['created_by']             = $fn->getSessionParam('userName');

        $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'patient_queue');
        $resultQueueSQL = $db->sql_query($insertQueueSQL);
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
    function getCancelVisitRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $SqlCancelVisit = "
        UPDATE patient_visit SET status = 'Cancelled'
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultCancelVisit  = $db->sql_query($SqlCancelVisit);

        /*$SqlDeleteQueue = "
        DELETE FROM patient_queue
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultDeleteQueue  = $db->sql_query($SqlDeleteQueue);*/
    }

    /**
     *
     *
     */
    function getCancelAppointmentRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $appointment_id = $fn->getReqParam('appointment_id');

        $SqlCancelVisit = "
        UPDATE appointment SET status = 'Cancelled'
        WHERE appointment_id = {$appointment_id}
        ";
        $resultCancelVisit  = $db->sql_query($SqlCancelVisit);

        $SqlCancelVisit = "
        UPDATE patient_visit SET status = 'Cancelled'
        WHERE appointment_id = {$appointment_id}
        ";
        $resultCancelVisit  = $db->sql_query($SqlCancelVisit);
    }
}
