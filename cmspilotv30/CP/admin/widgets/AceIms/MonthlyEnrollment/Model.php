<?
class CP_Admin_Widgets_AceIms_MonthlyEnrollment_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT inst.installment_id 
              ,inst.amount AS scheduled_payment_amount
              ,inst.invoice_id AS installment_invoice_id
              ,cc.course_contact_id
              ,cc.discount
              ,cc.course_status
              ,cc.registration_type
              ,cc.course_termination_date
              ,cc.no_of_months
              ,c.course_code
              ,c.title AS course_title
              ,c.course_type
              ,c.duration
              ,c.valid_date_from AS course_start_date
              ,c.valid_date_to AS course_end_date
              ,b.title AS batch_title
              ,cont.contact_id AS student_contact_id
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              ,cont.registration_no AS student_registration_no
              ,cont.id_card_no AS student_id_card_no
              ,cont.stp_application_date
              ,cont.stp_ipa_date
              ,cont.stp_issue_date
              ,cont.stp_expiry_date
              ,cont.stp_cancellation_date
              ,IF(cont.gender = 'Female', 'F', 'M') AS student_gender
              ,cont.nationality AS student_nationality
              ,cont.date_of_birth
              ,o.order_date
              ,o.order_id
              ,o.no_of_installment
        FROM installment inst
        LEFT JOIN invoice i ON (inst.invoice_id = i.invoice_id)
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = o.contact_id)
        LEFT JOIN course_contact cc ON (cont.contact_id = cc.contact_id)
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        ";
        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $day        = $fn->getReqParam('day');
         
        $current_year = date('Y');
        $search_date = $current_year . '-' . $month . '-' . $day;

        if ($month != '' && $day == '' || $month != '' && $day == 1){
            $search_date = $current_year . '-' . $month . '-' . '1';
            $searchVar->sqlSearchVar[] = "inst.invoice_date < '{$search_date}'";
        }
        else if ($month != '' && $day == 15){
            $search_date = $current_year . '-' . $month . '-' . '15';
            $searchVar->sqlSearchVar[] = "inst.invoice_date <= '{$search_date}'";
        }

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}'";
        }
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date <= '{$end_date}'";
        }

        $searchVar->sqlSearchVar[] = "inst.title != 'Registration' AND c.course_type = 'Short Term'";
        $searchVar->sortOrder = 'o.order_id';
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_monthlyEnrollmentReports');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $day        = $fn->getReqParam('day');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "MonthlyEnrollmentReport_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Data_Accuracy_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PEIs_UEN_No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PEI_Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STP_Requirement');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student_ID_Given_By_PEI');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC_FIN_No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student_Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date_Of_Birth');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Gender');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Singapore_Citizen_PR_Indicator');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Nationality');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STP_Application_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STP_IPA_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STP_Issue_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STP_Expiry_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STP_Cancellation_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Full_Part_Time_Indicator');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course_ID');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course_Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course_Start_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course_End_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course_Durations_Not_More_Than_50_Hours_Or_30_Days_Declaration');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course_Durations_Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total_Course_Fee_Stated_In_Student_Contract');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fee_Collection_Cap');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course_Termination_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'FPS_Waiver_Reason');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Waiver_Ref_No_From_CPE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CPE_Approval_Date_For_FPS_Waiver');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name_Of_Sponsor_Organization');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Any_Other_Useful_Information');
        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );
        
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);
        
        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $sqlAppend = '';
        if($start_date != '' && $end_date != ''){
            $sqlAppend = "AND o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        }

        $current_year = date('Y');
        $search_date = $current_year . '-' . $month . '-' . $day;
        if ($month != '' && $day !== ''){
            $sqlAppend .= "AND inst.invoice_date <= '{$search_date}'";
        }

        $SQL = "
        SELECT inst.installment_id 
              ,inst.amount AS scheduled_payment_amount
              ,inst.invoice_id AS installment_invoice_id
              ,cc.course_contact_id
              ,cc.discount
              ,cc.course_status
              ,cc.registration_type
              ,cc.course_termination_date
              ,cc.no_of_months
              ,c.course_code
              ,c.title AS course_title
              ,c.course_type
              ,c.duration
              ,c.valid_date_from AS course_start_date
              ,c.valid_date_to AS course_end_date
              ,b.title AS batch_title
              ,cont.contact_id AS student_contact_id
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              ,cont.registration_no AS student_registration_no
              ,cont.id_card_no AS student_id_card_no
              ,cont.stp_application_date
              ,cont.stp_ipa_date
              ,cont.stp_issue_date
              ,cont.stp_expiry_date
              ,cont.stp_cancellation_date
              ,IF(cont.gender = 'Female', 'F', 'M') AS student_gender
              ,cont.nationality AS student_nationality
              ,cont.date_of_birth
              ,o.order_date
              ,o.order_id
              ,o.no_of_installment
        FROM installment inst
        LEFT JOIN invoice i ON (inst.invoice_id = i.invoice_id)
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = o.contact_id)
        LEFT JOIN course_contact cc ON (cont.contact_id = cc.contact_id)
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        WHERE inst.title != 'Registration'
          AND c.course_type = 'Short Term'
        {$sqlAppend}
        ";

        $result = $db->sql_query($SQL);
        $current_date = date("Ym") . '01';

        $contact_id = '';
        $whereCountAppend = '';
        while ($row = $db->sql_fetchrow($result)) {
            if ($month != '' && $day == '' || $month != '' && $day == 1){
                $search_date = $current_year . '-' . $month . '-' . '1';
                $whereCountAppend = "AND i.invoice_date < '{$search_date}'";
            }
            else if ($month != '' && $day == 15){
                $search_date = $current_year . '-' . $month . '-' . '15';
                $whereCountAppend = "AND i.invoice_date <= '{$search_date}'";
            }
            
            $SQLCount = "
            SELECT count(*) AS no_of_installment
            FROM installment i 
            WHERE i.invoice_id = {$row['installment_invoice_id']}
            AND i.title != 'Registration'
            {$whereCountAppend}
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
                
                $fee_collection_cap = 'NA';
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

            $date_of_birth = $dateUtil->formatDate($row['date_of_birth'], 'YYYYMMDD');
            $course_start_date = $dateUtil->formatDate($row['course_start_date'], 'YYYYMMDD');
            $course_end_date = $dateUtil->formatDate($row['course_end_date'], 'YYYYMMDD');
            
            $course_termination_date = 'NA';
            if($row['course_termination_date'] != '') {
                $course_termination_date = $dateUtil->formatDate($row['course_termination_date'], 'YYYYMMDD');
            }

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
            
            $scheduled_payment_amount = number_format($row['scheduled_payment_amount'], 2);

            $stpRequirement = '2';
            
            $nationality = ($row['student_nationality'] == 'Singaporean') ? '1' : '3';
            
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $current_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['peiUenNo']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['printCompanyNamePvt']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stpRequirement);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_registration_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_id_card_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $date_of_birth);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_gender']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $nationality);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_nationality']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stp_application_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stp_ipa_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stp_issue_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stp_expiry_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stp_cancellation_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $full_part_time);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $course_start_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $course_end_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $course_duration);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NA');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $scheduled_payment_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fee_collection_cap);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $course_termination_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['courseDurationForF2Pvt']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['wavierRefNoForF2Pvt']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['cpeApprovalDateForF2Pvt']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NA');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NA');
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}