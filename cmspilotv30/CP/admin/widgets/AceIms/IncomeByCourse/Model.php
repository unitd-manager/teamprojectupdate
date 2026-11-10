<?
class CP_Admin_Widgets_AceIms_IncomeByCourse_Model extends CP_Common_Lib_WidgetModelAbstract
{
    //Course Name |Total No of Students| Income | Paid | Due
    function getSQL(){
        $fn = Zend_Registry::get('fn');

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $sqlAppend = '';
        if ($start_date != '') {
            $sqlAppend .= " AND od.order_date >= '{$start_date}'";
        }
        if ($end_date != '') {
            $sqlAppend .= " AND od.order_date <= '{$end_date}'";
        }

        $SQL = "
        SELECT DISTINCT c.course_id 
             , c.title as course_title
             , ABS( ABS( SUM( oi.unit_price ) ) ) AS net_total
             , c.title as course_title
			 , (SELECT COUNT(*)
				FROM course_contact cc2
				LEFT JOIN (`order` od) ON (cc2.order_id = od.order_id)
				WHERE cc2.course_id = c.course_id
				{$sqlAppend}
				) AS course_contact_count
			  ,o.order_date
			  ,oi.order_id
        FROM `order` o
        LEFT JOIN order_item oi ON oi.order_id = o.order_id
        LEFT JOIN course c ON c.course_id = oi.record_id
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $searchVar->mainTableAlias = 'o';

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}'";
        }
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date <= '{$end_date}'";
        }

        $searchVar->sqlSearchVar[] = "oi.item_title != 'Discount'";

        $searchVar->groupBy = 'c.course_id';
        $searchVar->sortOrder = 'course_title';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_incomeByCourse');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     *
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

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "IncomeByCourse_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Number of Students');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
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
        
        $sqlAppend = '';
        $sqlAppendCount = '';
        if ($start_date != '' && $end_date != '') {
            $sqlAppend = " AND o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
            $sqlAppendCount = " AND od.order_date >= '{$start_date}' AND od.order_date <= '{$end_date}'";
        }

        $SQL = "
        SELECT DISTINCT c.course_id 
             , c.title as course_title
             , ABS( ABS( SUM( oi.unit_price ) ) ) AS net_total
             , c.title as course_title
			 , (SELECT COUNT(*)
				FROM course_contact cc2
				WHERE cc2.course_id = c.course_id
				) AS course_contact_count
			  ,o.order_date
        FROM `order` o
        LEFT JOIN order_item oi ON oi.order_id = o.order_id
        LEFT JOIN course c ON c.course_id = oi.record_id
        {$sqlAppend}
        GROUP BY c.course_id
        ORDER BY course_title
        ";
        
        $SQL = "
        SELECT DISTINCT c.course_id 
             , c.title as course_title
             , ABS( ABS( SUM( oi.unit_price ) ) ) AS net_total
             , c.title as course_title
			 , (SELECT COUNT(*)
				FROM course_contact cc2
				LEFT JOIN (`order` od) ON (cc2.order_id = od.order_id)
				WHERE cc2.course_id = c.course_id
				{$sqlAppendCount}
				) AS course_contact_count
			  ,o.order_date
			  ,oi.order_id
        FROM `order` o
        LEFT JOIN order_item oi ON oi.order_id = o.order_id
        LEFT JOIN course c ON c.course_id = oi.record_id
        WHERE o.contact_id != ''
        {$sqlAppend}
          AND oi.item_title != 'Discount'
        GROUP BY c.course_id
        ORDER BY course_title
        ";
        $result = $db->sql_query($SQL);
        $current_date = date("Ym") . '01';

        while ($row = $db->sql_fetchrow($result)) {

            $discount_amount = $this->view->getCalculateDiscountAmount($row['order_id'], $row['net_total']);
            $net_total = number_format(($row['net_total'] - $discount_amount), 2);
            
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_contact_count']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $net_total);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}