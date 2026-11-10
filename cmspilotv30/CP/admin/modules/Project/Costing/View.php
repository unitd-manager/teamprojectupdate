<?
class CP_Admin_Modules_Project_Costing_View extends CP_Common_Lib_ModuleViewAbstract
{

    //==================================================================//
    function getBulkAdd() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $task_id = $fn->getReqParam('task_id');

        $formAction = "index.php?_spAction=bulkAddSubmit&module={$tv['module']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Prefix', 'prefix')}
                {$formObj->getTBRow('No of Records', 'no_of_records')}
            </fieldset>
            <input type='hidden' name='task_id' value='{$task_id}' />
        </form>
        ";

        return $text;
    }

    //==================================================================//
    function getBulkAddSubmit() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        $task_id = $fn->getReqParam('task_id');
        $prefix = $fn->getReqParam('prefix');
        $no_of_records = $fn->getReqParam('no_of_records');

        for ($i = 1; $i <= $no_of_records ; $i++){
            $fa = array();
            $fa['task_id'] = $task_id;
            $fa['title']   = $prefix . ' ' . $i;
            $fa['status']  = 'To be Started';
            $fa['sort_order'] =  $fn->getNextSortOrder('task_history', "task_id={$task_id}");
            $id = $fn->addRecord($fa);
        }

        return $validate->getSuccessMessageXML();
    }

    //==================================================================//
    function getExportCosting(){
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

        $project_id     = $fn->getReqParam('project_id', '', true);
        $opportunity_id = $fn->getReqParam('opportunity_id', '', true);

        if ($project_id != ''){
            $rec = $fn->getRecordRowByID('project', 'project_id', $project_id);
            $title = $rec['title'];
        } else {
            $rec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
            $title = $rec['title'];
        }

        $file_name = $title . "-Costing.xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        /******************** FORMAT HEADER *******************/
        $reportHeader = array(
            'font' => array(
                'bold' => true
               ,'size' => 16
            )
        );

        $headStyle = array(
            'font' => array(
                'bold' => true
               ,'size' => 12
               ,'color' => array('rgb' => 'ffffff')
            ),
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_SOLID,
        		'startcolor' => array(
        			'rgb' => '000000',
        		),
        	),
        );

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Estimated Hours for: ' . $title);
        $actSheet->mergeCells("A1:D1");
        $actSheet->getStyle("A1")->applyFromArray($reportHeader);

        $actSheet->getColumnDimension('A')->setWidth(20);
        $actSheet->getColumnDimension('B')->setWidth(20);
        $actSheet->getColumnDimension('C')->setWidth(50);
        $actSheet->getColumnDimension('D')->setWidth(15);

        $rowc++;
        $colc = 0;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Section');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Category');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Description');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Hours');

        $actSheet->getStyle('D2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A2:{$lastCol}2")->applyFromArray($headStyle);

        if ($project_id != ''){
            $appendSQL = "c.project_id = {$project_id}";
        } else {
            $appendSQL = "c.opportunity_id = {$opportunity_id}";
        }

        $SQL = "
        SELECT c.*
        FROM costing c
        WHERE {$appendSQL}
        ORDER BY c.sort_order
        ";

        $result = $db->sql_query($SQL);
        $total = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['section']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['category']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##0.00');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['hours']);

            $total += $row['hours'];
        }

        $rowc++;
        $actSheet->setCellValueByColumnAndRow(0, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow(3, $rowc, $total);

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}
