<?
class CP_Admin_Modules_Hms_PatientInformation_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('chosen-1.5.1');
    /**
     *
     */
    function getList($dataArray){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';
        //used to generate codes from 1000.
        //$this->getUpdateCode();

        foreach ($dataArray as $row){
            $email     = $row['email'];
            //$website   = $row['website'];
            //$dob = $fn->getCPDate($row['dob'],'d-m-Y');
            //$dob = $fn->getCPDate($row['dob'], 'Y');

            $age = '';
            if ($row['dob']) {
                $dob_for_age = $dateUtil->formatDate($row['dob'], 'DD-MM-YYYY');
                $age = $this->getFindage($dob_for_age, date('d-m-Y'));
            }

            $patient_code = '';
            if($row['patient_code'] != ''){
                $patient_code = 'PT-'.$row['patient_code'];
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($patient_code)}
            {$listObj->getListDataCell($row['patient_name'])}
            {$listObj->getListDataCell($age)}
            {$listObj->getListDataCell($row['nric'])}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListDataCell($row['bill_type'])}
            {$listObj->getListRowEnd($row['patient_information_id'])}
            ";

            $count++ ;
        }

        //return "Database error occured.";

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'p.patient_code')}
        {$listObj->getListHeaderCell('Name', 'p.name')}
        {$listObj->getListHeaderCell('Age', 'p.dob')}
        {$listObj->getListHeaderCell('NRIC', 'p.nric')}
        {$listObj->getListHeaderCell('Email', 'p.email' )}
        {$listObj->getListHeaderCell('Mobile', 'p.mobile')}
        {$listObj->getListHeaderCell('Bill Type', 'p.bill_type')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $expVl = array('sqlType' => 'OneField');
        $sqlBillType    = $fn->getValueListSQL('billType');

        $fielset1 = "
        {$formObj->getTBRow('First Name*', 'first_name')}
        {$formObj->getTBRow('Middle Name', 'middle_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('NRIC *', 'nric')}
        {$formObj->getDDRowBySQL('Bill Type *', 'bill_type', $sqlBillType, 'Individual', $expVl)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
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

            $patient_code = '';
            if($row['patient_code'] != ''){
                $patient_code = 'PT-'.$row['patient_code'];
            }

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['address_country']);
        $sqlComp = $fn->getDDSql('hms_company');
        $expComp  = array('detailValue' => $row['company_name']);

        $expVl = array('sqlType' => 'OneField');
        $sqlGender      = $fn->getValueListSQL('gender');
        $sqlRace        = $fn->getValueListSQL('race');
        $sqlBillType    = $fn->getValueListSQL('billType');

        $sqlCategory = $fn->getValueListSQL('patientInformationCategory');
        $sqlTitle    = $fn->getValueListSQL('patientInformationTitle');
        $expNoEdit = array('isEditable' => 0);

        $patientVisitRec = $fn->getRecordByCondition('patient_visit', "patient_information_id = '{$row['patient_information_id']}'");
        $patientVisitCount = $fn->getRecordCount('patient_visit', "patient_information_id = {$row['patient_information_id']}");

        $SQLPatientVisit = "
        SELECT pv.*
        FROM patient_visit pv
        WHERE pv.patient_information_id = '{$row['patient_information_id']}'
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        $employee_id_count = '';
        $treatment = '';
        $treatmentTitle = '';
        $employeeTitle = '';
        $PvText = '';
        $empNameArr = array();
        $empNameArr1 = array();
        $treatmentArr = array();
        $treatmentArr1 = array();
        $balance_Amount = '0.00';
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
                $empNameArr[] = $rowEv['employee_name'];
                if(!in_array($rowEv['employee_name'], $empNameArr1)){
                    $empNameArr1[] = $rowEv['employee_name'];
                }

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
                $treatmentArr[] = $rowTv['title'];
                if(!in_array($rowTv['title'], $treatmentArr1)){
                    $treatmentArr1[] = $rowTv['title'];
                }

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
            $visit_code = "<a href='index.php?_topRm=main&module=hms_patientVisit&record_id={$rowPv['patient_visit_id']}&_action=edit'>{$rowPv['visit_code']}</a>";

            $PvText .= "
            <tr>
                <td class=''>{$visit_code}</td>
                <td class=''>{$check_up_date}</td>
                <td class=''>{$dr_attended}</td>
                <td class=''>{$pv_treatment}</td>
                <td class=''>{$total_invoice_amount}</td>
                <td class=''>{$invoiced_Paid_Amount}</td>
                <td class=''>{$balance_Amount}</td>
            </tr>
            ";
        }
        foreach($empNameArr1 as $value){
            $employee_name = $value;
            $counts = array_count_values($empNameArr);
            $employee_count =  $counts[$value];

            $employee_id_count .= $employee_name.' ('.$employee_count.')<br>';
        }
        foreach($treatmentArr1 as $value){
            $counts1 = array_count_values($treatmentArr);
            $treatment_count =  $counts1[$value];

            $treatment .= $value.' ('.$treatment_count.')<br>';
        }

        $companyDetailsHide = '';
        if($row['bill_type'] == '' || $row['bill_type'] == 'Individual'){
            $companyDetailsHide = 'companyDetailsHide';
            $sqlCompany = '';
        }

        $companyDetailsLabel = 'Company Details';
        $categoryDDRLabel = 'Client Name';
        if($row['bill_type'] == 'Company'){
            $categoryForDDR = 'Client';
        }elseif ($row['bill_type'] == 'Panel') {
            $categoryForDDR = $row['bill_type'];
            $companyDetailsLabel = 'Panel Details';
            $categoryDDRLabel = 'Panel Name';
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

        $sqlPrimarycontact = "
        SELECT CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
        FROM patient_relationinfo pr
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pr.patient_information_source_id)
        WHERE pr.patient_information_id = '{$row['patient_information_id']}'
        ORDER BY patient_name
        ";

        if($row['c_address_country'] != ''){
            $SQLCountryName = "
            SELECT c.*
                  ,gc.name AS c_address_country
            FROM company c
            LEFT JOIN geo_country gc ON (gc.country_code = c.address_country)
            WHERE company_id = {$row['company_id']}
            ";
            $resultCountryName = $db->sql_query($SQLCountryName);
            $rowCountryName    = $db->sql_fetchrow($resultCountryName);
            $row['c_address_country'] = $rowCountryName['c_address_country'];
        }

        $primary_contact  = $fn->getReqParam('primary_contact');
        $primarycontactArray = array(
              "Father"
             ,"Mother"
             ,"Spouse"
             ,"Other"
        );

        $PrimaryContactHide = '';
        if($row['primary_contact'] != 'Other'){
            $PrimaryContactHide = 'PrimaryContactHide';
        }

        //$expanded = ($tv['newRecord'] == 1) ? 1 : 0;

        $text = "
        <table class='thinlist mb20 visitSummary'>
            <tr>
                <th class='label'>TOTAL VISITS</td>
                <th class='label'>PRIMARY DOCTORS</td>
                <th class='label'>TREATMENTS TAKEN</td>
            </tr>
            <tr>
                <td class=''>{$patientVisitCount}</td>
                <td class=''>{$employee_id_count}</td>
                <td class=''>{$treatment}</td>
            </tr>
        </table>

        <div class='floatbox'>
            <div class='float_left'>Created By : {$row['created_by']}  {$row['creation_date']} </div>
            <div class='float_right'>Modified By: {$row['modified_by']} {$row['modification_date']}</div>
        </div>

        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Patient Information Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <th colspan='5'>Main Details</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Code', 'patient_code', $patient_code,  $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('First Name*', 'first_name', $row['first_name'])}</td>
                                <td>{$formObj->getTBRow('Middle Name', 'middle_name', $row['middle_name'])}</td>
                                <td>{$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}</td>
                                <td>
                                    {$formObj->getDateRow('DOB (YYYY-MM-DD)', 'dob', $row['dob'], array('yearStart' => 1950, 'yearEnd' => date('Y') + 10))}
                                </td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                                <td>{$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVl)}</td>
                                <td>{$formObj->getTBRow('Nric *', 'nric', $row['nric'])}</td>
                                <td>{$formObj->getTBRow('Passport No', 'registration_no', $row['registration_no'])}</td>
                                <td>{$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVl)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Bill Type *', 'bill_type', $sqlBillType, $row['bill_type'], $expVl)}</td>
                                <td>{$formObj->getTBRow('Phone', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}</td>
                                <td>{$formObj->getDateRow('First Visit On (YYYY-MM-DD)', 'first_admit', $row['first_admit'])}</td>
                            </tr>
                            <tr class='companyDetailsTr {$companyDetailsHide}'>
                                <th colspan='5'>{$companyDetailsLabel}</th>
                            </tr>

                            <tr class='companyDetailsTr {$companyDetailsHide}'>
                                <td>{$formObj->getDDRowBySQL($categoryDDRLabel, 'company_id', $sqlCompany, $row['company_id'], $expComp)}</td>
                                <td>{$formObj->getTBRow('Main Phone', 'company_phone', $row['c_phone'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Address Flat', 'company_address_flat', $row['c_address_flat'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Address Street', 'company_address_street', $row['c_address_street'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Address Area', 'company_address_town', $row['c_address_town'], $expNoEdit)}</td>
                            </tr>
                            <tr class='companyDetailsTr {$companyDetailsHide}'>
                                <td>{$formObj->getTBRow('Address State', 'company_address_state', $row['c_address_state'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Address Country', 'company_address_country', $row['c_address_country'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Panel Worker ID', 'worker_id', $row['worker_id'])}</td>
                                <td>{$formObj->getTBRow('Serial No of Book', 'serial_no_of_book', $row['serial_no_of_book'])}</td>
                                <td>{$formObj->getTBRow('Department', 'department', $row['department'])}</td>
                            </tr>

                            <tr>
                                <th colspan='5'>Family Details</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getDDRowBySQL('Primary Contact', 'primary_contact', $sqlPrimarycontact, $row['primary_contact'], $expVl)}</td>
                                <td class='primaryContactTr {$PrimaryContactHide}'>{$formObj->getTBRow('', 'patient_information_id', '')}</td>
                                <td>{$formObj->getTBRow('Relationship', 'relationship', $row['relationship'])}</td>
                                <td>{$formObj->getTBRow('Alergies', 'alergies', $row['alergies'])}</td>
                                <td class='notesTitle'>{$formObj->getTARow('Notes ', 'notes', $row['notes'])}</td>
                            </tr>

                            <tr>
                                <th colspan='5'>Address Details</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address Street', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('Address Area', 'address_area', $row['address_area'])}</td>
                                <td>{$formObj->getTBRow('Address City', 'address_city', $row['address_city'])}</td>
                                <td>{$formObj->getTBRow('Address Code', 'address_code', $row['address_code'])}</td>
                                <td>{$formObj->getDDRowBySQL('Address Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Patient Visit History</div>
                    <div class='float_left InvoiceToggleHeading'>- Overall Due : {$balance_Amount}</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist mb20 visitSummary'>
                        <tr>
                            <th class='label'>Visit Code</td>
                            <th class='label'>Date</td>
                            <th class='label'>Dr Attended</td>
                            <th class='label'>Treatment</td>
                            <th class='label'>Total Amount</td>
                            <th class='label'>Paid</td>
                            <th class='label'>Balance</td>
                        </tr>
                        {$PvText}
                    </table>
                    <input type='hidden' name='patient_information_id' value={$row['patient_information_id']}>
                </div>
            </div>
        </div>
        ";

        /*<tr>
            <td class= 'creationModificationText' colspan = '5'>{$formObj->getCreationModificationText($row)}</td>
        </tr>*/

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


        $record_id = $fn->getIssetParam($row, 'patient_information_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_patientInformation', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain("hms_patientInformation", "hms_patientInformationLink", "Relations Linked", $row)}
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

        $name   = $fn->getReqParam('name');
        $sqlName   = $fn->getDDSql('name');
        $billType   = $fn->getReqParam('bill_type');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );
        $sqlBillType    = $fn->getValueListSQL('billType');

        $text = "
        <td>
            <select name='bill_type'>
                <option value=''>Bill Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlBillType, $billType)}
           </select>
        </td>

        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
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
    /*function getUpdateCode(){
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT patient_information_id
        FROM patient_information
        ";
        $result = $db->sql_query($SQL);
        $count = 1000;

        while ($row = $db->sql_fetchrow($result)) {

            $SQlUpdate="
            UPDATE patient_information set patient_code = {$count}
            WHERE patient_information_id = {$row['patient_information_id']}
            ";
            $resultUpdate = $db->sql_query($SQlUpdate);

            $count++;
        }
    }*/
    /**
     *
     */
    /**
     *
     */
    function getFindage($date_of_birth, $today) {

        /* Reference link
        https://stackoverflow.com/questions/10410877/how-can-i-calculate-the-age-of-a-person-in-year-month-days-in-php
        */
        $dob_a = explode("-", $date_of_birth);
        $today_a = explode("-", $today);
        $dob_d = $dob_a[0];$dob_m = $dob_a[1];$dob_y = $dob_a[2];
        $today_d = $today_a[0];$today_m = $today_a[1];$today_y = $today_a[2];
        $years = $today_y - $dob_y;
        $months = $today_m - $dob_m;
        if ($today_m.$today_d < $dob_m.$dob_d) {
            $years--;
            $months = 12 + $today_m - $dob_m;
        }

        if ($today_d < $dob_d) {
            $months--;
        }

        $firstMonths=array(1,3,5,7,8,10,12);
        $secondMonths=array(4,6,9,11);
        $thirdMonths=array(2);

        if($today_m - $dob_m == 1) {
            if(in_array($dob_m, $firstMonths)) {
                array_push($firstMonths, 0);
            } else if(in_array($dob_m, $secondMonths)) {
                array_push($secondMonths, 0);
            } else if(in_array($dob_m, $thirdMonths)) {
                array_push($thirdMonths, 0);
            }
        }

        $age = $years;

        return $age;
    }
}