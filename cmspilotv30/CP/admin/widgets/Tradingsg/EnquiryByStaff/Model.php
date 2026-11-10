<?
class CP_Admin_Widgets_Tradingsg_EnquiryByStaff_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;
		
		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",e.site_id" ;
		}

        $SQL = "
        SELECT e.*
			  ,c.company_name
              {$appendSql}
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM `enquiry` e
        LEFT JOIN staff s ON (s.staff_id = e.staff_id)
        LEFT JOIN company c ON (c.company_id = e.company_id)
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

        $staff_id       = $fn->getReqParam('staff_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_month  = date('m');
        $current_year   = date('Y');

        $location_id    = $fn->getReqParam('location_id');

        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "e.site_id = {$location_id}";
        }

        // JUST HIDE THE CONDITION BY THAMIM

        /*if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }*/

		if ($start_date != '' && $end_date == '') {
	        $searchVar->sqlSearchVar[] = "e.follow_up_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
	        $searchVar->sqlSearchVar[] = "e.follow_up_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "e.follow_up_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date     = $current_year . '-' . $current_month . '-' . '01';
            $end_date       = $current_year . '-' . $current_month . '-' . '31';
            $searchVar->sqlSearchVar[] = "e.follow_up_date BETWEEN '{$start_date}' AND '{$end_date}'";

        }

        $searchVar->sortOrder = 'e.follow_up_date DESC';

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_leadEnquiryByStaff');

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

        $file_name = "EnquiryByStaff_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Comment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
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

        $staff_id       = $fn->getReqParam('staff_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $location_id    = $fn->getReqParam('location_id');
        $current_month = date('m');
        $current_year  = date('Y');


        $appendStaffSQL = '';

        if ($start_date != '' && $end_date == '') {
            $appendFollowUpDateSQL = "e.follow_up_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $appendFollowUpDateSQL = "e.follow_up_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendFollowUpDateSQL = "e.follow_up_date BETWEEN '{$start_date}' AND '{$end_date}'";
       } else {
            $start_date     = $current_year . '-' . $current_month . '-' . '01';
            $end_date       = $current_year . '-' . $current_month . '-' . '31';
            $appendFollowUpDateSQL = "e.follow_up_date BETWEEN '{$start_date}' AND '{$end_date}'";

       }

        if ($staff_id) {
            $appendStaffSQL = " AND s.staff_id = {$staff_id}";
        }

        $appendSql = '';

        if ($location_id != '') {
            $appendSql = " AND e.site_id = {$location_id}";
        }

        $siteTitle = '' ;
        
        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $siteTitle = ",e.site_id" ;
        }
        $count =1;

        $SQL = "
        SELECT e.*
			  ,c.company_name
               {$siteTitle} 
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM `enquiry` e
        LEFT JOIN staff s ON (s.staff_id = e.staff_id)
        LEFT JOIN company c ON (c.company_id = e.company_id)
        WHERE
        {$appendFollowUpDateSQL} 
        {$appendStaffSQL}
        {$appendSql}       
        ORDER BY e.follow_up_date DESC
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

            $follow_up_date = $fn->getCPDate($row['follow_up_date'],"d-m-Y");

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $follow_up_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comments']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);

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