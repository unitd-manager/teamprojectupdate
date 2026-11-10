<?
class CP_Admin_Widgets_AceIms_IncomeExpenses_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        //Please refer the SQL written in geDataArray
        $SQL ="
            SELECT 1
        ";
        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
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
        $db = Zend_Registry::get('db');
        
        $year = $fn->getReqParam('year');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_incomeExpenses');
        
        
        //Month Name | Income(from receipt) | Expenses ( expense table )
        $months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        
        $dataArr = array();
        foreach ($months as $month) {
            $dataArr[$month] = array('month' => $month, 'income' => '', 'expense' => '');
        }
        
        if ($year == '') {
            $year = date('Y');
        }
        
        //income
        $SQL = "
        SELECT MONTHNAME(r.date) AS month
              ,SUM(r.amount) AS income
        FROM receipt r
        WHERE YEAR(r.date) = '{$year}'
        GROUP BY MONTH(r.date)
        ";
        $result = $db->sql_query($SQL);
        
        $income_sum = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $dataArr[$row['month']]['income'] = $row['income'];
        }
        
        $income_sum = 0;
        $SQL = "
        SELECT MONTHNAME(e.date) AS month
              ,SUM(e.amount) AS income
        FROM expenses e
        WHERE YEAR(e.date) = '{$year}'
            AND e.type = 'Income'
        GROUP BY MONTH(e.date)
        ";
        $result = $db->sql_query($SQL);
        
        $income_sum = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $dataArr[$row['month']]['income'] = $dataArr[$row['month']]['income'] + $row['income'];
            $income_sum += $dataArr[$row['month']]['income'] ;
        }
        
        //expense
        $SQL = "
        SELECT MONTHNAME(e.date) AS month
              ,SUM(e.amount) AS expense
        FROM expenses e
        WHERE YEAR(e.date) = '{$year}'
            AND e.type = 'Expense'
        GROUP BY MONTH(e.date)
        ";
        $result = $db->sql_query($SQL);

        $expense_sum = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $dataArr[$row['month']]['expense'] = $row['expense'];
            $expense_sum += $row['expense'];
        }
        
        $expense_sum = number_format($expense_sum, 2);

        $dataArr['Total'] = array(
            'month'   => ''
           ,'income'  => $income_sum
           ,'expense' => $expense_sum
        );
        $this->dataArray = $dataArr;
        return $dataArr;
    }
    
    function getExportToExcelOld($dataArray = ''){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
         
        $fa = array(
              'month'   => $phpExcel->getFldObj('Month')
             ,'income'  => $phpExcel->getFldObj('Income')
             ,'expense' => $phpExcel->getFldObj('Expense')
        );

        $file_name = "IncomeExpense_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
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
        
        $year = $fn->getReqParam('year');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Income_Expense_Report_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Income');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Expense');
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
        
        foreach ($this->dataArray as $month => $row) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $year);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['income']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['expense']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['expense']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}