<?
class CP_Admin_Widgets_Tradingsg_InvoicesForVatByClient_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;
		
		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",o.site_id" ;
		}

        $SQL = "
        SELECT i.*
        	  ,c.company_id
              ,c.company_name
              ,SUM(i.invoice_amount) AS company_invoice_amount
              {$appendSql}
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id   = o.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
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

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $company_id   = $fn->getReqParam('company_id');
        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "o .site_id = {$location_id}";
        }

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        $searchVar->sqlSearchVar[] = "(i.status = 'Paid' OR i.status='Due' OR i.status = 'Late')";

		if ($company_id != '') {
			$searchVar->sqlSearchVar[] = "c.company_id = {$company_id}" ;
		}

        if($cpCfg['cp.excludeStock'] == 1){
            $searchVar->sqlSearchVar[] = "o.link_stock = 1";
        }

        $searchVar->sqlSearchVar[] = "c.company_id != ''";
        $searchVar->sqlSearchVar[] = "i.vat = 1" ;

        $searchVar->groupBy = 'c.company_id';
        $searchVar->sortOrder = 'company_name';


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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_invoiceByClient');

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

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "VATInvoicesByClient_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
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

        $start_date 	= $fn->getReqParam('start_date');
        $end_date     	= $fn->getReqParam('end_date');
        $company_id   	= $fn->getReqParam('company_id');

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $invoiceDate = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";

		$companyId = '';

		if ($company_id != '') {
			$companyId = "AND c.company_id = {$company_id}" ;
		}

        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $appendSql = ",o.site_id" ;
        }

        $location_id = $fn->getReqParam('location_id');
        $Location = '';
        if ($location_id != '') {
            $Location = "AND o .site_id = {$location_id}";
        }

        $SQL = "
        SELECT i.*
        	  ,c.company_id
              ,c.company_name
              ,SUM(i.invoice_amount) AS company_invoice_amount
              {$appendSql}
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id   = o.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
		WHERE
		{$invoiceDate}
 		AND (i.status = 'Paid' OR i.status='Due' OR i.status = 'Late')
        AND c.company_id != ''
        AND i.vat = 1
 		{$companyId}
        {$linkToStock}
        {$Location}
        GROUP BY c.company_id
        ORDER BY company_name
        ";

        $result = $db->sql_query($SQL);

		$totalPayment = 0;
        $serialNo = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

			$totalPayment += $row['company_invoice_amount'];

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serialNo);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($row['company_invoice_amount'],2));
            if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
                $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $siteRec['title']); 
            }

            $serialNo++;
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($totalPayment,2));

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}