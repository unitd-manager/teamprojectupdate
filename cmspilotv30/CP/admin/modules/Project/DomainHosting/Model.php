<?
class CP_Admin_Modules_Project_DomainHosting_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT dh.*
      	      ,c.company_name
        FROM renewals dh
        LEFT JOIN company c ON (dh.company_id = c.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'dh';

        $domain_hosting_id  = $fn->getReqParam('domain_hosting_id');
		$company_id 		= $fn->getReqParam('company_id');
		$renewal_type 		= $fn->getReqParam('renewal_type');
		$registrar 			= $fn->getReqParam('registrar');
		$server_name 		= $fn->getReqParam('server_name');
		$currency 			= $fn->getReqParam('currency');
		$auto_reminder 		= $fn->getReqParam('auto_reminder');
		$status 			= $fn->getReqParam('status');
        $end_date1          = $fn->getReqParam('end_date_1');
        $end_date2          = $fn->getReqParam('end_date_2');
        $renewal_status     = $fn->getReqParam('renewal_status');

        if ($domain_hosting_id != "") {
            $searchVar->sqlSearchVar[] = "dh.renewal_id = '{$domain_hosting_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "dh.renewal_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'dh.renewal_id');

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "dh.company_id = '{$company_id}'";
            }

            if ($renewal_type != "") {
                $searchVar->sqlSearchVar[] = "dh.renewal_type = '{$renewal_type}'";
            }

            if ($registrar != "") {
                $searchVar->sqlSearchVar[] = "dh.registrar = '{$registrar}'";
            }

            if ($server_name != "") {
                $searchVar->sqlSearchVar[] = "dh.server_name = '{$server_name}'";
            }

            if ($currency != "") {
                $searchVar->sqlSearchVar[] = "dh.currency = '{$currency}'";
            }

			if ($auto_reminder == 'Yes') {
                $searchVar->sqlSearchVar[] = "dh.auto_reminder = 1";
			}

			if ($auto_reminder == 'No') {
                $searchVar->sqlSearchVar[] = "dh.auto_reminder = 0";
			}

            if ($end_date1 != "" && $end_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(dh.end_date BETWEEN '{$end_date1}' AND '{$end_date2}')";
            }

            /*if ($renewal_status != "") {
                $searchVar->sqlSearchVar[] = "dh.renewal_status = '{$renewal_status}'";
            }*/

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
	                  c.company_name  LIKE '%{$tv['keyword']}%'

                )";
            }

            if ($renewal_status == ""){
                $searchVar->sqlSearchVar[] = "dh.renewal_status IN ('Due', 'Late')";
            }else{
                $searchVar->sqlSearchVar[] = "dh.renewal_status = '{$renewal_status}'";
            }

            $searchVar->sortOrder = "c.company_name ASC";
        }

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('renewal_type', 'Please select the renewal type');
        $validate->validateData('company_id', 'Please select the company name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'renewal_type');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'domain');
        $fa = $fn->addToFieldsArray($fa, 'registrar');
        $fa = $fn->addToFieldsArray($fa, 'server_name');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'amount_for_domain');
        $fa = $fn->addToFieldsArray($fa, 'amount_for_dns');
        $fa = $fn->addToFieldsArray($fa, 'auto_reminder');
        $fa = $fn->addToFieldsArray($fa, 'renewal_status');
        $fa = $fn->addToFieldsArray($fa, 'remind_to');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');

        return $fa;
    }

     /**
     *
     */
    function getExportData1(){
        $db = Zend_Registry::get('db');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Timesheet-" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");;
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Renewal Type");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Company Name");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Domain");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Start Date");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "End Date");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Registrar");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Server Name");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Currency");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Amount");

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

        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['renewal_type']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['domain']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['start_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['end_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registrar']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['server_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['currency']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount']);

        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
             'renewal_id'      => $phpExcel->getFldObj('Renewal ID')
            ,'renewal_type'    => $phpExcel->getFldObj('Renewal Type')
            ,'company_name'    => $phpExcel->getFldObj('Company Name')
            ,'domain'          => $phpExcel->getFldObj('Domain')
            ,'start_date'      => $phpExcel->getFldObj('Start Date')
            ,'end_date'        => $phpExcel->getFldObj('End Date')
            ,'registrar'       => $phpExcel->getFldObj('Registrar')
            ,'server_name'     => $phpExcel->getFldObj('Server Name')
            ,'currency'        => $phpExcel->getFldObj('Currency')
            ,'amount'          => $phpExcel->getFldObj('Amount')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }


}


