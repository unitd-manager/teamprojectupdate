<?
class CP_Admin_Widgets_EnterpriseIms_MonthlyEnrollmentReports_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
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

    function getRowsHTML() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $rows = '';
        
        $current_date = date("Ym") . '01';

        foreach($this->model->dataArray as $row){
            $SQLSubject = "
            SELECT * FROM order_item 
            WHERE contact_id = {$row['student_contact_id']}
              AND order_id = {$row['order_id']}
              AND module = 'enterpriseIms_subject'
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
        
            $date_of_birth = $dateUtil->formatDate($row['date_of_birth'], 'YYYYMMDD');
            $course_start_date = $dateUtil->formatDate($row['course_start_date'], 'YYYYMMDD');
            $course_end_date = $dateUtil->formatDate($row['course_end_date'], 'YYYYMMDD');
            $insurance_start_date = $dateUtil->formatDate($row['insurance_start_date'], 'YYYYMMDD');
            $insurance_end_date = $dateUtil->formatDate($row['insurance_end_date'], 'YYYYMMDD');
            $scheduled_payment_due_date = $dateUtil->formatDate($row['scheduled_payment_due_date'], 'YYYYMMDD');
            $course_termination_date = $dateUtil->formatDate($row['course_termination_date'], 'YYYYMMDD');
            $stp_application_date = $dateUtil->formatDate($row['stp_application_date'], 'YYYYMMDD');
            $stp_ipa_date = $dateUtil->formatDate($row['stp_ipa_date'], 'YYYYMMDD');
            $stp_issue_date = $dateUtil->formatDate($row['stp_issue_date'], 'YYYYMMDD');
            $stp_expiry_date = $dateUtil->formatDate($row['stp_expiry_date'], 'YYYYMMDD');
            $stp_cancellation_date = $dateUtil->formatDate($row['stp_cancellation_date'], 'YYYYMMDD');
            
            $scheduled_payment_amount = number_format($row['scheduled_payment_amount'], 2);

            $protectionSchemeType = '3';
            $stpRequirement = '2';
            $feeProtectionStatus = '2';
            
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
                <td class='txtCenter'>{$row['student_is_citizen']}</td>
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
                <td class='txtCenter'>{$row['duration']}</td>
                <td class='txtCenter'>{$feeProtectionStatus}</td>
                <td class='txtRight'>{$scheduled_payment_amount}</td>
                <td class='txtRight'>{$row['premium_amount']}</td>
                <td class='txtRight'></td>
                <td class='txtRight'>{$course_termination_date}</td>
                <td></td>
                <td></td>
                <td class='txtCenter'></td>
                <td class='txtRight'>{$scheduled_payment_amount}</td>
                <td class='txtRight'>{$scheduled_payment_due_date}</td>
                <td class='txtRight'>{$row['insurance_code']}</td>
                <td class='txtRight'>{$insurance_start_date}</td>
                <td class='txtRight'>{$insurance_end_date}</td>
                <td class='txtRight'></td>
                <td class='txtRight'>{$row['premium_amount']}</td>
                <td class='txtRight'></td>
                <td></td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}