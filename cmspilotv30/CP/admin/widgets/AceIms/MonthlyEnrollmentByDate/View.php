<?
class CP_Admin_Widgets_AceIms_MonthlyEnrollmentByDate_View extends CP_Common_Lib_WidgetViewAbstract
{
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Data_Accuracy_Date</th>
                        <th>PEIs_UEN_No</th>
                        <th>PEI_Name</th>
                        <th>Bank_Insurer_Type</th>
                        <th>FPS_Provider</th>
                        <th>PEI_Facility_Account_No</th>
                        <th>Facility_Valid_Till_Date</th>
                        <th>Protection_Scheme_Type</th>
                        <th>STP_Requirement</th>
                        <th>Student_ID_Given_By_PEI</th>
                        <th>NRIC_FIN_No</th>
                        <th>Student_Name</th>
                        <th>Date_Of_Birth</th>
                        <th>Gender</th>
                        <th>Singapore_Citizen_PR_Indicator</th>
                        <th>Nationality</th>
                        <th>STP_Application_Date</th>
                        <th>STP_IPA_Date</th>
                        <th>STP_Issue_Date</th>
                        <th>STP_Expiry_Date</th>
                        <th>STP_Cancellation_Date</th>
                        <th>Full_Part_Time_Indicator</th>
                        <th>Course_ID</th>
                        <th>Course_Title</th>
                        <th>Course_Start_Date</th>
                        <th>Course_End_Date</th>
                        <th>Course_Durations_Not_More_Than_50_Hours_Or_30_Days_Declaration</th>
                        <th>Course_Durations_Month</th>
                        <th>Fee_Protection_Status</th>
                        <th>Total_Course_Fee_Stated_In_Student_Contract</th>
                        <th>Total_Course_Fee_To_Be_Protected</th>
                        <th>Fee_Collection_Cap</th>
                        <th>Course_Termination_Date</th>
                        <th>Date_That_Refund_Instruction_Sent_To_Bank_By_PEI</th>
                        <th>Course_Fee_Amount_Refunded</th>
                        <th>Instalment_ID</th>
                        <th>Scheduled_Payment_Amount</th>
                        <th>Scheduled_Payment_Due_Date</th>
                        <th>Escrow_Unique_No_Or_Insurance_Policy_No</th>
                        <th>Protection_Start_Date</th>
                        <th>Protection_End_Date</th>
                        <th>Amount_Received_By_The_PEI_From_Student</th>
                        <th>Protection_Amount</th>
                        <th>Protection_Amount_Received_Date</th>
                        <th>Payment_Acknowledgement_No</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }
    /**
     *
     */

    function getRowsHTMLOld() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');
        $rows = '';
        
        $month  = $fn->getReqParam('month');
        $day    = $fn->getReqParam('day');

        $current_year = date('Y');
        $current_date = $current_year . $month . $day;
        $date_15th    = $current_year . '-' . $month . '-' . '%';

        $contact_id = '';
        foreach($this->model->dataArray as $row){
            /*
            $SQLInv = "
            SELECT count(invoice_receipt_history) AS records_count
            FROM invoice_receipt_history irh 
            WHERE irh.invoice_id = {$row['invoice_id']}
            AND irh.invoice_date <= '{$search_date}'
            ";
            $resultInv = $db->sql_query($SQLInv);  
            */
            if($contact_id == ''){
                $no_of_installment = $row['no_of_installment'];
            }
            $contact_id = $row['student_contact_id']; 
            
            if ($row['student_contact_id'] == '' && $row['order_id'] == '') {
                $full_part_time = '';
                $course_duration = '';
                $fee_collection_cap = '';
            } else {
                $SQLPvt = "
                SELECT oi.*
                      ,o.order_id
                      ,o.contact_module
                      ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
                      ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
                      ,o.registration_type 
                      ,o.medical_insurance
                      ,o.add_registration_fee
                      ,o.full_time
                      ,cc.no_of_months
                FROM order_item oi 
                LEFT JOIN `order` o ON (o.order_id = oi.order_id)
                LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
                LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
                WHERE oi.order_id = {$row['order_id']}
                ORDER BY oi.order_item_id
                ";
                $resultForPvt = $db->sql_query($SQLPvt);  
                $modObj = getCPModuleObj('aceIms_order');
                $netTotal = $modObj->view->getTotalForPvtInst($resultForPvt);
                
                $fee_collection_cap = '';
                if ($contact_id == $row['student_contact_id'] || $contact_id == ''){
                    $no_of_installment = $no_of_installment - 1;
                }
                if($no_of_installment == 0){
                    if($row['no_of_months'] > 0){
                        $fee_collection_cap = number_format((($netTotal / $row['no_of_months'])* 6), 2);
                    }
                }
                
                $SQLSubject = "
                SELECT * FROM order_item 
                WHERE contact_id = {$row['student_contact_id']}
                  AND order_id = {$row['order_id']}
                  AND module = 'aceIms_subject'
                  AND item_title != 'Science Lab'
                ";
                $resultSubject = $db->sql_query($SQLSubject);
                $numRowsSubject = $db->sql_numrows($resultSubject);
                
                if ($numRowsSubject >= 5){
                    $full_part_time = 'F';
                    $course_duration = 'N';
                } else {
                    $full_part_time = 'P';
                    $course_duration = 'Y';
                }

                if ($no_of_installment == 0){
                    $contact_id = '';
                } else {
                    $contact_id = $row['student_contact_id']; 
                }            
            }

            $installment_title = substr($row['installment_title'], -1);
            $installment_title = 'ID' . $installment_title ;
            
            $date_of_birth = $dateUtil->formatDate($row['date_of_birth'], 'YYYYMMDD');
            $course_start_date = $dateUtil->formatDate($row['course_start_date'], 'YYYYMMDD');
            $course_end_date = $dateUtil->formatDate($row['course_end_date'], 'YYYYMMDD');
            $insurance_start_date = $dateUtil->formatDate($row['insurance_start_date'], 'YYYYMMDD');
            $insurance_end_date = $dateUtil->formatDate($row['insurance_end_date'], 'YYYYMMDD');
            $scheduled_payment_due_date = $dateUtil->formatDate($row['scheduled_payment_due_date'], 'YYYYMMDD');
            $course_termination_date = $dateUtil->formatDate($row['course_termination_date'], 'YYYYMMDD');

            $stp_application_date = 'NA';
            if($row['stp_application_date'] != '') {
                $stp_application_date = $dateUtil->formatDate($row['stp_application_date'], 'YYYYMMDD');
            }

            $stp_ipa_date = 'NA';
            if($row['stp_ipa_date'] != '') {
                $stp_ipa_date = $dateUtil->formatDate($row['stp_ipa_date'], 'YYYYMMDD');
            }

            $stp_issue_date = 'NA';
            if($row['stp_issue_date'] != '') {
                $stp_issue_date = $dateUtil->formatDate($row['stp_issue_date'], 'YYYYMMDD');
            }

            $stp_expiry_date = 'NA';
            if($row['stp_expiry_date'] != '') {
                $stp_expiry_date = $dateUtil->formatDate($row['stp_expiry_date'], 'YYYYMMDD');
            }

            $stp_cancellation_date = 'NA';
            if($row['stp_cancellation_date'] != '') {
                $stp_cancellation_date = $dateUtil->formatDate($row['stp_cancellation_date'], 'YYYYMMDD');
            }
            
            $receipt_date = $dateUtil->formatDate($row['receipt_date'], 'YYYYMMDD');
            
            $scheduled_payment_amount = number_format($row['scheduled_payment_amount'], 2);
            $premium_amount = number_format($row['premium_amount'], 2);

            $protectionSchemeType = '3';
            $stpRequirement = '2';
            $feeProtectionStatus = '2';
            
            $nationality = ($row['student_nationality'] == 'Singaporean') ? '1' : '3';
            
            $rows .= "
            <tr>
                <td class='txtRight'>{$current_date}</td>
                <td class='txtCenter'>{$cpCfg['peiUenNo']}</td>
                <td class='txtCenter'>{$cpCfg['printCompanyNamePvt']}</td>
                <td class='txtCenter'>{$cpCfg['bankInsurerType']}</td>
                <td class='txtCenter'>{$row['insurance_title']}</td>
                <td class='txtCenter'>{$cpCfg['peiFacilityAccountNo']}</td>
                <td class='txtCenter'>{$cpCfg['facilityValidTillDate']}</td>
                <td class='txtCenter'>{$protectionSchemeType}</td>
                <td class='txtCenter'>{$stpRequirement}</td>
                <td class='txtCenter'>{$row['student_registration_no']}</td>
                <td class='txtCenter'>{$row['student_id_card_no']}</td>
                <td>{$row['contact_name']}</td>
                <td class='txtCenter'>{$date_of_birth}</td>
                <td class='txtCenter'>{$row['student_gender']}</td>
                <td class='txtCenter'>{$nationality}</td>
                <td class='txtCenter'>{$row['student_nationality']}</td>
                <td class='txtCenter'>{$stp_application_date}</td>
                <td class='txtCenter'>{$stp_ipa_date}</td>
                <td class='txtCenter'>{$stp_issue_date}</td>
                <td class='txtCenter'>{$stp_expiry_date}</td>
                <td class='txtCenter'>{$stp_cancellation_date}</td>
                <td class='txtCenter'>{$full_part_time}</td>
                <td class='txtCenter'>{$row['course_code']}</td>
                <td>{$row['course_title']}</td>
                <td class='txtCenter'>{$course_start_date}</td>
                <td class='txtCenter'>{$course_end_date}</td>
                <td class='txtCenter'>{$course_duration}</td>
                <td class='txtCenter'>{$row['no_of_months']}</td>
                <td class='txtCenter'>{$feeProtectionStatus}</td>
                <td class='txtRight'>{$scheduled_payment_amount}</td>
                <td class='txtRight'>{$premium_amount}</td>
                <td class='txtRight'>{$fee_collection_cap}</td>
                <td class='txtRight'>{$course_termination_date}</td>
                <td></td>
                <td></td>
                <td class='txtCenter'>{$installment_title}</td>
                <td class='txtRight'>{$scheduled_payment_amount}</td>
                <td class='txtRight'>{$scheduled_payment_due_date}</td>
                <td class='txtRight'>{$insurance_code}</td>
                <td class='txtRight'>{$insurance_start_date}</td>
                <td class='txtRight'>{$insurance_end_date}</td>
                <td class='txtRight'>{$scheduled_payment_amount}</td>
                <td class='txtRight'>{$premium_amount}</td>
                <td class='txtRight'>{$receipt_date}</td>
                <td class='txtLeft'>{$receipt_code}</td>
                <td></td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
    function getRowsHTML() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');
        $rows = '';
        
        $month  = $fn->getReqParam('month');
        $day    = $fn->getReqParam('day');
        $whereCountAppend = '';
        $current_year = date('Y');
        $current_date = $current_year . $month . $day;
        $date_15th    = $current_year . '-' . $month . '-' . '%';

        $contact_id = '';
        foreach($this->model->dataArray as $row){
            /*
            $SQLInv = "
            SELECT count(invoice_receipt_history) AS records_count
            FROM invoice_receipt_history irh 
            WHERE irh.invoice_id = {$row['invoice_id']}
            AND irh.invoice_date <= '{$search_date}'
            ";
            $resultInv = $db->sql_query($SQLInv);  
            */

            if ($month != '' && $day == '' || $month != '' && $day == 1){
                $search_date = $current_year . '-' . $month . '-' . '1';
                $whereCountAppend = "i.invoice_date < '{$search_date}' AND";
            }
            else if ($month != '' && $day == 15){
                $search_date = $current_year . '-' . $month . '-' . '15';
                $whereCountAppend = "i.invoice_date <= '{$search_date}' AND";
            }
            
            $SQLCount = "
            SELECT count(*) AS no_of_installment
            FROM installment i 
            WHERE {$whereCountAppend}
            i.invoice_id = {$row['installment_invoice_id']}
            AND i.title != 'Registration'
            ";
            $resultCount = $db->sql_query($SQLCount);
            $rowCount = $db->sql_fetchrow($resultCount);

            if($contact_id == ''){
                $no_of_installment = $rowCount['no_of_installment'];
            }
            $contact_id = $row['student_contact_id']; 
            
            if ($row['student_contact_id'] == '' && $row['order_id'] == '') {
                $full_part_time = '';
                $course_duration = '';
                $fee_collection_cap = '';
            } else {
                $SQLPvt = "
                SELECT oi.*
                      ,o.order_id
                      ,o.contact_module
                      ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
                      ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
                      ,o.registration_type 
                      ,o.medical_insurance
                      ,o.add_registration_fee
                      ,o.full_time
                      ,cc.no_of_months
                FROM order_item oi 
                LEFT JOIN `order` o ON (o.order_id = oi.order_id)
                LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
                LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
                WHERE oi.order_id = {$row['order_id']}
                ORDER BY oi.order_item_id
                ";
                $resultForPvt = $db->sql_query($SQLPvt);  
                $modObj = getCPModuleObj('aceIms_order');
                $netTotal = $modObj->view->getTotalForPvtInst($resultForPvt);
                
                $fee_collection_cap = '';
                if ($contact_id == $row['student_contact_id'] || $contact_id == ''){
                    $no_of_installment = $no_of_installment - 1;
                }
                if($no_of_installment == 0){
                    if($row['no_of_months'] > 0){
                        $fee_collection_cap = number_format((($netTotal / $row['no_of_months'])* 6), 2);
                    }
                }
                
                $SQLSubject = "
                SELECT * FROM order_item 
                WHERE contact_id = {$row['student_contact_id']}
                  AND order_id = {$row['order_id']}
                  AND module = 'aceIms_subject'
                  AND item_title != 'Science Lab'
                ";
                $resultSubject = $db->sql_query($SQLSubject);
                $numRowsSubject = $db->sql_numrows($resultSubject);
                
                if ($numRowsSubject >= 5){
                    $full_part_time = 'F';
                    $course_duration = 'N';
                } else {
                    $full_part_time = 'P';
                    $course_duration = 'Y';
                }

                if ($no_of_installment == 0){
                    $contact_id = '';
                } else {
                    $contact_id = $row['student_contact_id']; 
                }            
            }

            $installment_title = substr($row['installment_title'], -1);
            $installment_title = 'ID' . $installment_title ;
            
            // To get insurance title
            $studInsRec = $fn->getRecordRowByID('student_insurance', 'installment_id', 
            $row['installment_id']);
            $insRec = $fn->getRecordRowByID('insurance', 'insurance_id', 
            $studInsRec['insurance_id']);
            $insurance_title = $insRec['title'];
            
            $date_of_birth = $dateUtil->formatDate($row['date_of_birth'], 'YYYYMMDD');
            $course_start_date = $dateUtil->formatDate($row['course_start_date'], 'YYYYMMDD');
            $course_end_date = $dateUtil->formatDate($row['course_end_date'], 'YYYYMMDD');
            $insurance_start_date = $this->getInstallmentRecords($row['installment_id'], 'ins_start_date');
            //$insurance_start_date = $dateUtil->formatDate($row['insurance_start_date'], 'YYYYMMDD');
            //$insurance_end_date = $dateUtil->formatDate($row['insurance_end_date'], 'YYYYMMDD');
            $insurance_end_date = $this->getInstallmentRecords($row['installment_id'], 'ins_end_date');
            $insurance_code = $this->getInstallmentRecords($row['installment_id'], 'ins_code');
            
            $scheduled_payment_due_date = $dateUtil->formatDate($row['scheduled_payment_due_date'], 'YYYYMMDD');
            $course_termination_date = $dateUtil->formatDate($row['course_termination_date'], 'YYYYMMDD');

            $stp_application_date = 'NA';
            if($row['stp_application_date'] != '') {
                $stp_application_date = $dateUtil->formatDate($row['stp_application_date'], 'YYYYMMDD');
            }

            $stp_ipa_date = 'NA';
            if($row['stp_ipa_date'] != '') {
                $stp_ipa_date = $dateUtil->formatDate($row['stp_ipa_date'], 'YYYYMMDD');
            }

            $stp_issue_date = 'NA';
            if($row['stp_issue_date'] != '') {
                $stp_issue_date = $dateUtil->formatDate($row['stp_issue_date'], 'YYYYMMDD');
            }

            $stp_expiry_date = 'NA';
            if($row['stp_expiry_date'] != '') {
                $stp_expiry_date = $dateUtil->formatDate($row['stp_expiry_date'], 'YYYYMMDD');
            }

            $stp_cancellation_date = 'NA';
            if($row['stp_cancellation_date'] != '') {
                $stp_cancellation_date = $dateUtil->formatDate($row['stp_cancellation_date'], 'YYYYMMDD');
            }
            
            $receipt_date = $this->getReceiptRecords($row['installment_id'], 'receipt_date');
            
            $receipt_code = $this->getReceiptRecords($row['installment_id'], 'receipt_code');
            
            $scheduled_payment_amount = number_format($row['scheduled_payment_amount'], 2);
            //$premium_amount = number_format($studInsRec['premium_amount'], 2);
            $premium_amount = $this->getInstallmentRecords($row['installment_id'], 'ins_amount');
            //$premium_amount = number_format($premium_amount, 2);

            $protectionSchemeType = '3';
            $stpRequirement = '2';
            $feeProtectionStatus = '2';
            
            $nationality = ($row['student_nationality'] == 'Singaporean') ? '1' : '3';
            
            $rows .= "
            <tr>
                <td class='txtRight'>{$current_date}</td>
                <td class='txtCenter'>{$cpCfg['peiUenNo']}</td>
                <td class='txtCenter'>{$cpCfg['printCompanyNamePvt']}</td>
                <td class='txtCenter'>{$cpCfg['bankInsurerType']}</td>
                <td class='txtCenter'>{$insurance_title}</td>
                <td class='txtCenter'>{$cpCfg['peiFacilityAccountNo']}</td>
                <td class='txtCenter'>{$cpCfg['facilityValidTillDate']}</td>
                <td class='txtCenter'>{$protectionSchemeType}</td>
                <td class='txtCenter'>{$stpRequirement}</td>
                <td class='txtCenter'>{$row['student_registration_no']}</td>
                <td class='txtCenter'>{$row['student_id_card_no']}</td>
                <td>{$row['contact_name']}</td>
                <td class='txtCenter'>{$date_of_birth}</td>
                <td class='txtCenter'>{$row['student_gender']}</td>
                <td class='txtCenter'>{$nationality}</td>
                <td class='txtCenter'>{$row['student_nationality']}</td>
                <td class='txtCenter'>{$stp_application_date}</td>
                <td class='txtCenter'>{$stp_ipa_date}</td>
                <td class='txtCenter'>{$stp_issue_date}</td>
                <td class='txtCenter'>{$stp_expiry_date}</td>
                <td class='txtCenter'>{$stp_cancellation_date}</td>
                <td class='txtCenter'>{$full_part_time}</td>
                <td class='txtCenter'>{$row['course_code']}</td>
                <td>{$row['course_title']}</td>
                <td class='txtCenter'>{$course_start_date}</td>
                <td class='txtCenter'>{$course_end_date}</td>
                <td class='txtCenter'>{$course_duration}</td>
                <td class='txtCenter'>{$row['no_of_months']}</td>
                <td class='txtCenter'>{$feeProtectionStatus}</td>
                <td class='txtRight'>{$scheduled_payment_amount}</td>
                <td class='txtRight'>{$premium_amount}</td>
                <td class='txtRight'>{$fee_collection_cap}</td>
                <td class='txtRight'>{$course_termination_date}</td>
                <td></td>
                <td></td>
                <td class='txtCenter'>{$installment_title}</td>
                <td class='txtRight'>{$scheduled_payment_amount}</td>
                <td class='txtRight'>{$scheduled_payment_due_date}</td>
                <td class='txtRight'>{$insurance_code}</td>
                <td class='txtRight'>{$insurance_start_date}</td>
                <td class='txtRight'>{$insurance_end_date}</td>
                <td class='txtRight'>{$scheduled_payment_amount}</td>
                <td class='txtRight'>{$premium_amount}</td>
                <td class='txtRight'>{$receipt_date}</td>
                <td class='txtLeft'>{$receipt_code}</td>
                <td></td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
    /**
     *
     */
    function getReceiptRecords($installment_id, $getWhat = "") {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $text = '';
        $receipt_date = '';
        $receipt_code = '';
        
        $SQLReceipt = "
        SELECT r.date
              ,r.receipt_code
        FROM invoice_receipt_history ir
        LEFT JOIN `receipt` r ON (r.receipt_id = ir.receipt_id)
        WHERE installment_id = {$installment_id}
        ORDER BY r.receipt_id
        ";
        $resultReceipt = $db->sql_query($SQLReceipt);
        
        while ($rowReceipt = $db->sql_fetchrow($resultReceipt)) {
            $receipt_date .= $dateUtil->formatDate($rowReceipt['date'], 'YYYYMMDD') . '/';
            $receipt_code .= $rowReceipt['receipt_code'] . '/';
        }
        if($getWhat == 'receipt_date'){
            $text = substr($receipt_date, 0, -1);
        }
        else if($getWhat == 'receipt_code'){
            $text = substr($receipt_code, 0, -1);
        }
        
        return $text;
    }
    /**
     *
     */
    function getInstallmentRecords($installment_id, $getWhat = "") {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $text = '';
        $ins_start_date = '';
        $ins_end_date = '';
        $ins_code = '';
        $ins_amount = '';
        
        $SQLReceipt = "
        SELECT ins.*
        FROM student_insurance ins
        WHERE ins.installment_id = {$installment_id}
        ORDER BY ins.insurance_id
        ";
        $resultIns = $db->sql_query($SQLReceipt);
        
        while ($rowIns = $db->sql_fetchrow($resultIns)) {
            $ins_start_date .= $dateUtil->formatDate($rowIns['insurance_start_date'], 'YYYYMMDD') . '/';
            $ins_end_date .= $dateUtil->formatDate($rowIns['insurance_end_date'], 'YYYYMMDD') . '/';
            $ins_code .= $rowIns['code'] . '/';
            $ins_amount += $rowIns['premium_amount'];
        }
        if($getWhat == 'ins_start_date'){
            $text = substr($ins_start_date, 0, -1);
        }
        else if($getWhat == 'ins_code'){
            $text = substr($ins_code, 0, -1);
        }
        else if($getWhat == 'ins_end_date'){
            $text = substr($ins_end_date, 0, -1);
        }
        else if($getWhat == 'ins_amount'){
            $text = $ins_amount;
        }
        
        return $text;
    }
}