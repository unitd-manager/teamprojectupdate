<?
class CP_Admin_Modules_Project_ThirdPartyCost_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT tp.*
              ,p.title AS project_title
              ,p.project_code
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS contact_name
              ,p.project_commission
        FROM third_party_cost tp
        LEFT JOIN (project p) ON (tp.project_id = p.project_id)
        LEFT JOIN (staff s)   ON (p.project_manager_id = s.staff_id)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $item_title = $fn->getReqParam('item_title');
        $third_party_cost_id = $fn->getReqParam('third_party_cost_id');

        if ($third_party_cost_id != "") {
            $searchVar->sqlSearchVar[] = "tp.third_party_cost_id   = {$third_party_cost_id}";
        }

        if ($item_title != "") {
            $searchVar->sqlSearchVar[] = "tp.item_title = '{$item_title}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                tp.item_title     LIKE '%{$tv['keyword']}%'
                OR p.title        LIKE '%{$tv['keyword']}%'
                OR p.project_code LIKE '%{$tv['keyword']}%'
            )";
        }
    }

    /**
     *
     */
    function getExportData() {
        $db = Zend_Registry::get('db');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "ThirdParty_" . date("d-m-Y") . ".xls";

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header("Content-Disposition: attachment;filename={$file_name}");
        header('Content-Transfer-Encoding: binary');

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Manager');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Item Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Budget Amount Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Actual Amount');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array( 'bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $SQL     = "SELECT DISTINCT project_id FROM third_party_cost a ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $project_id = $row['project_id'];

            $SQLQuery = "
            SELECT a.*
                  ,b.title AS project_title
                  ,b.project_code
                  ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
                  ,b.project_commission
            FROM third_party_cost a
            LEFT JOIN (project b) ON (a.project_id         = b.project_id)
            LEFT JOIN (staff c)   ON (b.project_manager_id = c.staff_id)
            WHERE a.project_id = {$project_id}
            ";
            $resultQuery = $db->sql_query($SQLQuery);
            $numRows     = $db->sql_numrows($resultQuery);
            $count       = 0;
            $budget_amount = 0;
            $actual_amount = 0;

            while ($rowQuery = $db->sql_fetchrow($resultQuery)) {
                $colc = 0;
                $rowc++;
                if ($count == 0) {
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['project_title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['project_code']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['contact_name']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['item_title']);

                    $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
                    $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['budget_amount']);

                    $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
                    $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['actual_amount']);
                } else {
                    $colc = 3;
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['item_title']);

                    $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
                    $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['budget_amount']);

                    $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
                    $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['actual_amount']);
                }

                $budget_amount = $budget_amount  + $rowQuery['budget_amount'];
                $actual_amount = $actual_amount  + $rowQuery['actual_amount'];
                $count++;

                if ($numRows == $count && $numRows > 1) {
                    $colc = 3;
                    $rowc++;
                    $colStr  = PHPExcel_Cell::stringFromColumnIndex($colc);
                    $colStr2 = PHPExcel_Cell::stringFromColumnIndex($colc+3);
                    $actSheet->getStyle("{$colStr}{$rowc}:{$colStr2}{$rowc}")->applyFromArray($headStyle);
                    $actSheet->getStyle("{$colStr}{$rowc}:{$colStr2}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  "Sub-total for 3rd parties cost");
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $budget_amount);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $actual_amount);

                    $colc = 3;
                    $rowc++;

                    $actSheet->getStyle("{$colStr}{$rowc}:{$colStr2}{$rowc}")->applyFromArray($headStyle);
                    $actSheet->getStyle("{$colStr}{$rowc}:{$colStr2}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  "Commission");
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['project_commission']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $rowQuery['project_commission']);

                    $colc = 3;
                    $rowc++;

                    $actSheet->getStyle("{$colStr}{$rowc}:{$colStr2}{$rowc}")->applyFromArray($headStyle);
                    $actSheet->getStyle("{$colStr}{$rowc}:{$colStr2}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  "Net 3rd parties cost");
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $budget_amount + $rowQuery['project_commission']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $actual_amount + $rowQuery['project_commission']);
                    $rowc++;
                }
            }
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

    }

    /**
     *
     */
    function getExportData1($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'project_opp_code'        => $phpExcel->getFldObj('Project Title')
             ,'title'                   => $phpExcel->getFldObj('Project Code')
             ,'project_opp_title'       => $phpExcel->getFldObj('Project Manager')
             ,'project_or_opp'          => $phpExcel->getFldObj('Item Title')
             ,'staff_names'             => $phpExcel->getFldObj('Budget Amount Name')
             ,'due_date'                => $phpExcel->getFldObj('Actual Amount')
        );
        
        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }       
}
