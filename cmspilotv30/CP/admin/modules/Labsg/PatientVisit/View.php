<?
class CP_Admin_Modules_Labsg_PatientVisit_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $count   = 0;
        $rows    = '';
        $searchDone = $fn->getReqParam('searchDone');

        foreach ($dataArray as $row){
            $email     = $row['email'];

            if($row['bill_type'] == "Company" || $row['bill_type'] == "Panel"){
            
                $sqlCompany = "
                SELECT company_name
                FROM company
                WHERE company_id = {$row['company_id']}
                ";
                $resultCompany = $db->sql_query($sqlCompany);
                $rowCompany    = $db->sql_fetchrow($resultCompany);

                $row['bill_type'] = $row['bill_type'].' ('.$rowCompany['company_name'].')';
            }

            if($row['order_id']){
                $invoiced = "Yes";
            } else {
                $invoiced = "No";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['visit_code'])}
            {$listObj->getListDateCell($row['check_up_date'])}
            {$listObj->getListDataCell($row['check_up_time'])}
            {$listObj->getListDataCell($row['name'])}
            {$listObj->getListDataCell($row['nationality'])}
            {$listObj->getListDateCell($row['dob'])}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($row['registration_no'])}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListDataCell($row['bill_type'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($invoiced, 'center')}
            {$listObj->getListRowEnd($row['patient_information_id'])}
            ";
            $count++ ;
        }

        //$newPatientLink = "index.php?_topRm=main&module=labsg_patientVisit&_action=new";
        $search_List    = "index.php?_topRm=main&module=labsg_patientVisit";
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
            <div>&nbsp;</div>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Visit Code', 'pv.visit_code')}
            {$listObj->getListHeaderCell('Visit Date', 'pv.check_up_date')}
            {$listObj->getListHeaderCell('Appointment', 'pv.check_up_time')}
            {$listObj->getListHeaderCell('Patient Name', 'p.name')}
            {$listObj->getListHeaderCell('Nationality', 'p.nationality')}
            {$listObj->getListHeaderCell('DOB', 'p.dob')}
            {$listObj->getListHeaderCell('Gender', 'p.gender')}
            {$listObj->getListHeaderCell('Passport / ID', 'p.registration_no')}
            {$listObj->getListHeaderCell('Mobile', 'p.mobile')}
            {$listObj->getListHeaderCell('Bill Type', 'p.bill_type')}
            {$listObj->getListHeaderCell('Status', 'pv.status')}
            {$listObj->getListHeaderCell('Invoiced', '')}
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
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset1 = "
        {$formObj->getDateRow('Check Up Date', 'check_up_date')}
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

        //$newPatientLink     = "index.php?_topRm=main&module=labsg_patientVisit&_action=new";
        $patient_visit_List = "index.php?_topRm=main&module=labsg_patientVisit";
        $expHideFirstOpt    = array('hideFirstOption' => 1);
        $searchlistArr      = array('Search by Name'
                                   ,'Search by NRIC');

        $row    = '';

        $formActionAddpatient = "index.php?module=labsg_patientVisit&_spAction=addPatientRecord&showHTML=0";

        $searchResultRows = $this->getPatientVisitSearchResult();
        $searchResultAppointmentRows = $this->getPatientVisitAppointmentSearchResult();

                /*<div class='searchSelectPatientVisit'>
                    {$formObj->getDDRowByArr('', 'search_type_by_list', $searchlistArr, '', $expHideFirstOpt)}
                </div>*/
        $text = "
        <div class='floatbox'>
            <div class='float_left displayVisitRecords'>
                <a href='#' class='button'>Display Visit Records</a>
            </div>
            <div class='float_right button mb10'>
                <a href='{$formActionAddpatient}' id='addPatientRecord'>Quick Add Patient</a>
            </div>
        </div>
        <div class='searchPanelInPatientVisitLabel'>
            <label class=''>Please key in related words below to search the records<br/>(Name, Passport / ID, Company Name)</label>

            <div class='searchPanelInPatientVisit'>
                <input class='searchInputPatientVisit'/>
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
        SELECT p.*
              ,c.company_name
        FROM patient_information p
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        WHERE (p.name LIKE '%{$inputBoxVaue}%'
           OR p.registration_no LIKE '%{$inputBoxVaue}%'
           OR c.company_name LIKE '%{$inputBoxVaue}%')
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
                  ,pv.visit_code
                  ,pv.check_up_time
            FROM patient_visit pv
            WHERE patient_information_id = {$rec['patient_information_id']}
            AND pv.check_up_date = '{$currentDate}'
            {$appendSqlPV}
            ORDER BY patient_visit_id DESC
            ";
            $resultPatientVisit  = $db->sql_query($SQLPatientVisit);
            $numRowsPatientVisit = $db->sql_numrows($resultPatientVisit);

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
            <div class='button visitCreateButton'>
                <a class='createVisit' patient_information_id={$rec['patient_information_id']} dr_required='{$rowAppointment['dr_Linked']}' appointment_id='{$rowAppointment['appointment_id']}'>
                    Create Visit
                </a>
            <div>
            ";

            if($rec['bill_type'] == "Company" || $rec['bill_type'] == "Panel"){
            
                $sqlCompany = "
                SELECT company_name
                FROM company
                WHERE company_id = {$rec['company_id']}
                ";
                $resultCompany = $db->sql_query($sqlCompany);
                $rowCompany    = $db->sql_fetchrow($resultCompany);

                $rec['bill_type'] = $rec['bill_type'].' ('.$rowCompany['company_name'].')';
            }

            if($numRowsPatientVisit > 0){
                while($rowPatientVisit = $db->sql_fetchrow($resultPatientVisit)){
                    if($numRowsPatientVisit > 0){
                        $patientVisitLink = "index.php?_topRm=main&module=labsg_patientVisit&_action=edit&patient_visit_id={$rowPatientVisit['patient_visit_id']}";
                        $createVisit = "<a class = 'button viewVisitRecord' href='{$patientVisitLink}'>
                                            View Record - {$rowPatientVisit['visit_code']}
                                        </a>
                                        <a class = 'mt10 button duplicatePatientVisit' patient_information_id={$rec['patient_information_id']} dr_required='{$rowAppointment['dr_Linked']}' appointment_id='{$rowAppointment['appointment_id']}'>Take other Test</a>
                        ";
                    }

                    $highlightRow = '';
                    if ($rowPatientVisit['status'] == 'Cancelled') {
                        $highlightRow = "highlight";
                    }

                    $text .= "
                    <tr class='{$highlightRow}'>
                        <td>{$rec['name']}</td>
                        <td>{$rec['nationality']}</td>
                        <td>{$dob}</td>
                        <td>{$rec['gender']}</td>
                        <td>{$rowPatientVisit['check_up_time']}</td>
                        <td class='txtCenter'>{$createVisit}</td>
                        <td>{$rec['registration_no']}</td>
                        <td>{$rowPatientVisit['status']}</td>
                        <td>{$rec['bill_type']}</td>
                    </tr>
                    ";
                }
            }else{

                $highlightRow = '';
                if ($rowAppointment['status'] == 'Cancelled') {
                    $highlightRow = "highlight";
                }

                $text .= "
                <tr class='{$highlightRow}'>
                    <td>{$rec['name']}</td>
                    <td>{$rec['nationality']}</td>
                    <td>{$dob}</td>
                    <td>{$rec['gender']}</td>
                    <td>{$rowAppointment['check_up_time']}</td>
                    <td class='txtCenter'>{$createVisit}</td>
                    <td>{$rec['registration_no']}</td>
                    <td>{$rowAppointment['status']}</td>
                    <td>{$rec['bill_type']}</td>
                </tr>
                ";
            }
        }

        if($numRows > 0){
            $resultRow = "
            <div class='searchResultLabel'>
                <label class=''>Please find the Search Results below : {$numRows} Record(s)</label>
            </div>
            <table class='thinlist'>
                <thead>
                    <th>Patient Name</th>
                    <th>Nationality</th>
                    <th>DOB</th>
                    <th>Gender</th>
                    <th>Appointment</th>
                    <th class='txtCenter'>Visit</th>
                    <th>Passport / ID</th>
                    <th>Status</th>
                    <th>Bill Type</th>
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

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
        SELECT a.appointment_id, a.check_up_date, a.check_up_time
              ,p.*
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
        FROM appointment a
        LEFT JOIN patient_information p ON (p.patient_information_id = a.patient_information_id)
        WHERE a.check_up_date = '{$currentDate}'
        {$appendSqlAp}
        UNION
        SELECT pv.patient_visit_id, pv.check_up_date, pv.check_up_time
              ,p.*
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
        FROM patient_visit pv
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        WHERE pv.check_up_date = '{$currentDate}'
          AND pv.record_type = 'Walk In'
          {$appendSqlPV}
        ORDER BY appointment_id DESC
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        while($rec    = $db->sql_fetchrow($result)){

            $dob = $fn->getCPDate($rec['dob'], 'd M Y');

            $SQLPatientVisit = "
            SELECT pv.visit_code, pv.status
            FROM patient_visit pv
            WHERE patient_visit_id = {$rec['appointment_id']}
            ";
            $resultPatientVisit  = $db->sql_query($SQLPatientVisit);
            $numRowsPatientVisit = $db->sql_numrows($resultPatientVisit);
            $rowPatientVisit     = $db->sql_fetchrow($resultPatientVisit);

            $SQLAppointment = "
            SELECT a.appointment_id
                  ,a.dr_Linked
            FROM appointment a
            WHERE appointment_id = {$rec['appointment_id']}
            AND a.check_up_date = '{$currentDate}'
            ";
            $resultAppointment   = $db->sql_query($SQLAppointment);
            $numRowsAppointment  = $db->sql_numrows($resultAppointment);
            $rowAppointment      = $db->sql_fetchrow($resultAppointment);

            $createVisit = "
            <div class='button visitCreateButton'>
                <a class='createVisit' patient_information_id={$rec['patient_information_id']} dr_required='{$rowAppointment['dr_Linked']}' appointment_id='{$rowAppointment['appointment_id']}'>
                    Create Visit
                </a>
            <div>
            ";

            $status = '';
            $highlightRow = '';
            $printLabelBtn = "";
            if($numRowsPatientVisit > 0){
                $patientVisitLink = "index.php?_topRm=main&module=labsg_patientVisit&_action=edit&patient_visit_id={$rec['appointment_id']}";
                $createVisit = "
                <a class = 'button viewVisitRecord' href='{$patientVisitLink}'>View Record - {$rowPatientVisit['visit_code']}</a><br/>
                <a class = 'mt10 button duplicatePatientVisit' patient_information_id={$rec['patient_information_id']} dr_required='{$rowAppointment['dr_Linked']}' appointment_id='{$rowAppointment['appointment_id']}'>Take other Test</a>
                ";
                $status = $rowPatientVisit['status'];
                if ($status == 'Cancelled') {
                    $highlightRow = "highlight";
                } else {
                    $printLabelBtn = "
                    <div class='button'><a class='printLabelPatientVisitList' patient_visit_id='{$rec['appointment_id']}' patient_information_id='{$rec['patient_information_id']}'>Print Label</a></div>                    
                    ";
                }
            }

            if($rec['bill_type'] == "Company" || $rec['bill_type'] == "Panel"){
            
                $sqlCompany = "
                SELECT company_name
                FROM company
                WHERE company_id = {$rec['company_id']}
                ";
                $resultCompany = $db->sql_query($sqlCompany);
                $rowCompany    = $db->sql_fetchrow($resultCompany);

                $rec['bill_type'] = $rec['bill_type'].' ('.$rowCompany['company_name'].')';
            }

            $text .= "
            <tr class='{$highlightRow}'>
                <td>{$rec['name']}</td>
                <td>{$rec['nationality']}</td>
                <td>{$dob}</td>
                <td>{$rec['gender']}</td>
                <td>{$rec['check_up_time']}</td>
                <td class='txtCenter'>{$createVisit}</td>
                <td>{$rec['registration_no']}</td>
                <td>{$status}</td>
                <td>{$rec['bill_type']}</td>
                <td style='text-align:center;'>{$printLabelBtn}</td>
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
                    <th>Patient Name</th>
                    <th>Nationality</th>
                    <th>DOB</th>
                    <th>Gender</th>
                    <th>Appointment</th>
                    <th class='txtCenter'>Visit</th>
                    <th>Passport / ID</th>
                    <th>Status</th>
                    <th>Bill Type</th>
                    <th class='txtCenter'>Print</th>
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
        $dateUtil = Zend_Registry::get('dateUtil');

        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        //$sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        //$expCountry = array('detailValue' => $row['address_country']);

        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('patientVisitCategory');
        $sqlTitle    = $fn->getValueListSQL('patientVisitTitle');
        $expNoEdit = array('isEditable' => 0);

        $testLbl = "
        <li class='first'>
            <a href='#tabs-1'>Tests</a>
        </li>
        ";

        $testTab = "
        <div id='tabs-1' class='ui-tabs-hide'>
            <div class='subcolumns'>
                <div id=''><form>Test</form></div>
            </div>
        </div>
        ";

        $recCount = $fn->getRecordCount('order_item', "patient_visit_id = '{$row['patient_visit_id']}'", array('includeSiteId' => false));

        $addclass = '';
        $message = '';
        if($recCount > 0){
            $addclass = "highlight";
            $message = "<div>Please Note that Invoice is already generated for the visit.</div>";
        }

        $treatmentLbl = "
        {$message}
        <li class='second'>
            <a class='{$addclass}' href='#tabs-2'>Lab Tests</a>
        </li>
        ";

        $treatmentTab = "
        <div id='tabs-2'>
            <div class='subcolumns'>
                <div id='treatmentDisplay'>{$this->getTreatmentPortalDisplay($row['patient_visit_id'])}</div>
            </div>
        </div>
        ";

        $summaryLbl = "
        <li class='third'>
            <a href='#tabs-3'>Summary</a>
        </li>
        ";

        $summaryTab = "
        <div id='tabs-3'>
            <div id='summaryDisplay'>{$this->getSummaryPortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $urlPrint  = "index.php?module=labsg_patientVisit&_spAction=printLabel&patient_visit_id={$row['patient_visit_id']}&showHTML=0";

        if(!empty($row['dob'])){
            $birthdate = new DateTime($row['dob']);
            $today   = new DateTime('today');
            $age = $birthdate->diff($today)->y;
        }else{
            $age = 0;
        }

        $dateofbirth = $dateUtil->formatDate($row['dob'], 'DD-MM-YYYY');
        $agedob = $dateofbirth.' / '.$age;

        $generateOrder = '';
        if($row['bill_type'] == 'Company'){
            if($row['order_id'] != ''){
                $OrderLink = "index.php?_topRm=finance&module=labsg_order&_action=edit&order_id={$row['order_id']}";
                $gotoOrder = "<a href='{$OrderLink}' class='button'>Goto Order</a>";
                $generateOrder = "<div class='button'><a href='#' id='createOrderRecord' patient_visit_id='{$row['patient_visit_id']}'>Update Bill</a></div>";
            }
            else{
                $generateOrder = "<div class='button'><a href='#' id='createOrderRecord' patient_visit_id='{$row['patient_visit_id']}'>Generate Bill</a></div>";
            }
        } else {
            $rowOrder = $fn->getRecordRowByID('order', 'patient_visit_id', $row['patient_visit_id']);
            $numRowsInvoice = 0;
            if (is_array($rowOrder)) {
                $SQLInvoice = "
                SELECT i.*
                FROM invoice i
                WHERE i.order_id = {$rowOrder['order_id']}
                AND i.status != 'Cancelled'
                ";
                $resultInvoice = $db->sql_query($SQLInvoice);
                $numRowsInvoice = $db->sql_numrows($resultInvoice);
            }

            if($numRowsInvoice == 0){
                $generateOrder = "<div class='button mr5'><a href='#' id='createOrderRecordIndividual' patient_visit_id='{$row['patient_visit_id']}'>Generate Bill</a></div>";
            } else {
                $generateOrder = "<div class='button mr5'><a href='#' id='billSummaryOrder' order_id='{$rowOrder['order_id']}'>Bill Summary</a></div>";
            }
        }

        $rowOrder = $fn->getRecordRowByID('order', 'patient_visit_id', $row['patient_visit_id']);

        if($row['bill_type'] == "Company" || $row['bill_type'] == "Panel"){
            
            $sqlCompany = "
            SELECT company_name
            FROM company
            WHERE company_id = {$row['company_id']}
            ";
            $resultCompany = $db->sql_query($sqlCompany);
            $rowCompany    = $db->sql_fetchrow($resultCompany);

            $row['bill_type'] = $row['bill_type'].' ('.$rowCompany['company_name'].')';
        }

        $invoicePortalDisplay = '';
        $receiptPortalDisplay = '';
        if (is_array($rowOrder)) {
            $modObj = getCPModuleObj('labsg_order');
            $invoicePortalDisplay = $modObj->view->getInvoicePortalDisplay($rowOrder);
            $receiptPortalDisplay = $modObj->view->getReceiptPortalDisplay($rowOrder);
        }
        $creation_date = $fn->getCPDate($row['creation_date'],"d-m-Y");
        $modification_date = $fn->getCPDate($row['modification_date'],"d-m-Y");
        $cancelPatientVisitForm = "index.php?module=labsg_patientVisit&_spAction=cancelPatientVisitForm&patient_visit_id={$row['patient_visit_id']}&showHTML=0";

        $actionButton = "<div class='mt10 mb10'><strong>Please note that Patient Visit is Cancelled</strong></div>";
        if ($row['status'] != 'Cancelled') {
            if ($row['order_id']) {
                $actionButton = "
                <div class='button mb20 mr5'><a class='printLabelPatientVisit' patient_information_id='{$row['patient_information_id']}'>Print Label</a></div>
                {$generateOrder}
                <div><strong>Please note Billing is already done for this Visit. To Cancel the visit, Cancel <u>Invoice/Receipt</u> to proceed further</strong></div>
                ";
            } else {
                $actionButton = "
                <div class='button mb20 mr5'><a class='printLabelPatientVisit' patient_information_id='{$row['patient_information_id']}'>Print Label</a></div>
                {$generateOrder}
                <div class='button mb20'><a href='{$cancelPatientVisitForm}' class='cancelPatientVisit' patient_visit_id='{$row['patient_visit_id']}'>Cancel Patient Visit</a></div>
                ";
            }
        }
        //<div class='button mb20'><a href='{$urlPrint}' target='_blank'>Print Label</a></div>
        $text = "
        {$actionButton}
        <div class='float_right'>Created By : {$row['created_by']} on {$creation_date} <br>Modified By: {$row['modified_by']} on {$modification_date}</div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Patient Visit Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Visit Code: {$row['visit_code']}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Patient Name', 'name', $row['name'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Nationality', 'nationality', $row['nationality'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('DOB (dd-mm-yyyy) / Age', 'dob', $agedob, $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Gender', 'gender', $row['gender'], $expNoEdit)}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Passport / ID', 'registration_no', $row['registration_no'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Type of pass', 'pass_type', $row['pass_type'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Bill Type', 'bill_type', $row['bill_type'], $expNoEdit)}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Mobile', 'mobile', $row['mobile'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'], $expNoEdit)}</td>
                                <td>{$formObj->getDateRow('Check Up Date', 'check_up_date', $row['check_up_date'])}</td>
                                <td>{$formObj->getTimeRow('Check Up Time', 'check_up_time', $row['check_up_time'])}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Occupation', 'occupation', $row['occupation'], $expNoEdit)}</td>
                                <td>{$formObj->getDateRow('Follow Up Date', 'follow_up_date', $row['follow_up_date'])}</td>
                                <td class='notesTitle'>{$formObj->getTARow('Summary', 'visit_summary', $row['visit_summary'])}</td>
                                <td class='notesTitle'>{$formObj->getTARow('Remarks', 'notes', $row['notes'])}</td>
                                <input type='hidden' name='patient_visit_id' value='{$row['patient_visit_id']}' />
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id='tabs'>
            <ul>
                {$testLbl}
                {$treatmentLbl}
                {$summaryLbl}
            </ul>
            {$testTab}
            {$treatmentTab}
            {$summaryTab}
            <div class='tab-footer'>
            </div>
        </div>
        {$invoicePortalDisplay}
        {$receiptPortalDisplay}
        ";

        return $text;
    }

    /**
    **/

    function getSummaryInOrder() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $order_id = $fn->getReqParam('order_id');
        $rows  = "";
        $text = '';

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
              ,(SELECT  SUM(oi.unit_price) AS Amount
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type != ''
                )AS Total_Amount
        FROM `order`o
        WHERE o.order_id = {$order_id}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $orderAmt   = number_format(round($row['order_amount']), 2);
        $invoiceAmt = number_format($row['invoice_amount'] ,2);
        $receiptAmt = number_format($row['receipt_amount'] ,2);

        $outstandingInvoiceAmt = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);
        $overallBalanceAmt     = number_format($row['order_amount'] - $row['receipt_amount'], 2);

        $order_items_Details = '';

        $Lab = '';
        $SQLOrderItem = "
        SELECT  record_type
               ,SUM(unit_price) AS Amount
               ,SUM(unit_price*qty) AS QTY_AMOUNT
               ,CONCAT_WS(' ', first_name, middle_name, last_name ) AS patient_name
               ,patient_information_id
        FROM order_item
        WHERE order_id = {$row['order_id']}
        AND record_type != ''
        GROUP BY patient_information_id
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
                AND patient_information_id = {$rowOrderItem['patient_information_id']}
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                $Lab .= "<tr>
                            <td><b>{$rowOrderItem['record_type']}:</b>
                            <ol>
                        ";


                if($numRowsList > 0){
                    while($rowList    = $db->sql_fetchrow($resultList)){
                        $Lab .= "<li>{$rowList['item_title']}</li>";
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

        $sub_total    = number_format($Sub_Total, 2);
        $discount     = number_format($row['discount'], 2);
        $total_amount = number_format($Sub_Total - $row['discount'], 2);

        $rows = "
        <div class='orderSummary'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Bill Summary</div>
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
                            <th class='txtRight summaryBg'>Sub Total</th>
                            <th class='txtRight summaryBg'>{$sub_total}</th>
                        </tr>
                        <tr>
                            <th class='txtRight summaryBg'>Discount</th>
                            <th class='txtRight summaryBg'>{$discount}</th>
                        </tr>
                        <tr>
                            <th class='txtRight summaryBg'>Total Amount</th>
                            <th class='txtRight summaryBg'>{$total_amount}</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        ";

        if($numRowsOrderItem > 0){
        $text = "
        {$rows}
        ";
        }

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

        $formAction = "index.php?_topRm=main&module=labsg_patientVisit&_spAction=summaryPortalSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patientInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id']);

        $text = "
        <div id='' class=''>
            <form id='portalForm_summaryDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
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
                <div class='type-button floatbox'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
            </form>
        </div>
        ";

        return $text;
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

        $formAction = "index.php?_topRm=main&module=labsg_patientVisit&patient_visit_id={$patient_visit_id}&_spAction=treatmentRecordSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

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

        $bill_type    = $fn->getReqParam('bill_type');
        $company_id    = $fn->getReqParam('company_id');

        if($patient_visit_id == ''){
            $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        }

        $VisitRow = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        
        $SQLTreatment = "
        SELECT t.treatment_id
              ,t.title
              ,t.fees
        FROM treatment t
        ";
        $resultTreatment   = $db->sql_query($SQLTreatment);
        $count = 0;
        while ($rowTreatment = $db->sql_fetchrow($resultTreatment)) {
            $sqlTreatVisitRec = "
            SELECT *
            FROM treatment_visit
            WHERE treatment_id = '{$rowTreatment['treatment_id']}'
              AND patient_visit_id = '{$patient_visit_id}'
            LIMIT 0, 1
            ";
            $resultTreatVisitRec = $db->sql_query($sqlTreatVisitRec);
            $treatVisitRec = $db->sql_fetchrow($resultTreatVisitRec);

            $patientinfoRec = $fn->getRecordByCondition('patient_information', "patient_information_id  = '{$VisitRow['patient_information_id']}'");

            if($treatVisitRec['treatment_visit_id'] != ''){
                $checked = "checked='checked'";
                $class ="";
            } else {
                $checked = '';
                $class ="displayNone";
            }

            if($treatVisitRec['notes'] != ''){
                $notes = 'View Notes';
            }else {
                $notes = 'Add Notes';
            }

            $fees = $rowTreatment['fees'];

            /* Displaying Price for tests under Lab test tab - START */
            if($patientinfoRec['bill_type'] == 'Company'){
                $SQLCompanTreat = "
                SELECT ct.treatment_id
                       ,ct.amount
                FROM company_treatment ct
                LEFT JOIN patient_information p ON (p.company_id = ct.company_id)
                WHERE ct.treatment_id = {$rowTreatment['treatment_id']}
                AND p.patient_information_id = {$VisitRow['patient_information_id']}
                ";
                $resultCompanTreat  = $db->sql_query($SQLCompanTreat);
                $rowCompanTreat     = $db->sql_fetchrow($resultCompanTreat);

                if ($rowCompanTreat['amount'] != ''){
                    $fees = $rowCompanTreat['amount'];
                } else {
                    $sqlTreatmentVisitRec = "
                    SELECT *
                    FROM treatment_visit
                    WHERE treatment_id = {$rowTreatment['treatment_id']}
                      AND patient_visit_id = {$patient_visit_id}
                    LIMIT 0, 1
                    ";
                    $resultTreatmentVisitRec = $db->sql_query($sqlTreatmentVisitRec);
                    $treatmentVisitRec = $db->sql_fetchrow($resultTreatmentVisitRec);
                    $fees = $treatmentVisitRec['fees'];
                }
            }else if($patientinfoRec['bill_type'] == 'Individual'){
                $treatmentRec = $fn->getRecordByConditionForHistoryTable('treatment', "treatment_id = {$rowTreatment['treatment_id']}");
                if($treatmentRec['fees'] != ''){
                    $fees = $treatmentRec['fees'];
                } else {
                    $treatmentVisitRec = $fn->getRecordByConditionForHistoryTable('treatment_visit', "treatment_id = {$rowTreatment['treatment_id']} AND patient_visit_id = {$patient_visit_id}");
                    $fees = $treatmentVisitRec['fees'];
                }
            }
            /* Displaying Price for tests under Lab test tab - END */

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

            $inputRow .= "
            <div class='c33l'>
                <div class='type-check treatmentBox'>
                    <input id='treatment_id_{$rowTreatment['treatment_id']}' {$checked} class='treatment_id' type='checkbox' name='treatmentId[]' value='{$rowTreatment['treatment_id']}_{$count}'>
                    <label for='treatment_id_{$rowTreatment['treatment_id']}'>{$rowTreatment['title']}</label>
                    <div class='hideTreatmentDetails_{$rowTreatment['treatment_id']}_{$count} {$class} treatmentNotes mt20'>
                        <input type='text' value='{$fees}' id='fld_fees' class='text mb20' name='fees[]'>
                        <input class='button treatmentStatus mb10' name='treatment_status[]' value='{$status}'/>
                        <div class='{$classDate} followUpDate'>
                            {$formObj->getDropDownRowByArray('', 'future_value_'.$rowTreatment['treatment_id'], $followUp, $future_value, $expArr)}
                            {$formObj->getDateRow('', 'future_date_'.$rowTreatment['treatment_id'], $future_date)}
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
            $count ++;
        }

        $SQLOrder = "
        SELECT order_id
        FROM `order`
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultOrder  = $db->sql_query($SQLOrder);
        $numRowsOrder = $db->sql_numrows($resultOrder);

        /* Block Treatment portal when Order (Invoice) is raised */
        $disableLabDiv = "";
        if($VisitRow['order_id'] != ''){
        //if($numRowsInvoice > 0){
            $disableLabDiv = "disabledDiv";
        }

        if($numRowsOrder > 0){
            $rowOrder     = $db->sql_fetchrow($resultOrder);
            $SQLInvoice = "
            SELECT i.*
            FROM invoice i
            WHERE i.order_id = {$rowOrder['order_id']}
            AND i.status != 'Cancelled'
            ";
            $resultInvoice = $db->sql_query($SQLInvoice);
            $numRowsInvoice = $db->sql_numrows($resultInvoice);
        }

        $text = "
        <div id='' class='treatmentDisplay {$disableLabDiv}'>
            <form id='portalForm_treatmentDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='floatbox'>{$inputRow}</div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
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

        $formAction = "index.php?module=labsg_patientVisit&_spAction=addNoteTreatmentSubmit&showHTML=0";
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
        {$media->getRightPanelMediaDisplay('Attachments', 'labsg_patientVisit', 'attachment', $row)}
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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $invoiced   = $fn->getReqParam('invoiced');

        $spArray = array(
            ""
           ,"Yes"
           ,"No"
        );

        $text = "
        <td class='dateRange'>
            From Date:
            <input type='text' allowEdit='1' name='start_date' class='fld_date'
            id='fld_start_date' value='{$start_date}' />
            To Date:
            <input type='text' allowEdit='1' name='end_date' class='fld_date'
            id='fld_end_date' value='{$end_date}' />
        </td>
        <td>
            <select name='invoiced'>
                <option value=''>Invoiced</option
                {$cpUtil->getDropDown1($spArray, $invoiced)}
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

        $formAction = "index.php?_topRm=main&module=labsg_patientVisit&_spAction=createVisitRecordSubmit&showHTML=0";

        $sqlEmployee = "
        SELECT employee_id, employee_name FROM employee
        WHERE category = 'Doctor'
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
    function getPrintLabel1() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $patient_visit_id = 1;

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);


        $prms = array('unique' => true);
        $TBS->Plugin(OPENTBS_CHANGE_PICTURE, '#main_map#', '/admin/images/finance.png', $prms);


        $template = 'label.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $file_name = $patient_visit_id . '.xlsx';
        //$file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        //if ($cpCfg['local']['site'] == 'local') {
            $path = realpath($cpCfg['cp.mediaFolder']) . '\temp\label';
            $file_name_save = $path . '\\' . $file_name;
        /*} else {
            $path = realpath($cpCfg['cp.mediaFolder']) . '/temp/label';
            $file_name_save = $path . '//' . $file_name;
        }*/
        $sourceFilePath = $file_name_save;

        $SQL = "
        SELECT pv.*
              ,p.name
              ,p.nric
              ,p.email
              ,p.mobile
              ,p.dob
              ,p.bill_type
        FROM patient_visit pv
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);

        $today = date("Y-m-d");

        global $TeamList;
        $TeamList    = array();

        $arr            = array();
        $blkMain        = array();

        $count     = 1;

        for ($i = 1; $i <= 3; $i++) {
            $TeamList[$count+$i] = array(
                'company_name' => 'New Medical',
                'reg_no'       => 'Reg No.: 11111',
                'address1'     => 'Address 1',
                'image'        => 'E:/Projects/newmedlab/httpdocs/admin/images/finance.png',
                'invoice_code' => 'Bill No.' . '12345',
                'date'         => 'Date:' . $fn->getCPDate($today, 'd-m-Y'),
                'name'         => 'Name',
                'age'          => 'Age',
                'col3'          => 'Col3',
                'test'         => 'Test',
                'row_name'     => $row['name'],
                'row_nric'     => $row['nric'],
                'empty_space'  => '',
                );
        }

        $blkMain[]            = $arr;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('mb', $TeamList);
        $TBS->MergeBlock('mb','array','TeamList');

        $TBS->Show(OPENTBS_FILE, $sourceFilePath);
        //$TBS->Show(OPENTBS_DOWNLOAD, $file_name);
        echo "<script>window.close();</script>";

    }
    /**
     *
     */
    function getPrintLabelOLD() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

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

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $patient_visit_id = 1;

        $SQL = "
        SELECT pv.*
              ,p.name
              ,p.nric
              ,p.email
              ,p.mobile
              ,p.dob
              ,p.bill_type
        FROM patient_visit pv
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $result = $db->sql_query($SQL);

        $today = date("d-m-Y");

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold">DELIVERY ORDER</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        //$pdf->writeHTML($tbl2, true, false, false, false, '');
        //$pdf->writeHTML($tbl3, true, false, false, false, '');
        //$pdf->writeHTML($tbl5, true, false, false, false, '');
        //$pdf->writeHTML($tbl6, true, false, false, false, '');
        $pdf->Output('Delivery-Order.pdf', 'I');
    }

    function getPrintLabel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/tcpdf.php');
        //include_once(CP_LOCAL_PATH.'lib/headfoot1.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        //$pdf = new MYPDF2();
        //create new PDF document
        //$pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf = new MYPDF_Local('L', 'px', array('302.362', '151.181'), true, 'UTF-8', false);
        $pdf = new MYPDF_Local('L', 'px', array('261.250', '110.48'), true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Universal Software Solutions');
        $pdf->SetSubject('Print Label');
        $pdf->SetTitle('Print Label');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        //$pdf->SetMargins(3, 3, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(32, 0, PDF_MARGIN_RIGHT); //Left, Top, Right
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,0);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------QUERY START
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $case_note = $fn->getReqParam('case_note');
        $lab_note = $fn->getReqParam('lab_note');
        $dob = $fn->getReqParam('dob');
        $diff = '';

        $SQL = "
        SELECT pv.*
              ,p.name
              ,p.registration_no
              ,p.email
              ,p.mobile
              ,p.dob
              ,p.bill_type
              ,p.gender
              ,p.nationality
              ,p.company_id
              ,p.occupation
        FROM patient_visit pv
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);
        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/

        $pdf->SetFont('Calibri','',9);
        $pdf->AddPage();

        for($i=1;$i<=$case_note;$i++){

            if (!empty($row['dob'])) {
                $birthdate = new DateTime($row['dob']);
                $today     = new DateTime('today');
                $age = $birthdate->diff($today)->y;
            } else {
                $age = 0;
            }

            $dateofbirth   = $fn->getCPDate($row['dob'], 'd-M-Y');
            $check_up_date = $fn->getCPDate($row['check_up_date'], 'd-M-Y');

            $happyText = "";
            if($row['bill_type'] == "Company" || $row['bill_type'] == "Panel"){
                $sqlCompany = "
                SELECT company_name FROM company
                WHERE company_id = {$row['company_id']}
                ";
                $resultCompany = $db->sql_query($sqlCompany);
                $rowCompany    = $db->sql_fetchrow($resultCompany);
                $happyText = $rowCompany['company_name'];
            }

            $remarks = '';
            if ($row['notes']) {
                $remarks = substr($row['notes'], 0, 40);
                $stringCount = strlen($row['notes']);

                if ($stringCount > 40) {
                    $remarks = strtoupper($remarks) . '...';
                } else {
                    $remarks = strtoupper($remarks);
                }
            }

            $tbl1 ='
            <br/><br/>
            <table border="0" nobr="true" width="100%" cellpadding="0">
                <tr>
                    <td width="40%" height="8px">MEDIWAY MEDICAL</td>
                    <td width="30%" height="8px">'.$check_up_date.'</td>
                    <td width="30%" height="8px">'.$row['check_up_time'].'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="1" height="2px">'.strtoupper($row['name']).'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.$row['registration_no'].'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.$row['gender'].' / '.$dateofbirth. ' / ' . $age . ' year(s) old</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.$row['nationality'].'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.strtoupper($row['occupation']).'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.strtoupper($happyText).'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.$remarks.'</td>
                </tr>
            </table>
            ';

            $pdf->writeHTML($tbl1, true, false, false, false, '');
            $pdf->Ln();
        }

        for($j=1;$j<=$lab_note;$j++){
            $pdf->AddPage();
            if (!empty($row['dob'])) {
                $birthdate = new DateTime($row['dob']);
                $today     = new DateTime('today');
                $age       = $birthdate->diff($today)->y;
            } else {
                $age = 0;
            }

            $dateofbirth   = $fn->getCPDate($row['dob'], 'd-M-Y');
            $check_up_date = $fn->getCPDate($row['check_up_date'], 'd-M-Y');

            $tbl1 ='
            <br/><br/>
            <table border="0" nobr="true" width="100%" cellpadding="1">
                <tr>
                    <td width="40%" height="8px">MEDIWAY MEDICAL</td>
                    <td width="30%" height="8px" align="center">'.$check_up_date.'</td>
                    <td width="30%" height="8px">'.$row['check_up_time'].'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.strtoupper($row['name']).'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.$row['registration_no'].'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.$row['gender'].' / '.$dateofbirth. ' / ' . $age . ' year(s) old</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.$row['nationality'].'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.strtoupper($row['occupation']).'</td>
                </tr>
                <tr>
                    <td width="100%" colspan="3" height="2px">'.$remarks.'</td>
                </tr>
            </table>
            ';

            $pdf->writeHTML($tbl1, true, false, false, false, '');
            $pdf->Ln();
        }

        $pdf->IncludeJS("print();");
        $pdf->Output('Label.pdf', 'I');
    }

    /**
     */
     function getAddPatientRecord() {
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_topRm=main&module=labsg_patientVisit&_spAction=addPatientRecordSubmit&showHTML=0";
        
        $expVl = array('sqlType' => 'OneField');
        $expHideFO = array('sqlType' => 'OneField','hideFirstOption' => true);
        
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $sqlBillType      = $fn->getValueListSQL('billType', 'value ASC', array('globalForAllSites' => true));
        $sqlGender        = $fn->getValueListSQL('gender', 'value ASC', array('globalForAllSites' => true));
        $sqlNationality   = $fn->getValueListSQL('nationality', 'value ASC', array('globalForAllSites' => true));
        $sqlOccupation    = $fn->getValueListSQL('occupation', 'value ASC', array('globalForAllSites' => true));
        $sqlCountry       = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $formAddOccupation = "index.php?module=labsg_patientVisit&_spAction=addNewValuelistForm&valuelist_name=occupation&showHTML=0";
        $expOccupation = array('sqlType' => 'OneField','hideFirstOption' => true
                            ,'notesRight' => "<a href='{$formAddOccupation}' class='mr20 addNewValue' valuelist_name='occupation'>Add</a>");
        $row = '';

        $billType = $fn->getReqParam('bill_type');

        $sqlCompany = "
        SELECT company_id
              ,company_name
        FROM company
        ORDER BY company_name
        ";

        /* Last company Name Auto show - START */
        $sqlPI = "
        SELECT pai.company_id
        FROM patient_information pai
        LEFT JOIN (patient_visit pv) ON (pai.patient_information_id = pv.patient_information_id)
        ORDER BY pv.patient_visit_id DESC LIMIT 0,1
        ";
        $resultPI = $db->sql_query($sqlPI);
        $numRowsPI = $db->sql_numrows($resultPI);
        $rowPI = $db->sql_fetchrow($resultPI);
        $company_id = '';
        $billTypeVal = "Individual";        
        $companyDetailsHide = 'companyDetailsHide';

        if ($numRowsPI && $rowPI['company_id'] != '') {
            $billTypeVal = "Company";
            $company_id = $rowPI['company_id'];
            $companyDetailsHide = '';
        }
        /* Last company Name Auto show - END */

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
                    <td>{$formObj->getTBRow('Name*', 'name')}</td>
                    <td>{$formObj->getDDRowBySQL('Nationality*', 'nationality', $sqlNationality, '', $expVl)}</td>
                    <td>
                        {$formObj->getDateRow('DOB (YYYY-MM-DD)*', 'dob', '', array('yearStart' => 1950, 'yearEnd' => date('Y') + 10))}
                    </td>
                    <td>{$formObj->getDDRowBySQL('Gender*', 'gender', $sqlGender, '', $expVl)}</td>
               </tr>
                <tr company_id={$company_id}>
                    <td>{$formObj->getTBRow('Passport / ID*', 'registration_no')}</td>
                    <td>{$formObj->getTBRow('Type of pass', 'pass_type')}</td>
                    <td>{$formObj->getDDRowBySQL('Bill Type*', 'bill_type', $sqlBillType, $billTypeVal, $expVl)}</td>
                    <td class='companyDetailsTr {$companyDetailsHide}'>{$formObj->getDDRowBySQL('Company', 'company_id', $sqlCompany, $company_id)}</td>
                </tr>
                <tr>
                    <td>{$formObj->getDDRowBySQL('Occupation', 'occupation', $sqlOccupation, '', $expOccupation)}</td>
                    <td>{$formObj->getDateRow('First Visit On (YYYY-MM-DD)', 'first_admit', date('Y-m-d'))}</td>
                    <td colspan='2'>{$formObj->getTARow('Remarks', 'notes', '')}</td>
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

        $company_category = $fn->getReqParam('company_category');

        $json  = array();

        $SQL = "
        SELECT company_id
              ,company_name
        FROM company
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
    function getPrintLabelPatientVisitForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_topRm=main&module=labsg_patientVisit&_spAction=printLabelPatientVisitFormSubmit&showHTML=0";
        $text = "
        <form id='PrintLabelPatientVisitForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getTBRow('Case note', 'case_note', '2')}
                {$formObj->getTBRow('Lab note', 'lab_note', '2')}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name = $fn->getReqParam('valuelist_name');

        $formAction = "index.php?module=labsg_patientVisit&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalFormValuelist' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getCancelPatientVisitForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $rows = '';
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $formAction = "index.php?_topRm=main&module=labsg_patientVisit&_spAction=cancelPatientVisitFormSubmit&showHTML=0";
        $text = "
        <form id='portalForm' class='yform columnar cancelPatientVisitForm' method='post' action='{$formAction}'>
            <div><strong>Please Note that all finance records will also get cancelled if any.</strong></div>
            {$formObj->getTARow('Notes', 'cancelling_notes', '')}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;
    }
}