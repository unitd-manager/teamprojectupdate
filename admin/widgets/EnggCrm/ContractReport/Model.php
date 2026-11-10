<?
class CPL_Admin_Widgets_EnggCrm_ContractReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT 
            v.*, 
            c.first_name AS contact_names, 
            b.company_name, 
            c.phone, 
            (
                SELECT sr.shop
                FROM shop_renewal sr
                WHERE sr.renewal_id = v.renewal_id
                LIMIT 1
            ) AS renewal_shop,
            (
                SELECT sr.location
                FROM shop_renewal sr
                WHERE sr.renewal_id = v.renewal_id
                LIMIT 1
            ) AS renewal_location
        FROM renewal v
        LEFT JOIN company b ON v.company_id = b.company_id
        LEFT JOIN contact c ON v.contact_id = c.contact_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'v';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $company_id = $fn->getReqParam('company_id');

        $current_date = date('Y-m-d');

        /*if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
            $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
            $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$current_date}'";
        }*/

        if ($company_id != '') {
            $searchVar->sqlSearchVar[] = "v.company_id = '{$company_id}'";
        }

        $searchVar->sortOrder = "v.renewal_id DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_overallSalesSummary');

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
        $company_id = $fn->getReqParam('company_id');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Overall_Sales_Summary__" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Contract No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Shop Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Start Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'End Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Renewal Due');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Value');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Service Due');
        
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
        /*if ($start_date != '' && $end_date == '') {
            $appendSql .= " AND i.invoice_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
            $appendSql .= " AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql .= " AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
            $appendSql .= " AND i.invoice_date BETWEEN '{$start_date}' AND '{$current_date}'";
        }*/

        if ($company_id != '') {
            $appendSql .= " AND v.company_id = '{$company_id}'";
        }

        $SQL = "
        SELECT 
            v.*, 
            c.first_name AS contact_names, 
            b.company_name, 
            c.phone, 
            (
                SELECT sr.shop
                FROM shop_renewal sr
                WHERE sr.renewal_id = v.renewal_id
                LIMIT 1
            ) AS renewal_shop,
            (
                SELECT sr.location
                FROM shop_renewal sr
                WHERE sr.renewal_id = v.renewal_id
                LIMIT 1
            ) AS renewal_location
        FROM renewal v
        LEFT JOIN company b ON v.company_id = b.company_id
        LEFT JOIN contact c ON v.contact_id = c.contact_id
        WHERE v.renewal_id != '' 
        {$appendSql}
        ORDER BY v.renewal_id DESC
        ";
        $result = $db->sql_query($SQL);

        $overall_sales       = 0;
        $overall_purchase    = 0;
        $gstAmount           = 0;
        $totAlamount         = 0;
        $totalPurchaseAmount = 0;
        $overall_Discount    = 0;
        $profit              = 0;
        $overall_gst         = 0;
        $overall_profit      = 0;
        $appendSqlOther = '';
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $current_date = date('Y-m-d');
            $start_date = $fn->getCPDate($row['start_date'], 'd-m-Y');
            $end_date = $fn->getCPDate($row['end_date'], 'd-m-Y');
            $renewal_due = $fn->getCPDate($row['renewal_due'], 'd-m-Y');

            $renewalDate = new DateTime($row['renewal_due']);
            $currentDate = new DateTime();
            $renewalInterval = $renewalDate->diff($currentDate)->days;
    
            $highlightRenewalDue = "";
            if ($renewalDate > $currentDate && $renewalInterval <= 30) {
                $highlightRenewalDue = "style='background-color: yellow; font-weight: bold;'";
            }

            $latestRenewal = $fn->getRecordByCondition('service_renewal', "renewal_id = '{$row['renewal_id']}' ORDER BY service_renewal_id DESC");
            $highlightRow = false;
            $serviceDueText = "No"; // Default text for service due column
            if ($latestRenewal && isset($latestRenewal['schedule_date'])) {
                $actualDate = new DateTime($latestRenewal['schedule_date']);
                $now = new DateTime();
                $interval = $now->diff($actualDate)->days;
    
                if ($interval > 92) {
                    $highlightRow = true; // Flag to set row color to pink
                    $serviceDueText = "Yes"; // Change text if condition is met
                }
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['ref_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['renewal_shop']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['renewal_location']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $start_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $end_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $renewal_due);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contract_value']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serviceDueText);
            
            if ($serviceDueText === "Yes") {
                $rowStyle = array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFC0CB') // Light pink color
                    )
                );
                $actSheet->getStyle("A{$rowc}:I{$rowc}")->applyFromArray($rowStyle);
            }
        }
        $colc = 0;
        $rowc++;

        $actSheet->getStyle("A{$rowc}:I{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}