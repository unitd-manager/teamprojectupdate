<?
class CP_Admin_Widgets_Tradingsg_QuoteByStaff_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT q.*
			  ,c.company_name
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM `quote` q
        LEFT JOIN staff s ON (s.staff_id = q.staff_id)
        LEFT JOIN company c ON (c.company_id = q.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'e';

        $staff_id  = $fn->getReqParam('staff_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        // JUST HIDE THE CONDITION BY THAMIM

        /*if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }*/

		if ($start_date != '' && $end_date == '') {
	        $searchVar->sqlSearchVar[] = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
	        $searchVar->sqlSearchVar[] = "e.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
        }

        $searchVar->sortOrder = 'q.quote_date DESC';

        if ($staff_id != '' ) {
            $searchVar->sqlSearchVar[] = "s.staff_id = {$staff_id}";
        }

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_quoteByStaff');

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

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "QuoteByStaff_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
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

        $staff_id  = $fn->getReqParam('staff_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

		if ($start_date != '' && $end_date == '') {
	        $quoteDate = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
	        $quoteDate = "e.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $quoteDate = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $quoteDate = "";
        }

		$staffId = '';
        if ($staff_id != '' ) {
        	if ($quoteDate == '') {
            	$staffId = "s.staff_id = {$staff_id}";
        	} else {
            	$staffId = "AND s.staff_id = {$staff_id}";
        	}
        }


        $SQL = "
        SELECT q.*
			  ,c.company_name
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM `quote` q
        LEFT JOIN staff s ON (s.staff_id = q.staff_id)
        LEFT JOIN company c ON (c.company_id = q.company_id)
        WHERE
        {$quoteDate}
        {$staffId}
 		ORDER BY q.quote_date DESC
 		";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            $follow_up_date = $fn->getCPDate($row['follow_up_date'],"d-m-Y");

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $follow_up_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}