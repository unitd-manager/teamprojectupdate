<?
class CP_Admin_Modules_Labsg_PatientInformation_View extends CP_Common_Lib_ModuleViewAbstract
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

        foreach ($dataArray as $row){
            $email     = $row['email'];
            //$website   = $row['website'];

            $patient_code = '';
            if($row['patient_code'] != ''){
                $patient_code = 'PT-'.$row['patient_code'];
            }

            /** Please uncommand the code and refresh the patient information list page it will be generated **/
            /**    $patient_code = $fn->getSettingsValueByKey("nextPatientCode");

                $SQLP  = "
                SELECT patient_code
                       ,patient_information_id
                FROM patient_information
                WHERE patient_code = ''
                ";
                $resultp = $db->sql_query($SQLP);
                while($rowp = $db->sql_fetchrow($resultp)){


                    $SQLUpdatep = "UPDATE patient_information SET patient_code ={$patient_code} WHERE patient_information_id = {$rowp['patient_information_id']}";
                    $resultUpdatep = $db->sql_query($SQLUpdatep);
                    //To update patient code
                    $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientCode'";
                    $resultUpdate = $db->sql_query($SQLUpdate);

                }   */

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

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($patient_code)}
            {$listObj->getListDataCell($row['name'])}
            {$listObj->getListDataCell($row['nationality'])}
            {$listObj->getListDateCell($row['dob'])}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($row['registration_no'])}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListDataCell($row['bill_type'])}
            {$listObj->getListRowEnd($row['patient_information_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'p.patient_code')}
        {$listObj->getListHeaderCell('Name', 'p.name')}
        {$listObj->getListHeaderCell('Nationality', 'p.nationality')}
        {$listObj->getListHeaderCell('DOB', 'p.dob')}
        {$listObj->getListHeaderCell('Gender', 'p.gender')}
        {$listObj->getListHeaderCell('Passport / ID', 'p.registration_no')}
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

        $fielset1 = "
        {$formObj->getTBRow('Name*', 'name')}
        {$formObj->getTBRow('Passport / ID*', 'registration_no')}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

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
        $sqlComp = $fn->getDDSql('labsg_company');
        $expComp  = array('detailValue' => $row['company_name']);

        $expVl          = array('sqlType' => 'OneField');
        $expOccupation  = array('sqlType' => 'OneField','hideFirstOption' => true);
        $expNoEdit      = array('isEditable' => 0);
        $sqlGender      = $fn->getValueListSQL('gender');
        $sqlRace        = $fn->getValueListSQL('race');
        $sqlBillType    = $fn->getValueListSQL('billType');
        $sqlCategory    = $fn->getValueListSQL('patientInformationCategory');
        $sqlTitle       = $fn->getValueListSQL('patientInformationTitle');
        $sqlNationality = $fn->getValueListSQL('nationality');
        $sqlOccupation  = $fn->getValueListSQL('occupation');

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
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            $SQLEV = "
            SELECT Distinct ev.employee_id
                  ,e.employee_name
                  ,ev.patient_visit_id
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE ev.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY e.employee_name
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

            //$orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$rowPv['patient_visit_id']}'");
            $total_invoice_amount = '0.00';
            $invoiced_Paid_Amount = '0.00';
            $balance_Amount = '0.00';

            if($rowPv['order_id'] != ''){
                $subSqlForPercentSum = "
                SELECT o.*
                      ,(SELECT SUM(invHist.amount) AS prev_sum
                        FROM invoice_receipt_history invHist
                        LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                        LEFT JOIN `invoice` i ON (i.order_id = {$rowPv['order_id']})
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
                WHERE o.order_id = {$rowPv['order_id']}
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
            $visit_code = "<a href='index.php?_topRm=main&module=labsg_patientVisit&record_id={$rowPv['patient_visit_id']}&_action=edit'>{$rowPv['visit_code']}</a>";

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

        $expanded = ($tv['newRecord'] == 1) ? 1 : 0;

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
        <div class='linkPortalWrapper'>
            <div expanded='{$expanded}' class='header'>
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
                                <td>{$formObj->getTBRow('Name*', 'name', $row['name'])}</td>
                                <td>{$formObj->getDDRowBySQL('Nationality*', 'nationality', $sqlNationality, $row['nationality'], $expVl)}</td>
                                <td>{$formObj->getDateRow('DOB*', 'dob', $row['dob'])}</td>
                                <td>{$formObj->getDDRowBySQL('Gender*', 'gender', $sqlGender, $row['gender'], $expVl)}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Passport / ID*', 'registration_no', $row['registration_no'])}</td>
                                <td>{$formObj->getTBRow('Type of pass', 'pass_type', $row['pass_type'])}</td>
                                <td>{$formObj->getDDRowBySQL('Bill Type*', 'bill_type', $sqlBillType, $row['bill_type'], $expVl)}</td>
                                <td colspan='2'>{$formObj->getDDRowBySQL('Occupation', 'occupation', $sqlOccupation, $row['occupation'], $expOccupation)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}</td>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                                <td>{$formObj->getDateRow('First Visit On', 'first_admit', $row['first_admit'])}</td>
                            </tr>
                            
                            <tr>
                                <th colspan='5'>Company Details</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlComp, $row['company_id'], $expComp)}</td>
                                <td>{$formObj->getTBRow('Main Phone', 'company_phone', $row['c_phone'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Address Flat', 'company_address_flat', $row['c_address_flat'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Address Street', 'company_address_street', $row['c_address_street'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Address Area', 'company_address_town', $row['c_address_town'], $expNoEdit)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Address State', 'company_address_state', $row['c_address_state'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Address Country', 'company_address_country', $row['c_address_country'], $expNoEdit)}</td>
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

                            <tr>
                                <th colspan='5'>Family Details</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Father Name', 'father_name', $row['father_name'])}</td>
                                <td>{$formObj->getTBRow('Mother Name', 'mother_name', $row['mother_name'])}</td>
                                <td>{$formObj->getTBRow('Spouse Name', 'spuse_name', $row['spuse_name'])}</td>
                                <td>{$formObj->getTBRow('Primary Contact', 'primary_contact', $row['primary_contact'])}</td>
                                <td>{$formObj->getTBRow('Alergies', 'alergies', $row['alergies'])}</td>
                            </tr>

                            <tr>
                                <td class='notesTitle'>{$formObj->getTARow('Notes ', 'notes', $row['notes'])}</td>
                            </tr>

                            <tr>
                                <td class= 'creationModificationText' colspan = '5'>{$formObj->getCreationModificationText($row)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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


        $record_id = $fn->getIssetParam($row, 'patient_information_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'labsg_patientInformation', 'attachment', $row)}
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

        $name       = $fn->getReqParam('name');
        $sqlName    = $fn->getDDSql('name');
        $billType   = $fn->getReqParam('bill_type');
        $company_id = $fn->getReqParam('company_id');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );
        $sqlBillType = $fn->getValueListSQL('billType');
        $sqlComp     = $fn->getDDSql('labsg_company');

        $companyFilter = '';
        if($billType == 'Company'){
            $companyFilter ="
            <td>
                <select name='company_id'>
                    <option value=''>Company Name</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlComp, $company_id)}
               </select>
            </td>
            ";
        }else{
            $company_id = '';
        }

        $text = "
        <td>
            <select name='bill_type'>
                <option value=''>Bill Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlBillType, $billType)}
           </select>
        </td>
        {$companyFilter}
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }


    /**
     *
     */


}