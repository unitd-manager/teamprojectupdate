<?
class CP_Admin_Widgets_Pms_OverdueReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DISTINCT c.contact_id
              ,c.first_name
              ,c.registration_no
              ,p.first_name AS parent_name
              ,SUM(i.invoice_amount - i.discount_amount) AS total_amount_payable
              ,s.title AS site_title
        FROM contact c
        LEFT JOIN (invoice i)           ON (c.contact_id  = i.contact_id)
        LEFT JOIN (`order` o)           ON (i.order_id    = o.order_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id  = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id  = p.parent_id)
        LEFT JOIN (site s)              ON (c.site_id     = s.site_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $searchVar = $this->searchVar;

        $site_id = $fn->getReqParam('site_id');
        $year    = $fn->getReqParam('year');
        $month   = $fn->getReqParam('month');
        
        if ($site_id) {
            if(is_numeric($site_id)) {
                $searchVar->sqlSearchVar[] = "c.site_id = '{$site_id}'";
            }
        }
        
        if ($year != '' && $month != '') {
            $date = $year . '-' . $month . '-01';
            //$searchVar->sqlSearchVar[] = "i.invoice_date <= '{$date}'";
            $searchVar->sqlSearchVar[] = "i.invoice_date = '{$date}'";
        }

        $searchVar->sqlSearchVar[] = "i.status = 'Due'";
        $searchVar->sqlSearchVar[] = "c.status = 'Active'";

        $searchVar->sortOrder = "c.site_id, c.registration_no ASC";
        $searchVar->groupBy = "i.contact_id";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_overdueReport');

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
        
        $site_id = $fn->getReqParam('site_id');
        $year    = $fn->getReqParam('year');
        $month   = $fn->getReqParam('month');
        
        if (is_numeric($site_id)) {
            $sqlAppend .= " AND c.site_id = {$site_id}";
        }
        
        if ($year != '' && $month != '') {
            $date = $year . '-' . $month . '-01';
            $sqlAppend .= " AND i.invoice_date = '{$date}'";
        }

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "OverdueFeesReport_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Registration No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Due');

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
              ,c.first_name
              ,c.registration_no
              ,p.first_name AS parent_name
              ,SUM(i.invoice_amount - i.discount_amount) AS total_amount_payable
              ,s.title AS site_title
        FROM contact c
        LEFT JOIN (invoice i)           ON (c.contact_id  = i.contact_id)
        LEFT JOIN (`order` o)           ON (i.order_id    = o.order_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id  = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id  = p.parent_id)
        LEFT JOIN (site s)              ON (c.site_id     = s.site_id)
        WHERE i.status = 'Due'
          AND c.status = 'Active'
          {$sqlAppend}
        GROUP BY i.contact_id
        ORDER BY c.site_id, c.registration_no ASC
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            if ($row['total_amount_payable'] > 0) {
                $colc = 0;
                $rowc++;
    
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['site_title']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['parent_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['total_amount_payable']);
            }
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}