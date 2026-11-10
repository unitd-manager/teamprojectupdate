<?
class CP_Admin_Widgets_Pms_IncomeExpenses_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
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
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

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

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_incomeExpenses');
        
        
        //Month Name | Income(from receipt) | Expenses ( expense table )
        $months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        
        $dataArr = array();
        foreach ($months as $month) {
            $dataArr[$month] = array('month' => $month, 'income' => '', 'expense' => '');
        }
        
        $currYear = date('Y');
        
        //income
        $SQL = "
        SELECT MONTHNAME(r.date) AS month
              ,SUM(r.amount) AS income
        FROM receipt r
        WHERE YEAR(r.date) = '{$currYear}'
        GROUP BY MONTH(r.date)
        ";
        $result = $db->sql_query($SQL);
        
        $income_sum = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $dataArr[$row['month']]['income'] = $row['income'];
            //$income_sum += $row['income'];
        }
        
        $income_sum = 0;
        $SQL = "
        SELECT MONTHNAME(e.date) AS month
              ,SUM(e.amount) AS income
        FROM expenses e
        WHERE YEAR(e.date) = '{$currYear}'
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
        WHERE YEAR(e.date) = '{$currYear}'
            AND e.type = 'Expense'
        GROUP BY MONTH(e.date)
        ";
        $result = $db->sql_query($SQL);

        $expense_sum = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $dataArr[$row['month']]['expense'] = $row['expense'];
            $expense_sum += $row['expense'];
        }

        $dataArr['Total'] = array(
            'month' => ''
           ,'income' => $income_sum
           ,'expense' => $expense_sum
        );
        $this->dataArray = $dataArr;
        return $dataArr;
    }
    
    function getExportToExcel($dataArray = ''){
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
}