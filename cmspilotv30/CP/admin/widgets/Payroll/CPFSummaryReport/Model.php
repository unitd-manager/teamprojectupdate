<?
class CP_Admin_Widgets_Payroll_CPFSummaryReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT pm.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.nric_no
              ,e.salary
              ,e.date_of_birth AS dob
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'pm';

        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        $searchVar->sqlSearchVar[] = "pm.payroll_month = '{$month}'";
        $searchVar->sqlSearchVar[] = "pm.payroll_year = '{$year}'";
        $searchVar->sqlSearchVar[] = "pm.total_cpf_contribution > 0";
        
        $searchVar->sortOrder = 'e.first_name ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_cPFSummaryReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $tv       = Zend_Registry::get('tv');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $current_date = date('Y-m-d');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "CPFSummaryReport_" . date("d-m-Y") . ".xls";

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
        $row  = 1;
        $actSheet = &$objPHPExcel->getActiveSheet();
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CPF by Employer');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CPF By Employee');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total CPF');
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

        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND pm.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT pm.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.nric_no
              ,e.salary
              ,e.date_of_birth AS dob
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        WHERE pm.total_cpf_contribution > 0
          AND pm.payroll_month = '{$month}'
          AND pm.payroll_year = '{$year}'
          {$appendSqlSite}
        ORDER BY e.first_name ASC
        ";
        $result = $db->sql_query($SQL);
        $count = 1;
        $overall_cpf_employer = 0;
        $overall_cpf_employee = 0;
        $overall_total_cpf = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $overall_cpf_employer += $row['cpf_employer'];
            $overall_cpf_employee += $row['cpf_employee'];
            $overall_total_cpf += $row['total_cpf_contribution'];

            $colc = 0;
            $rowc++;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['nric_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['cpf_employer']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['cpf_employee']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['total_cpf_contribution']);

            $count++;
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_cpf_employer);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_cpf_employee);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_total_cpf);
        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}