<?
class CP_Admin_Widgets_Tradingsg_EnquiryActivityByStaff_Model extends CP_Common_Lib_WidgetModelAbstract
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
			 ,e.staff_id
             ,e.title
              {$appendSql}
             ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM comment c
        LEFT JOIN enquiry e ON (e.enquiry_id = c.record_id )
        LEFT JOIN staff s ON (s.staff_id = c.contact_id)
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
        $searchVar->sqlSearchVar[] = "c.room_name = 'tradingsg_enquiry'";

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "c.site_id = {$location_id}";
        }

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $searchVar->sqlSearchVar[] = "c.comment_date BETWEEN '{$start_date}' AND '{$end_date}'";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_enquiryActivityByStaff');

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

        $staff_id       = $fn->getReqParam('staff_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $location_id    = $fn->getReqParam('location_id');


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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Enquiry Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Activity');
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

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $commentDate = "c.comment_date BETWEEN '{$start_date}' AND '{$end_date}'";

		$staffId = '';
        if ($staff_id != '' ) {
            $staffId = "AND s.staff_id = {$staff_id}";
        }

        $siteTitle = '' ;       
        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $siteTitle = ",c.site_id" ;
        }

        $appendSql = '';
        if ($location_id != '') {
            $appendSql = "AND cs.site_id = {$location_id}";
        }

        $count = 1;

        $SQL = "
        SELECT c.*
			 ,e.staff_id
               {$siteTitle} 
             ,e.title
             ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM comment c
        LEFT JOIN enquiry e ON (e.enquiry_id = c.record_id )
        LEFT JOIN staff s ON (s.staff_id = c.contact_id)
 		WHERE
		{$commentDate}
        {$staffId}
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
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comment_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comments']);
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