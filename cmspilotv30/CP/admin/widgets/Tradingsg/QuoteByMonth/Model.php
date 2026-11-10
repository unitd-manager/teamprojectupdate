<?
class CP_Admin_Widgets_Tradingsg_QuoteByMonth_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;
		
		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",q.site_id" ;
		}

        $SQL = "
        SELECT q.title
              ,c.company_name
              ,q.status
              {$appendSql}
              ,(SELECT SUM(qp.selling_price * qp.qty) FROM quote_product qp
                WHERE q.quote_id = qp.quote_id
                ) AS quote_total_amount
        FROM quote q
        LEFT JOIN (company c) ON (q.company_id = c.company_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'q';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');

        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "q.site_id = {$location_id}";
        }

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "q.quote_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $searchVar->sqlSearchVar[] = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $searchVar->sortOrder = 'company_name ASC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_quoteByMonth');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    /*function getExportToExcel($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $fa = array(
              'title'               => $phpExcel->getFldObj('Title')
             ,'company_name'        => $phpExcel->getFldObj('Client')
             ,'status'              => $phpExcel->getFldObj('Status')
             ,'quote_total_amount'  => $phpExcel->getFldObj('Amount')
        );

        $file_name = "QuoteByMonth_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }*/
    
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

        $file_name = "QuoteByMonth" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        if($cpCfg['cp.hasMultiUniqueSites']){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
         }   
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

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $month      	= $fn->getReqParam('month');
        $year       	= $fn->getReqParam('year');
        $location_id    = $fn->getReqParam('location_id');
        $current_date 	= date('Y-m-d');


        if ($start_date != '' && $end_date == '') {
            $quoteDate = "q.quote_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $quoteDate = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $quoteDate = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $quoteDate = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $siteTitle = '' ;       
        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $siteTitle = ",q.site_id" ;
        }

		$appendSql = '';
        if ($location_id != '') {
            $appendSql = "AND q.site_id = {$location_id}";
        }

        $payment_total = '';
        $count = 1;

        $SQL = "
		SELECT q.title
              ,c.company_name
               {$siteTitle} 
              ,q.status
              ,(SELECT SUM(qp.selling_price) FROM quote_product qp
                WHERE q.quote_id = qp.quote_id
                ) AS quote_total_amount
        FROM quote q
        LEFT JOIN (company c) ON (q.company_id = c.company_id)
     	WHERE
     	{$quoteDate} 
     	{$appendSql}
 		ORDER BY company_name ASC
		";
		
		$result = $db->sql_query($SQL);
		
        while ($row = $db->sql_fetchrow($result)) {

            if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
                $siteRecSql ="
                SELECT s.title 
                FROM site s
                WHERE s.site_id = {$row['site_id']}
                ";

                $resultSiteLocation = $db->sql_query($siteRecSql);
                $rowSite            = $db->sql_fetchrow($resultSiteLocation);
             }   


            $colc = 0;
            $rowc++;

            $payment_total += $row['quote_total_amount'];

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
            }   
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['quote_total_amount']);
            $count++;

        }

        $colc = 0;
        $rowc++;

        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        }   
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    
}