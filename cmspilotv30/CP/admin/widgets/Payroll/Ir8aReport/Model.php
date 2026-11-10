<?
class CP_Admin_Widgets_Payroll_Ir8aReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DISTINCT pm.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
              ,e.work_permit
              ,e.date_of_birth AS dob
              ,e.status
              ,e.ir21_filed
        FROM employee e
        LEFT JOIN (payroll_management pm) ON (e.employee_id = pm.employee_id)
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
        $searchVar->mainTableAlias = 'e';

        $searchVar->sqlSearchVar[] = "pm.payroll_year = '{$cpCfg['cp.ir8aFormForYear']}'";
        $searchVar->sqlSearchVar[] = "(pm.status = 'Generated' OR pm.status = 'Approved' OR pm.status = 'Paid')";
        $searchVar->sortOrder = "e.first_name ASC";
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_ir8aReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $tv       = Zend_Registry::get('tv');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');
        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "IR8AReport_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $styleTopHeader = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array('bold' => true, 'size' => 16)
        );
        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $row  = 1;
        $actSheet = &$objPHPExcel->getActiveSheet();
        $actSheet->mergeCells("A{$rowc}:K{$rowc}");
        $actSheet->getStyle("A{$rowc}:K{$rowc}")->applyFromArray($styleTopHeader);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "IR8A Report for {$cpCfg['cp.ir8aFormForYear']}");

        $colc = 0;
        $rowc++;
        $actSheet->getStyle("A{$rowc}:K{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'IR 21 Filed');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date of Birth');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC/ FIN/ WP');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Payroll months');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Gross Salary');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Transport Allowance');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Entertainment Allowance');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Other Allowance');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Overall Income');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Gross Employee CPF');
       /******************** FORMAT HEADER *******************/
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND e.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT DISTINCT pm.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
              ,e.work_permit
              ,e.date_of_birth AS dob
              ,e.status
              ,e.ir21_filed
        FROM employee e
        LEFT JOIN (payroll_management pm) ON (e.employee_id = pm.employee_id)
        WHERE pm.payroll_year = '{$cpCfg['cp.ir8aFormForYear']}'
          AND (pm.status = 'Generated' OR pm.status = 'Approved' OR pm.status = 'Paid')
          {$appendSqlSite}
        ORDER BY e.first_name ASC
        ";
        $result = $db->sql_query($SQL);
        $count = 1;
        $overall_income = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $dob = $fn->getCPDate($row['dob'], 'd-m-Y');

            /*if ($row['citizen'] == 'SP' || $row['citizen'] == 'WP'){
                $id_no = $row['work_permit'];
            } else
            */
            
            if ($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
                $id_no = $row['nric_no'];
            } else {
                $id_no = $row['fin_no'];
            }

            $appendSqlSite1 = "";
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite1 = "AND site_id = {$cpSiteIdSession}";
            }

            $sqlPmMonth = "
            SELECT payroll_month
                  ,basic_pay
                  ,cpf_employee
                  ,ot_amount
                  ,allowance1
                  ,allowance2
                  ,allowance3
                  ,allowance4
                  ,allowance5
            FROM payroll_management
            WHERE payroll_year = '{$cpCfg['cp.ir8aFormForYear']}'
              AND employee_id = '{$row['employee_id']}'
              {$appendSqlSite1}
            ";
            $resultPmMonth = $db->sql_query($sqlPmMonth);
            $numRowsPmMonth = $db->sql_numrows($resultPmMonth);
            $countPmMonth = 1;
            $total_amount = 0;
            $total_allowance1 = 0;
            $total_allowance2 = 0;
            $total_allowance345 = 0;
            $total_cpf_amount = 0;
            $payroll_months_display = '';
            while ($rowPmMonth = $db->sql_fetchrow($resultPmMonth)) {
                if ($countPmMonth == $numRowsPmMonth) {
                    $payroll_months_display .= $rowPmMonth['payroll_month'];
                } else {
                    $payroll_months_display .= $rowPmMonth['payroll_month'] . ', ';
                }
                $countPmMonth++;
                $total_amount += $rowPmMonth['basic_pay'] + $rowPmMonth['ot_amount'];
                $total_allowance1 += $rowPmMonth['allowance1'];
                $total_allowance2 += $rowPmMonth['allowance2'];
                $total_allowance345 += $rowPmMonth['allowance3'] + $rowPmMonth['allowance4'] + $rowPmMonth['allowance5'];
                $total_cpf_amount += $rowPmMonth['cpf_employee'];
            }

            $overall_income = $total_amount + $total_allowance1 + $total_allowance2 + $total_allowance345;

            $ir21_filed = '';
            if ($row['status'] == 'Archive' && $row['ir21_filed'] == 1){
                $ir21_filed = 'Yes';
            }

            $colc = 0;
            $rowc++;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $ir21_filed);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $dob);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $id_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payroll_months_display);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_allowance1);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_allowance2);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_allowance345);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_income);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_cpf_amount);
            $count++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:K{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}