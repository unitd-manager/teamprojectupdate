<?
class CP_Admin_Widgets_Pms_GiroFailureByMonthReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT c.contact_id
              ,c.first_name
              ,i.order_id
              ,p.first_name AS parent_name
              ,p.dda
              ,i.invoice_id
              ,s.title AS branch_name
              ,DATE_FORMAT(i.invoice_date, '%m') AS invoice_month
              ,(i.invoice_amount - i.discount_amount) AS invoice_amount
        FROM contact c 
        JOIN (invoice i)                ON (i.contact_id = c.contact_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id = p.parent_id)
        LEFT JOIN (site s)              ON (c.site_id    = s.site_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $searchVar = $this->searchVar;

        $site_id    = $fn->getReqParam('site_id');
        $year       = $fn->getReqParam('year');
        $from_month = $fn->getReqParam('from_month');
        $to_month   = $fn->getReqParam('to_month');
        
        if ($site_id) {
            if(is_numeric($site_id)) {
                $searchVar->sqlSearchVar[] = "c.site_id = '{$site_id}'";
            }
        }

        if ($year != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'";
        } 
        
        if ($from_month != '' && $to_month != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%m') BETWEEN '{$from_month}' AND '{$to_month}'";
        }

        $searchVar->sqlSearchVar[] = "i.giro_failed = 'Yes'";
        $searchVar->sqlSearchVar[] = "i.status = 'Due' ";
        $searchVar->sqlSearchVar[] = "c.status = 'Active'";
        $searchVar->sqlSearchVar[] = "p.mode_of_payment = 'Giro'";

        $searchVar->sortOrder = "DATE_FORMAT(i.invoice_date, '%m') ASC, p.dda ASC";
        //$searchVar->groupBy = "i.contact_id";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_giroFailureByMonthReport');

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
        
        $site_id    = $fn->getReqParam('site_id');
        $year       = $fn->getReqParam('year');
        $from_month = $fn->getReqParam('from_month');
        $to_month   = $fn->getReqParam('to_month');
        
        if (is_numeric($site_id)) {
            $sqlAppend .= " AND c.site_id = {$site_id}";
        }
        
        if ($year != '') {
            $sqlAppend .= " AND DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'";
        }
        
        if ($from_month != '' && $to_month != '') {
            $sqlAppend .= " AND DATE_FORMAT(i.invoice_date, '%m') BETWEEN '{$from_month}' AND '{$to_month}'";
        }
        
        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "GiroFailureByMonthReport_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'DDA');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
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
        SELECT c.contact_id
              ,c.first_name
              ,i.order_id
              ,p.first_name AS parent_name
              ,p.dda
              ,i.invoice_id
              ,s.title AS branch_name
              ,DATE_FORMAT(i.invoice_date, '%m') AS invoice_month
              ,(i.invoice_amount - i.discount_amount) AS invoice_amount
        FROM contact c 
        JOIN (invoice i)                ON (i.contact_id = c.contact_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id = p.parent_id)
        LEFT JOIN (site s)              ON (c.site_id    = s.site_id)
        WHERE i.status = 'Due'
          AND i.giro_failed = 'Yes'
          AND c.status = 'Active'
          AND p.mode_of_payment = 'Giro'
          {$sqlAppend}
        ORDER BY DATE_FORMAT(i.invoice_date, '%m') ASC, p.dda ASC
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $prefix_month = $row['invoice_month'];

            switch ($prefix_month) {
                case 1: $prefix_month = 'January';
                break;
                case 2: $prefix_month = 'February';
                break;
                case 3: $prefix_month = 'March';
                break;
                case 4: $prefix_month = 'April';
                break;
                case 5: $prefix_month = 'May';
                break;
                case 6: $prefix_month = 'June';
                break;
                case 7: $prefix_month = 'July';
                break;
                case 8: $prefix_month = 'August';
                break;
                case 9: $prefix_month = 'September';
                break;
                case 10: $prefix_month = 'October';
                break;
                case 11: $prefix_month = 'November';
                break;
                case 12: $prefix_month = 'December';
                break;
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['dda']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['parent_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $prefix_month);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_amount']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}