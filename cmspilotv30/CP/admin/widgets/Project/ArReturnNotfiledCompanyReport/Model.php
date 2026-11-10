<?
class CP_Admin_Widgets_Project_ArReturnNotfiledCompanyReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSQL(){
        $SQL = "
        SELECT c.company_name
              ,p.title AS project_title
              ,t.title
              ,t.due_date
        FROM company c
        LEFT JOIN (project p) ON (c.company_id = p.company_id)
        LEFT JOIN (task t) ON (p.project_id = t.project_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'c';
        
        $group_name = $fn->getReqParam('group_name');
        
        if ($group_name != '' ) {
            $searchVar->sqlSearchVar[] = "c.group_name = '{$group_name}'";
        }   
       
        $searchVar->sqlSearchVar[] = "t.title = 'AR Date'";
        $searchVar->sqlSearchVar[] = "c.status != 'Strike Off'";
        $searchVar->sqlSearchVar[] = "p.work_status != 'Complete'";
        $searchVar->sqlSearchVar[] = "t.status != 'Complete'";
        $searchVar->sortOrder = 'c.company_name ASC';
    }
    
    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_arReturnNotfiledCompanyReport');

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

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "ArReturnNotFiled_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Serial No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Task Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Due Date');
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
        
        $group_name = $fn->getReqParam('group_name');
        $SQL = "
        SELECT c.company_name
              ,p.title AS project_title
              ,t.title
              ,t.due_date
        FROM company c
        LEFT JOIN (project p) ON (c.company_id = p.company_id)
        LEFT JOIN (task t) ON (p.project_id = t.project_id)
        WHERE c.group_name = '{$group_name}'
          AND t.title = 'AR Date'
          AND c.status != 'Strike Off'
          AND p.work_status != 'Complete'
          AND t.status != 'Complete'
        ORDER BY c.company_name ASC
        ";
        $result = $db->sql_query($SQL);
        $serial_no = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $date = $fn->getCPDate($row['due_date'],"d-m-Y");
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $date);
            $serial_no++;
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    /**
     *
     */
    function getExportToPdf(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

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

        $group_name = $fn->getReqParam('group_name');

        $prefix_month = $dateUtil->getLongMonthName($group_name);

        $SQL = "
        SELECT c.company_name
              ,p.title AS project_title
              ,t.title
              ,t.due_date
        FROM company c
        LEFT JOIN (project p) ON (c.company_id = p.company_id)
        LEFT JOIN (task t) ON (p.project_id = t.project_id)
        WHERE c.group_name = '{$group_name}'
          AND t.title = 'AR Date'
          AND c.status != 'Strike Off'
          AND p.work_status != 'Complete'
          AND t.status != 'Complete'
        ORDER BY c.company_name ASC
        ";
        $result = $db->sql_query($SQL);

        $tbl3 = '
        <table>
            <tbody>
                <tr>
                    <td align="center"><strong>AR Return not filed report for ' . $prefix_month .' Year End Companies</strong></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <table border="1" width="100%" cellpadding="4">
            <tr bgcolor="#B6E5F9">
                <td width="7%"><strong>S.No</strong></td>
                <td width="32%"><strong>Company Name</strong></td>
                <td width="33%"><strong>Project Title</strong></td>
                <td width="14%"><strong>Task Title</strong></td>
                <td width="14%"><strong>Due Date</strong></td>
            </tr>
        ';

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $due_date = $fn->getCPDate($row['due_date'],"d-m-Y");

            $tbl3 .= '
            <tr>
                <td width="7%" align="center">'. $count .'</td>
                <td width="32%">' . $row['company_name'] . '</td>
                <td width="33%">' . $row['project_title'] . '</td>
                <td width="14%">' . $row['title'] . '</td>
                <td width="14%">' . $due_date . '</td>
            </tr>
            ';
            $count++;
        }

        $tbl3 = $tbl3 .'
        </table>
        ';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Company-Year-end" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }
}