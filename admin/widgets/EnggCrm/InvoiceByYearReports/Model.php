<?
class CPL_Admin_Widgets_EnggCrm_InvoiceByYearReports_Model extends CP_Admin_Widgets_EnggCrm_InvoiceByYearReports_Model
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
              ,(SUM(i.invoice_amount + 
                        ((i.invoice_amount * i.gst_percentage) / 100)
                    )
                ) AS invoice_amount_yearly
        FROM invoice i
        LEFT JOIN `order` o   ON (o.order_id   = i.order_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;        
        $searchVar->mainTableAlias = 'i';

        $record_type = $fn->getReqParam('record_type');

        $searchVar->sqlSearchVar[] = "o.record_type = '{$record_type}'";
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->groupBy = "DATE_FORMAT(i.invoice_date, '%Y')";

    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_invoiceByYearReports');

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

        $record_type = $fn->getReqParam('record_type');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "InvoiceByYear_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Year');
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
        SELECT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
              ,(SUM(i.invoice_amount + 
                        ((i.invoice_amount * i.gst_percentage) / 100)
                    )
                ) AS invoice_amount_yearly
        FROM invoice i
        LEFT JOIN `order` o   ON (o.order_id   = i.order_id)
        WHERE o.record_type = '{$record_type}'
          AND i.status != 'Cancelled'
        GROUP BY DATE_FORMAT(i.invoice_date, '%Y')
         ";
        $result = $db->sql_query($SQL);
        $invoice_amount_yearly = 0;

        while ($row = $db->sql_fetchrow($result)) {

            $fraction_length = strlen(substr(strrchr($row['invoice_amount_yearly'], "."), 1)); // Checking the lingth of the fraction value
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $row['invoice_amount_yearly']);

                /* Checking whether 3rd decimal point is more than or equal to 5
                   If Yes, add 1 to 2nd decimal point
                 */
                $totalDecimalMore = substr($fraction, 2, 1);

                $fraction = substr($fraction, 0, 2);
                if ($totalDecimalMore >= 5) {
                    $fraction = $fraction + 1;
                }

                $fraction = substr($fraction, 0, 2);
                $invoice_amount_yearly = $integer . "." . $fraction;
            }

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_year']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount_yearly);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}