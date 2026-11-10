<?
class CP_Admin_Widgets_Hms_RevenueByMonth_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DATE_FORMAT(i.invoice_date, '%b %Y') AS invoice_month
              ,(SUM(i.invoice_amount - i.discount)) AS invoice_amount_monthly
        FROM invoice i
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';
        
        $site_id    = $fn->getReqParam('site_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $yearVal    = $fn->getReqParam('year');

        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "i.site_id = {$site_id}" ;
            }
        }
        
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->groupBy = "DATE_FORMAT(i.invoice_date, '%Y-%m')";
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_revenueByMonth');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "RevenueByMonth" . date("d-m-Y") . ".xls";

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
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $actSheet->mergeCells("A{$rowc}:B{$rowc}");
        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Revenue by Month');

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        /******************** FORMAT HEADER *******************/
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A2:{$lastCol}2")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $appendSql  = '';
        $site_id    = $fn->getReqParam('site_id');
        $yearVal    = $fn->getReqParam('year');

        if ($yearVal != '') {
            $appendSql .= " AND DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'";
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $appendSql .= " AND i.site_id = {$site_id}" ;
            }
        }

        $SQL = "
        SELECT DATE_FORMAT(i.invoice_date, '%b %Y') AS invoice_month
              ,(SUM(i.invoice_amount - i.discount)) AS invoice_amount_monthly
        FROM invoice i
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        WHERE i.status != 'Cancelled'
        GROUP BY DATE_FORMAT(i.invoice_date, '%Y-%m')
        ";
        $result = $db->sql_query($SQL);
        $total = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $total += $row['invoice_amount_monthly'];
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_month']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($row['invoice_amount_monthly'],2));
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($total,2));
        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}