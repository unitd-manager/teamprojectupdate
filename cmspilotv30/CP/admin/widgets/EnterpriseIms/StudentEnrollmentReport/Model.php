<?
class CP_Admin_Widgets_EnterpriseIms_StudentEnrollmentReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSQL(){

        $year = date('Y');
        $SQL = "
        SELECT DISTINCT co.contact_id
              ,c.title AS course_title
              ,b.title AS batch_title
              ,co.first_name AS student_name
              ,p.first_name AS parent_name
              ,p.mode_of_payment
    		  ,(SELECT COUNT(*)
    		    FROM course_contact cco
    		    WHERE cco.contact_id = co.contact_id
    		  	AND cco.year_of_enrollment <= {$year}
                  AND co.status = 'Active'
    		  ) AS contact_count
    		  ,s.title AS branch_title
        FROM course_contact cc
        LEFT JOIN site s ON (cc.site_id = s.site_id)
        JOIN course c ON (c.course_id = cc.course_id)
        JOIN contact co ON (co.contact_id = cc.contact_id)
        JOIN batch b ON (b.batch_id = cc.batch_id)
        JOIN parent p ON (p.parent_id = cc.parent_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $searchVar = $this->searchVar;
        //$searchVar->mainTableAlias = 'cc';
        $year = date('Y');

        $payment_mode = $fn->getReqParam('payment_mode');
        $new_student  = $fn->getReqParam('new_student');
        $site_id      = $fn->getReqParam('site_id');
        
        if ($site_id) {
            if(is_numeric($site_id)) {
                $searchVar->sqlSearchVar[] = "cc.site_id = '{$site_id}'";
            }
        }

        if ($payment_mode == 'All' || $payment_mode == '') {
        } else {
            $searchVar->sqlSearchVar[] = "p.mode_of_payment = '{$payment_mode}'";
        }

        $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = {$year}";       
        $searchVar->sqlSearchVar[] = "co.status = 'Active'";
        $searchVar->sortOrder = "cc.site_id ASC, p.first_name ASC, co.contact_id ASC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enterpriseIms_studentEnrollmentReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $sqlAppend = '';
        
        $payment_mode = $fn->getReqParam('payment_mode');
        $new_student  = $fn->getReqParam('new_student');
        $site_id      = $fn->getReqParam('site_id');
        
        if (is_numeric($site_id)) {
            $sqlAppend .= "AND cc.site_id = {$site_id}";
        }
        
        if ($payment_mode) {
            $sqlAppend .= " AND p.mode_of_payment = '{$payment_mode}'";
        }

        $year = date('Y');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "StudentEnrollmentReport_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Branch');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name of Student');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Gender');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Class');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Session');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mode of Payment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Address 1');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Address 2');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Postal Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Phone');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mobile');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Email');
        
        if ($payment_mode == 'Giro') {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name of Bank');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Account Name');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Bank Code');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Branch Code');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Account No');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'DDA');
        }
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
        
        $additionalFields = '';
        if ($payment_mode == 'Giro') {
            $additionalFields .= "
            ,p.bank_name
            ,p.bank_code
            ,p.dda
            ,p.account_name
            ,p.branch
            ,p.account_no
            ";
        }
        
        $SQL = "
        SELECT DISTINCT co.contact_id
              ,co.registration_no
              ,co.gender
              ,c.title AS course_title
              ,b.title AS batch_title
              ,co.first_name AS student_name
              ,p.first_name AS parent_name
              ,p.mode_of_payment
              ,p.address_flat
              ,p.address_street
              ,p.address_po_code
              ,p.phone
              ,p.mobile
              ,p.email
    		  ,(SELECT COUNT(*)
    		    FROM course_contact cco
    		    WHERE cco.contact_id = co.contact_id
    		  	AND cco.year_of_enrollment <= {$year}
                  AND co.status = 'Active'
    		  ) AS contact_count
    		  {$additionalFields}
    		  ,s.title AS branch_title
        FROM course_contact cc
        LEFT JOIN site s ON (cc.site_id = s.site_id)
        JOIN course c ON (c.course_id = cc.course_id)
        JOIN contact co ON (co.contact_id = cc.contact_id)
        JOIN batch b ON (b.batch_id = cc.batch_id)
        JOIN parent p ON (p.parent_id = cc.parent_id)
        WHERE co.status = 'Active'
          AND cc.year_of_enrollment = {$year}
          {$sqlAppend}
        ORDER BY cc.site_id ASC, p.first_name ASC, co.contact_id ASC 
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            if ($new_student == 1) {
                if ($row['contact_count'] == 1) {
                    $colc = 0;
                    $rowc++;
                    
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch_title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_name']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gender']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['parent_name']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['batch_title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mode_of_payment']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_flat']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_street']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_po_code']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['phone']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mobile']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['email']);

                    if ($payment_mode == 'Giro') {
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['bank_name']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['account_name']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['bank_code']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['account_no']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['dda']);
                    }
		        }
		    } else if ($new_student == 0 && $new_student != '') {
                if ($row['contact_count'] > 1) {
                    $colc = 0;
                    $rowc++;

                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch_title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_name']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gender']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['parent_name']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['batch_title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mode_of_payment']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_flat']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_street']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_po_code']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['phone']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mobile']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['email']);

                    if ($payment_mode == 'Giro') {
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['bank_name']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['account_name']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['bank_code']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['account_no']);
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['dda']);
                    }
		        }
		    } else {
                $colc = 0;
                $rowc++;

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch_title']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gender']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['parent_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_title']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['batch_title']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mode_of_payment']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_po_code']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['phone']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mobile']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['email']);

                if ($payment_mode == 'Giro') {
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['bank_name']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['account_name']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['bank_code']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['account_no']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['dda']);
                }
		    }
        }
        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    /**
     *
     */
    function getSqlForCount($new_student) {
        $db = Zend_Registry::get('db');
        
        $serial_no = 0;
        foreach($this->dataArray as $row){           
            if ($new_student == 1) {
                if ($row['contact_count'] == 1) {
                    $serial_no += 1;
		        }
		    } else if ($new_student == 0 && $new_student != '') {
                if ($row['contact_count'] > 1) {
                    $serial_no += 1;
		        }
		    } else {
                $serial_no += 1;
		    }
        }
        
        return $serial_no;
    }
}