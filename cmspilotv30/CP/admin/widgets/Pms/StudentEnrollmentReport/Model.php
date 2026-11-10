<?
class CP_Admin_Widgets_Pms_StudentEnrollmentReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSQL(){

        $SQL = "
        SELECT DISTINCT c.contact_id
              ,c.first_name AS student_name
              ,c.date_of_birth
              ,c.gender
              ,c.registration_no
              ,c.id_card_no
              ,s.title AS branch_name
              ,p.dda
              ,p.first_name AS parent_name
        FROM contact c
        LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p) ON (pc.parent_id = p.parent_id)
        LEFT JOIN (site s) ON (c.site_id = s.site_id)
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

        $year_of_joining = $fn->getReqParam('year');
        $site_id         = $fn->getReqParam('site_id');
        
        if ($year_of_joining == '') {
            $year = date('Y');
        }

        if ($site_id) {
            if(is_numeric($site_id)) {
                $searchVar->sqlSearchVar[] = "c.site_id = '{$site_id}'";
            }
        }

        $searchVar->sqlSearchVar[] = "c.year_of_joining = {$year_of_joining}";       
        $searchVar->sqlSearchVar[] = "c.status = 'Active'";
        $searchVar->sortOrder = "c.site_id ASC, c.registration_no ASC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_studentEnrollmentReport');

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
        
        $year_of_joining = $fn->getReqParam('year');
        $site_id         = $fn->getReqParam('site_id');
        
        if (is_numeric($site_id)) {
            $sqlAppend .= "AND c.site_id = {$site_id}";
        }
        
        if ($year_of_joining == '') {
            $year = date('Y');
        }

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
        $total_receipt_amount = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Branch');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name of Student');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Reg No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Gender');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Year of Joining');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'DDA');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount Paid');
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
        
        $SQL = "
        SELECT DISTINCT c.contact_id
              ,c.first_name AS student_name
              ,c.date_of_birth
              ,c.gender
              ,c.registration_no
              ,c.id_card_no
              ,s.title AS branch_name
              ,c.year_of_joining
              ,p.dda
              ,p.first_name AS parent_name
        FROM contact c
        LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p) ON (pc.parent_id = p.parent_id)
        LEFT JOIN (site s) ON (c.site_id = s.site_id)
        WHERE c.status = 'Active'
          AND c.year_of_joining = {$year_of_joining}
          {$sqlAppend}
        ORDER BY c.site_id ASC, c.registration_no ASC
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            /* Finding course contact id (SQL showing duplicate data, so writing separately */
            $sqlCc = "
            SELECT order_id, creation_date FROM course_contact
             WHERE year_of_enrollment = '{$year_of_joining}' AND contact_id = {$row['contact_id']}
            ";
            $resultCc = $db->sql_query($sqlCc);
            $ccRec = $db->sql_fetchrow($resultCc);

            $registration_date = $dateUtil->formatDate($ccRec['creation_date'], 'YYYY-MM-DD');
            $date = $registration_date . ' 00:00:00';

            $sqlReceipt = "
            SELECT SUM(amount) AS receipt_amount
            FROM receipt
            WHERE date = '{$date}'
              AND order_id = '{$ccRec['order_id']}'
            ";
            $resultReceipt = $db->sql_query($sqlReceipt);
            $rowReceipt = $db->sql_fetchrow($resultReceipt);

            $colc = 0;
            $rowc++;
            $total_receipt_amount += $rowReceipt['receipt_amount'];
            
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['id_card_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gender']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['year_of_joining']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['parent_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['dda']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowReceipt['receipt_amount']);
        }
        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Sub Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_receipt_amount);
        $actSheet->getStyle("A{$rowc}:I{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    /**
     *
     */
    function getSqlForCount() {
        $db = Zend_Registry::get('db');
        
        $serial_no = 0;
        foreach($this->dataArray as $row){           
            $serial_no += 1;
        }
        
        return $serial_no;
    }
}