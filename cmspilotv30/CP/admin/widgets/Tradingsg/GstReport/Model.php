<?
class CP_Admin_Widgets_Tradingsg_GstReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT  o.order_id
               ,DATE_FORMAT(o.order_date, '%d-%m-%Y')AS order_date
               ,SUM(oi.qty*oi.unit_price)AS order_amount
               ,(SELECT SUM(i.invoice_amount)
                    FROM invoice i
                  WHERE i.order_id = o.order_id
                  AND i.status !='Cancelled')AS amount_invoiced
               FROM `order` o
        LEFT JOIN `order_item` oi ON (oi.order_id = o.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $year       = $fn->getReqParam('year');
        $month      = $fn->getReqParam('month');
        $company_id = $fn->getReqParam('company_id');

        $current_date = date('Y-m-d');

        //$searchVar->sqlSearchVar[] = "i.status !='Cancelled'";

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            if ($year == '') {
                $year = date('Y');
            }

            $start_date = $year . '-' . $month . '-01';
            $end_date   = $year . '-' . $month . '-31';;

            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $searchVar->groupBy   = 'o.order_id';
        $searchVar->sortOrder = "o.order_id DESC,o.order_date DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_summaryByClient');

        $this->dataArray = $dataArray;
        return $this->dataArray;
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
        $year       = $fn->getReqParam('year');
        $month      = $fn->getReqParam('month');
        $company_id = $fn->getReqParam('company_id');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "GSTReport__" . date("d-m-Y") . ".xls";

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
        $appendSql = '';
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Id');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount Invoiced');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'GST Amount');
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

        $appendSql = '';
        $current_date = date('Y-m-d');
        if ($start_date != '' && $end_date == '') {
            $appendSql .= " o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
            $appendSql .= " o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql .= " o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            if ($year == '') {
                $year = date('Y');
            }

            $start_date = $year . '-' . $month . '-01';
            $end_date   = $year . '-' . $month . '-31';;

            $appendSql .= " o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $SQL = "
        SELECT  o.order_id
               ,DATE_FORMAT(o.order_date, '%d-%m-%Y')AS order_date
               ,SUM(oi.qty*oi.unit_price)AS order_amount
               ,(SELECT SUM(i.invoice_amount)
                    FROM invoice i
                  WHERE i.order_id = o.order_id
                  AND i.status !='Cancelled')AS amount_invoiced
               FROM `order` o
        LEFT JOIN `order_item` oi ON (oi.order_id = o.order_id)
        WHERE {$appendSql}
        GROUP BY o.order_id
        ORDER BY o.order_id DESC,o.order_date DESC
        ";
        $result = $db->sql_query($SQL);

        $overall_order_amount   = 0;
        $overall_amount_invoiced = 0;
        $overall_gst_amount  = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            //$overall_purchase += $row['total_purchase'];

            $gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
            $gstvalue    = $row['order_amount'] * $gsttaxvalue / 100;
            $totalvalue  = $gstvalue + $row['order_amount'];
            $overall_order_amount    += $row['order_amount'];
            $overall_amount_invoiced += $row['amount_invoiced'];
            $overall_gst_amount      += $gstvalue;
            $gstvalue    = number_format($gstvalue,2);
            $totalvalue  = number_format($totalvalue,2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount_invoiced']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $gstvalue);
        }
        $overall_order_amount   = number_format($overall_order_amount, 2);
        $overall_amount_invoiced = number_format($overall_amount_invoiced, 2);
        $overall_gst_amount  = number_format($overall_gst_amount, 2);

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Overall Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_order_amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_amount_invoiced);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_gst_amount);

        $actSheet->getStyle("A{$rowc}:E{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}