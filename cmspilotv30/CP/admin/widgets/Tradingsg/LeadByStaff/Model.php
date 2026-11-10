<?
class CP_Admin_Widgets_Tradingsg_LeadByStaff_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
	    $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;
		
		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",c.site_id" ;
		}

        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
              {$appendSql}
        FROM call_registry c
        LEFT JOIN staff s ON (s.staff_id = c.staff_id)
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

        $staff_id  = $fn->getReqParam('staff_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');
        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "c.site_id = {$location_id}";
        }

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "c.contact_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $searchVar->sqlSearchVar[] = "c.contact_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "c.contact_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "c.contact_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_leadByStaff');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     */
    function getExportToExcel12($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');


        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $fa = array(
              'contactDate'               => $phpExcel->getFldObj('Date')
             ,'company_name'        => $phpExcel->getFldObj('Client')
             ,'comments'              => $phpExcel->getFldObj('Meeting Notes')
             ,'staff_name'  => $phpExcel->getFldObj('Staff')
        );

        $file_name = "LeadByStaff_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
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

        $staff_id       = $fn->getReqParam('staff_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $month          = $fn->getReqParam('month');
        $year           = $fn->getReqParam('year');
        $location_id    = $fn->getReqParam('location_id');
        $current_date   = date('Y-m-d');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "LeadByStaff__" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Meeting Notes');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff');
        if($cpCfg['cp.hasMultiUniqueSites']){
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

        if ($start_date != '' && $end_date == '') {
            $contactDate = "(c.contact_date BETWEEN '{$start_date}' AND '{$current_date}')";
        } else if ($start_date == '' && $end_date != ''){
            $contactDate = "(c.contact_date BETWEEN '{$start_date}' AND '{$end_date}')";
        } else if ($start_date != '' && $end_date != '') {
            $contactDate = "(c.contact_date BETWEEN '{$start_date}' AND '{$end_date}')";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $contactDate = "(c.contact_date BETWEEN '{$start_date}' AND '{$end_date}')";
        }

		$staffId = '';
        if ($staff_id != '' ) {
            $staffId = "AND s.staff_id = {$staff_id}";
        }

        $appendSql = '';
        if ($location_id != '') {
            $appendSql = "AND c.site_id = {$location_id}";
        }

        $siteTitle = '' ;       
        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $siteTitle = ",c.site_id" ;
        }

        $count = 1;

        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
               {$siteTitle} 
        FROM call_registry c
        LEFT JOIN staff s ON (s.staff_id = c.staff_id)
        WHERE
        {$contactDate}
        {$staffId}
        {$appendSql}
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

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comments']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_name']);

            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
            }   
            $count++;

        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}