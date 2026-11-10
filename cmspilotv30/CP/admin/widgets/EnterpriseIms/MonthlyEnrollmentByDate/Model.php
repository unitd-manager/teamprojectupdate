<?
class CP_Admin_Widgets_EnterpriseIms_MonthlyEnrollmentByDate_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSQLOld(){
        $SQL = "
        SELECT irh.invoice_receipt_history_id
              ,irh.title as installment_title
              ,irh.invoice_date AS scheduled_payment_due_date
              ,irh.amount AS scheduled_payment_amount
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
              ,si.premium_amount
              ,si.code AS insurance_code
              ,si.insurance_start_date
              ,si.insurance_end_date
              ,r.receipt_code
              ,r.date as receipt_date
        FROM invoice_receipt_history irh
        LEFT JOIN invoice i ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN receipt r ON (irh.receipt_id = r.receipt_id)
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = o.contact_id)
        LEFT JOIN course_contact cc ON (cont.contact_id = cc.contact_id)
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        LEFT JOIN student_insurance si ON (irh.invoice_receipt_history_id = si.invoice_receipt_history_id)
        ";
        //LEFT JOIN student_insurance b ON (cc.course_contact_id = b.course_contact_id)
        return $SQL;
    }
    function getSQL(){
        $SQL = "
        SELECT inst.installment_id 
              ,inst.title as installment_title
              ,inst.invoice_date AS scheduled_payment_due_date
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
        //LEFT JOIN student_insurance b ON (cc.course_contact_id = b.course_contact_id)
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

        $searchVar->sqlSearchVar[] = "inst.title != 'Registration' AND c.course_type = 'Long Term'";
        $searchVar->sortOrder = 'o.order_id';
    }

    /**
     *
     */
    function setSearchVarOld() {
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
            $searchVar->sqlSearchVar[] = "irh.invoice_date < '{$search_date}'";
        }
        else if ($month != '' && $day == 15){
            $search_date = $current_year . '-' . $month . '-' . '15';
            $searchVar->sqlSearchVar[] = "irh.invoice_date <= '{$search_date}'";
        }

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}'";
        }
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date <= '{$end_date}'";
        }

        $searchVar->sqlSearchVar[] = "irh.title != 'Registration'";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enterpriseIms_monthlyEnrollmentReports');

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Bank_Insurer_Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'FPS_Provider');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PEI_Facility_Account_No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Facility_Valid_Till_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Protection_Scheme_Type');
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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fee_Protection_Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total_Course_Fee_Stated_In_Student_Contract');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total_Course_Fee_To_Be_Protected');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fee_Collection_Cap');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course_Termination_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date_That_Refund_Instruction_Sent_To_Bank_By_PEI');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course_Fee_Amount_Refunded');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Instalment_ID');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Scheduled_Payment_Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Scheduled_Payment_Due_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Escrow_Unique_No_Or_Insurance_Policy_No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Protection_Start_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Protection_End_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount_Received_By_The_PEI_From_Student');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Protection_Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Protection_Amount_Received_Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Payment_Acknowledgement_No');
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
              ,inst.title as installment_title
              ,inst.invoice_date AS scheduled_payment_due_date
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
              ,si.premium_amount
              ,si.code AS insurance_code
              ,si.insurance_start_date
              ,si.insurance_end_date
              ,ins.title AS insurance_title
        FROM installment inst
        LEFT JOIN invoice i ON (inst.invoice_id = i.invoice_id)
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = o.contact_id)
        LEFT JOIN course_contact cc ON (cont.contact_id = cc.contact_id)
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        LEFT JOIN student_insurance si ON (inst.installment_id = si.installment_id)
        LEFT JOIN insurance ins ON (si.insurance_id = ins.insurance_id)
        WHERE inst.title != 'Registration'
          AND c.course_type = 'Long Term'
        {$sqlAppend}
        ORDER BY o.order_id
        ";

        $result = $db->sql_query($SQL);

        $month  = $fn->getReqParam('month');
        $day    = $fn->getReqParam('day');
        $whereCountAppend = '';

        $current_year = date('Y');
        $current_date = $current_year . $month . $day;
        $date_15th    = $current_year . '-' . $month . '-' . '%';

        $contact_id = '';
        while ($row = $db->sql_fetchrow($result)) {
            
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
                $modObj = getCPModuleObj('enterpriseIms_order');
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
            $insurance_start_date = $this->view->getInstallmentRecords($row['installment_id'], 'ins_start_date');
            //$insurance_start_date = $dateUtil->formatDate($row['insurance_start_date'], 'YYYYMMDD');
            //$insurance_end_date = $dateUtil->formatDate($row['insurance_end_date'], 'YYYYMMDD');
            $insurance_end_date = $this->view->getInstallmentRecords($row['installment_id'], 'ins_end_date');
            $insurance_code = $this->view->getInstallmentRecords($row['installment_id'], 'ins_code');
            
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
            
            $receipt_date = $this->view->getReceiptRecords($row['installment_id'], 'receipt_date');
            
            $receipt_code = $this->view->getReceiptRecords($row['installment_id'], 'receipt_code');
            
            $scheduled_payment_amount = number_format($row['scheduled_payment_amount'], 2);
            //$premium_amount = number_format($studInsRec['premium_amount'], 2);
            $premium_amount = $this->view->getInstallmentRecords($row['installment_id'], 'ins_amount');
            //$premium_amount = number_format($premium_amount, 2);

            $protectionSchemeType = '3';
            $stpRequirement = '2';
            $feeProtectionStatus = '2';
            
            $nationality = ($row['student_nationality'] == 'Singaporean') ? '1' : '3';
            
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $current_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['peiUenNo']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['printCompanyNamePvt']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['bankInsurerType']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $insurance_title);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['peiFacilityAccountNo']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['facilityValidTillDate']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $protectionSchemeType);
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
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['no_of_months']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $feeProtectionStatus);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $scheduled_payment_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $premium_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fee_collection_cap);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $course_termination_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $installment_title);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $scheduled_payment_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $scheduled_payment_due_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $insurance_code);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $insurance_start_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $insurance_end_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $scheduled_payment_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $premium_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $receipt_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $receipt_code);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}