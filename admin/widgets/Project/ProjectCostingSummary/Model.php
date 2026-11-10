<?
class CPL_Admin_Widgets_Project_ProjectCostingSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT e.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        
        $searchVar->sqlSearchVar[] = "(e.citizen = 'WP' OR e.citizen = 'SP')";
        $searchVar->sqlSearchVar[] = "e.employee_type = 'In house'";
        
        $searchVar->sortOrder = 'e.first_name ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_employeeSalaryReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $employee_status = $fn->getReqParam('employee_status');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "EmployeeSalaryReport_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fin No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Levy');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Normal Rate');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'OT Rate');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Dorm');
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

        $appendSql = '';       
        $employee_status = $fn->getReqParam('employee_status');

        $SQL = "
        SELECT e.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        WHERE (e.citizen = 'WP' OR e.citizen = 'SP')
        AND e.employee_type = 'In house'
        ORDER BY e.first_name ASC
        ";
        $result = $db->sql_query($SQL);
        $counter = 1;
        $overallTotal = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $start_date = $fn->getReqParam('start_date');
            $end_date   = $fn->getReqParam('end_date');
            $current_date = date('Y-m-d');

            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            if ($start_date != '' && $end_date == '') {
                $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
                $end_date = $current_date;
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $current_date;
                $end_date = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
            } else if ($start_date != '' && $end_date != '') {
                $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
                $end_date = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
            } else {
                $start_date = $current_date;
                $end_date = $current_date;
            }

              //AND (j.act_join_date >= '{$start_date}' AND j.termination_date <= '{$end_date}')
            $sqlJobInfo = "
            SELECT j.*
            FROM job_information j
            WHERE j.employee_id = {$row['employee_id']}
              AND (j.termination_date <= '{$end_date}')
            ORDER BY j.job_information_id DESC
            ";
            $resultJobInfo = $db->sql_query($sqlJobInfo);
            $numRows = $db->sql_numrows($resultJobInfo);
            if($numRows == 0){
                $sqlJobInfo = "
                SELECT j.*
                FROM job_information j
                WHERE j.employee_id = {$row['employee_id']}
                  AND (j.termination_date = '' OR j.termination_date IS NULL)
                ORDER BY j.job_information_id DESC
                ";
                $resultJobInfo = $db->sql_query($sqlJobInfo);                
            }
            $rowJi = $db->sql_fetchrow($resultJobInfo);


            //$basic_pay_formatted = number_format($rowJi['basic_pay'], 2);
            $SQLTimesheet = "
            SELECT *
            FROM employee_timesheet
            WHERE employee_id = {$row['employee_id']}
            AND (date >= '{$start_date}' AND date <= '{$end_date}')
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $client = 0;
            $nr_rate = 0;
            $ot_rate = 0;
            while ($rowTimesheet = $db->sql_fetchrow($resultTimesheet)) {
                $nrRate = $rowTimesheet['employee_hours'] * $rowTimesheet['hourly_rate'];
                $otRate = $rowTimesheet['employee_ot_hours'] * $rowTimesheet['ot_hourly_rate'];
                $phRate = $rowTimesheet['employee_ph_hours'] * $rowTimesheet['ph_hourly_rate'];
                $client += $nrRate + $otRate + $phRate; 
                $nr_rate += $rowTimesheet['employee_hours'];
                $ot_rate += $rowTimesheet['employee_ot_hours'] + $rowTimesheet['employee_ph_hours'];
            }

            $normalRate = (($rowJi['basic_pay'] / 30) / 8) * $nr_rate;
            $overtimeRate = ((($rowJi['basic_pay'] / 30) / 8) * $rowJi['over_time_rate']) * $ot_rate;

            $datetime1  = date_create($start_date); 
            $datetime2  = date_create($end_date); 
            $interval   = date_diff($datetime1, $datetime2); 
            $no_of_days = $interval->format('%a') + 1;
            $levy_amount = ($rowJi['levy_amount'] / 30) * $no_of_days;
            $deduction1 = (($rowJi['deduction1'] + 30) / 30) * $no_of_days;
            $total = $client - ($levy_amount + $normalRate + $overtimeRate + $deduction1);
            $overallTotal += $total;
            
            $client = number_format($client, 2);
            $normalRate = number_format($normalRate, 2);
            $overtimeRate = number_format($overtimeRate, 2);
            $levy_amount = number_format($levy_amount, 2);
            $deduction1 = number_format($deduction1, 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $counter);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $ic_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $client);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $levy_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $normalRate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overtimeRate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $deduction1);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total);

            $counter++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:J{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getCostingSummarySubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id                     = $fn->getPostParam('project_id');
        $po_code                        = $fn->getPostParam('po_code');
        $invoice_code                   = $fn->getPostParam('invoice_code');
        $delivery_date                  = $fn->getPostParam('delivery_date');
        $no_of_worker_used              = $fn->getPostParam('no_of_worker_used');
        $no_of_days_worked              = $fn->getPostParam('no_of_days_worked');
        $labour_rates_per_day           = $fn->getPostParam('labour_rates_per_day');
        $po_price                       = $fn->getPostParam('po_price');
        $invoiced_price                 = $fn->getPostParam('invoiced_price');
        $profit_percentage              = $fn->getPostParam('profit_percentage');
        $po_price_with_gst              = $fn->getPostParam('po_price_with_gst');
        $invoiced_price_with_gst        = $fn->getPostParam('invoiced_price_with_gst');
        $profit                         = $fn->getPostParam('profit');
        $total_material_price           = $fn->getPostParam('total_material_price');
        $transport_charges              = $fn->getPostParam('transport_charges');
        $total_labour_charges           = $fn->getPostParam('total_labour_charges');
        $salesman_commission            = $fn->getPostParam('salesman_commission');
        $finance_charges                = $fn->getPostParam('finance_charges');
        $office_overheads               = $fn->getPostParam('office_overheads');
        $other_charges                  = $fn->getPostParam('other_charges');
        $total_cost                     = $fn->getPostParam('total_cost');
        $transport_charges_percentage   = $fn->getPostParam('transport_charges_percentage');
        $salesman_commission_percentage = $fn->getPostParam('salesman_commission_percentage');
        $finance_charges_percentage     = $fn->getPostParam('finance_charges_percentage');
        $office_overheads_percentage    = $fn->getPostParam('office_overheads_percentage');

        $sketch_arr      = $fn->getPostParam('sketch', array());
        $supplier_id_arr = $fn->getPostParam('supplier_id', array());
        $product_id_arr  = $fn->getPostParam('product_id', array());
        $sub_con_id_arr  = $fn->getPostParam('sub_con_id', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());
        $unit_price_arr  = $fn->getPostParam('unit_price', array());
        $unit_arr        = $fn->getPostParam('unit', array());
        $amount_arr      = $fn->getPostParam('amount', array());

        if (!$this->getCostingSummaryFormValidate()){
            return $validate->getErrorMessageXML();
        }
                
        /* Generation of Invoice record */
        $faInv = array();
        $faInv['po_code']                        = $po_code;
        $faInv['invoice_code']                   = $invoice_code;
        $faInv['delivery_date']                  = $delivery_date;
        $faInv['no_of_worker_used']              = $no_of_worker_used;
        $faInv['no_of_days_worked']              = $no_of_days_worked;
        $faInv['labour_rates_per_day']           = $labour_rates_per_day;
        $faInv['po_price']                       = $po_price;
        $faInv['po_price_with_gst']              = $po_price_with_gst;
        $faInv['invoiced_price']                 = $invoiced_price;
        $faInv['invoiced_price_with_gst']        = $invoiced_price_with_gst;
        $faInv['profit_percentage']              = $profit_percentage;
        $faInv['profit']                         = $profit;
        $faInv['total_material_price']           = $total_material_price;
        $faInv['transport_charges']              = $transport_charges;
        $faInv['total_labour_charges']           = $total_labour_charges;
        $faInv['salesman_commission']            = $salesman_commission;
        $faInv['finance_charges']                = $finance_charges;
        $faInv['office_overheads']               = $office_overheads;
        $faInv['other_charges']                  = $other_charges;
        $faInv['total_cost']                     = $total_cost;
        $faInv['project_id']                     = $project_id;
        $faInv['salesman_commission_percentage'] = $salesman_commission_percentage;
        $faInv['finance_charges_percentage']     = $finance_charges_percentage;
        $faInv['office_overheads_percentage']    = $office_overheads_percentage;
        $faInv['transport_charges_percentage']   = $transport_charges_percentage;
        $faInv['creation_date']                  = date('Y-m-d H:i:s');
        $faInv['created_by']                     = $fn->getSessionParam('userName');
        
        $insertInv  = $dbUtil->getInsertSQLStringFromArray($faInv, 'costing_summary');
        $resultInv  = $db->sql_query($insertInv);
        $costing_summary_id = $db->sql_nextid();

        $total_cost = 0;
        $count = count($product_id_arr);
        for ($i= 0; $i < $count; $i++) {
            //$sketch      = $sketch_arr[$i];
            $supplier_id = $supplier_id_arr[$i];
            $product_id  = $product_id_arr[$i];
            $sub_con_id  = $sub_con_id_arr[$i];
            $quantity    = $quantity_arr[$i];
            $unit_price  = $unit_price_arr[$i];
            $amount      = $amount_arr[$i];
            $unit        = $unit_arr[$i];

            if($product_id != "" || $sub_con_id != "") {
                $faIi = array();
                $faIi['costing_summary_id'] = $costing_summary_id;
                $faIi['supplier_id']        = $supplier_id;
                $faIi['sub_con_id']         = $sub_con_id;
                $faIi['product_id']         = $product_id;
                $faIi['project_id']         = $project_id;
                //$faIi['sketch']             = $sketch;
                $faIi['quantity']           = $quantity;
                $faIi['unit_price']         = $unit_price;
                $faIi['unit']               = $unit;
                $faIi['amount']             = $amount;
                $faIi['creation_date']      = date('Y-m-d H:i:s');
                
                $insert = $dbUtil->getInsertSQLStringFromArray($faIi, 'costing_summary_history');
                $result = $db->sql_query($insert);
                $costing_summary_history_id = $db->sql_nextid();
            }                
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCostingSummaryFormValidate() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $product_id_arr  = $fn->getPostParam('product_id', array());
        $sub_con_id_arr    = $fn->getPostParam('sub_con_id', array());

        $validate->resetErrorArray();

        $filterTitleArray       = array_filter($product_id_arr);
        $filterDescriptionArray = array_filter($sub_con_id_arr);
        if (count($filterTitleArray) == 0 && count($filterDescriptionArray) == 0){
            $msg = 'Please enter atleast 1 item.';
            $validate->validateData('error_box1', $msg);
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditCostingSummarySubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id                     = $fn->getPostParam('project_id');
        $costing_summary_id             = $fn->getPostParam('costing_summary_id');
        $po_code                        = $fn->getPostParam('po_code');
        $invoice_code                   = $fn->getPostParam('invoice_code');
        $delivery_date                  = $fn->getPostParam('delivery_date');
        $no_of_worker_used              = $fn->getPostParam('no_of_worker_used');
        $no_of_days_worked              = $fn->getPostParam('no_of_days_worked');
        $labour_rates_per_day           = $fn->getPostParam('labour_rates_per_day');
        $po_price                       = $fn->getPostParam('po_price');
        $invoiced_price                 = $fn->getPostParam('invoiced_price');
        $profit_percentage              = $fn->getPostParam('profit_percentage');
        $po_price_with_gst              = $fn->getPostParam('po_price_with_gst');
        $invoiced_price_with_gst        = $fn->getPostParam('invoiced_price_with_gst');
        $profit                         = $fn->getPostParam('profit');
        $total_material_price           = $fn->getPostParam('total_material_price');
        $transport_charges              = $fn->getPostParam('transport_charges');
        $total_labour_charges           = $fn->getPostParam('total_labour_charges');
        $salesman_commission            = $fn->getPostParam('salesman_commission');
        $finance_charges                = $fn->getPostParam('finance_charges');
        $office_overheads               = $fn->getPostParam('office_overheads');
        $other_charges                  = $fn->getPostParam('other_charges');
        $total_cost                     = $fn->getPostParam('total_cost');
        $transport_charges_percentage   = $fn->getPostParam('transport_charges_percentage');
        $salesman_commission_percentage = $fn->getPostParam('salesman_commission_percentage');
        $finance_charges_percentage     = $fn->getPostParam('finance_charges_percentage');
        $office_overheads_percentage    = $fn->getPostParam('office_overheads_percentage');
        $sketch_arr                     = $fn->getPostParam('sketch', array());
        $supplier_id_arr                = $fn->getPostParam('supplier_id', array());
        $product_id_arr                 = $fn->getPostParam('product_id', array());
        $sub_con_id_arr                 = $fn->getPostParam('sub_con_id', array());
        $quantity_arr                   = $fn->getPostParam('quantity', array());
        $unit_price_arr                 = $fn->getPostParam('unit_price', array());
        $amount_arr                     = $fn->getPostParam('amount', array());
        $unit_arr                       = $fn->getPostParam('unit', array());
        $costing_summary_history_id_arr = $fn->getPostParam('costing_summary_history_id', array());

        if (!$this->getCostingSummaryFormValidate()){
            return $validate->getErrorMessageXML();
        }
                
        /* Generation of Invoice record */
        $faInv = array();
        $faInv['po_code']                        = $po_code;
        $faInv['invoice_code']                   = $invoice_code;
        $faInv['delivery_date']                  = $delivery_date;
        $faInv['no_of_worker_used']              = $no_of_worker_used;
        $faInv['no_of_days_worked']              = $no_of_days_worked;
        $faInv['labour_rates_per_day']           = $labour_rates_per_day;
        $faInv['po_price']                       = $po_price;
        $faInv['po_price_with_gst']              = $po_price_with_gst;
        $faInv['invoiced_price']                 = $invoiced_price;
        $faInv['invoiced_price_with_gst']        = $invoiced_price_with_gst;
        $faInv['profit_percentage']              = $profit_percentage;
        $faInv['profit']                         = $profit;
        $faInv['total_material_price']           = $total_material_price;
        $faInv['transport_charges']              = $transport_charges;
        $faInv['total_labour_charges']           = $total_labour_charges;
        $faInv['salesman_commission']            = $salesman_commission;
        $faInv['finance_charges']                = $finance_charges;
        $faInv['office_overheads']               = $office_overheads;
        $faInv['other_charges']                  = $other_charges;
        $faInv['total_cost']                     = $total_cost;
        $faInv['project_id']                     = $project_id;
        $faInv['transport_charges_percentage']   = $transport_charges_percentage;
        $faInv['salesman_commission_percentage'] = $salesman_commission_percentage;
        $faInv['finance_charges_percentage']     = $finance_charges_percentage;
        $faInv['office_overheads_percentage']    = $office_overheads_percentage;
        $faInv['creation_date']                  = date('Y-m-d H:i:s');
        $faInv['created_by']                     = $fn->getSessionParam('userName');
        
        $whereCondition = "WHERE costing_summary_id = {$costing_summary_id}";
        $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($faInv, 'costing_summary', $whereCondition);
        $db->sql_query($SQLUpdate);
        if($costing_summary_id != ''){
            $costing_summary_id = $costing_summary_id;
        } else {
            $costing_summary_id = $db->sql_nextid();
        }

        $total_cost = 0;
        $count = count($product_id_arr);
        for ($i= 0; $i < $count; $i++) {
            //$sketch      = $sketch_arr[$i];
            $supplier_id                = $supplier_id_arr[$i];
            $product_id                 = $product_id_arr[$i];
            $sub_con_id                 = $sub_con_id_arr[$i];
            $quantity                   = $quantity_arr[$i];
            $unit_price                 = $unit_price_arr[$i];
            $amount                     = $amount_arr[$i];
            $costing_summary_history_id = $costing_summary_history_id_arr[$i];
            $unit                       = $unit_arr[$i];

            if($product_id != "" || $sub_con_id != "") {
                if($costing_summary_history_id != ''){
                    $faIi = array();
                    $faIi['costing_summary_id'] = $costing_summary_id;
                    $faIi['supplier_id']        = $supplier_id;
                    $faIi['sub_con_id']         = $sub_con_id;
                    $faIi['product_id']         = $product_id;
                    $faIi['project_id']         = $project_id;
                    //$faIi['sketch']           = $sketch;
                    $faIi['quantity']           = $quantity;
                    $faIi['unit_price']         = $unit_price;
                    $faIi['unit']               = $unit;
                    $faIi['amount']             = $amount;
                    $faIi['creation_date']      = date('Y-m-d H:i:s');

                    $whereConditionLI = "WHERE costing_summary_history_id = {$costing_summary_history_id}";
                    $SQLUpdateLineItem = $dbUtil->getUpdateSQLStringFromArray($faIi, 'costing_summary_history', $whereConditionLI);
                    $db->sql_query($SQLUpdateLineItem);                    
                } else {
                    $faIi = array();
                    $faIi['costing_summary_id'] = $costing_summary_id;
                    $faIi['supplier_id']        = $supplier_id;
                    $faIi['sub_con_id']         = $sub_con_id;
                    $faIi['product_id']         = $product_id;
                    $faIi['project_id']         = $project_id;
                    //$faIi['sketch']           = $sketch;
                    $faIi['quantity']           = $quantity;
                    $faIi['unit_price']         = $unit_price;
                    $faIi['unit']               = $unit;
                    $faIi['amount']             = $amount;
                    $faIi['creation_date']      = date('Y-m-d H:i:s');
                    
                    $insert = $dbUtil->getInsertSQLStringFromArray($faIi, 'costing_summary_history');
                    $result = $db->sql_query($insert);
                    $costing_summary_history_id = $db->sql_nextid();
                }
            }                
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSearchSubCon() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $subConName = $extractor[0];

        $SQL = "
        SELECT s.company_name AS value
              ,s.company_name AS label
              ,s.sub_con_id AS id
              ,CONCAT_WS(' **** ', s.company_name) AS label
        FROM sub_con s
        WHERE (s.company_name LIKE '{$subConName}%')
        ORDER BY s.company_name
        ";

        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getActualChargesSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil   = Zend_Registry::get('cpUtil');
        
        $project_id = $fn->getPostParam('project_id');
        $title = $fn->getPostParam('title');
        $date = $fn->getPostParam('date');
        $amount = $fn->getPostParam('amount');
        $description = $fn->getPostParam('description');

        if (!$this->getActualChargesValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['project_id']    = $project_id;
        $fa['title']    = $title;
        $fa['date']    = $date;
        $fa['amount']    = $amount;
        $fa['description']    = $description;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'actual_costing_summary');

        $fn->addRecord($fa, 'actual_costing_summary');           

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getActualChargesValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $amount = $fn->getPostParam('amount');

        $validate->resetErrorArray();

        if ($amount == 0 || $amount == ''){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Amount";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddNewSupplierValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter supplier name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddNewSupplierSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddNewSupplierValidate()){
            return $validate->getErrorMessageXML();
        }

        $company_name    = $fn->getPostParam('company_name');
        $email           = $fn->getPostParam('email');
        $fax             = $fn->getPostParam('fax');
        $mobile          = $fn->getPostParam('mobile');
        $address_flat    = $fn->getPostParam('address_flat');
        $address_street  = $fn->getPostParam('address_street');
        $address_state   = $fn->getPostParam('address_state');
        $address_country = $fn->getPostParam('address_country');
        
        $fa = array();
        $fa['company_name']    = $company_name;
        $fa['email']           = $email;
        $fa['fax']             = $fax;
        $fa['mobile']          = $mobile;
        $fa['address_flat']    = $address_flat;
        $fa['address_street']  = $address_street;
        $fa['address_state']   = $address_state;
        $fa['address_country'] = $address_country;
        $fa['category']        = 'Supplier';
        $fa['created_by']      = $fn->getSessionParam('userName');
        $fa['creation_date']   = date("Y-m-d H:i:s");

        $insert1 = $dbUtil->getInsertSQLStringFromArray($fa, 'supplier');
        $result1 = $db->sql_query($insert1);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSupplierByJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $json = array();

        $SQL = "
        SELECT supplier_id
              ,company_name
        FROM supplier
        ORDER BY company_name
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['supplier_id'], "caption" => $row['company_name']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getSearchProductTitle() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title) AS label
              ,p.category_id AS category
              ,p.product_type
              ,(SELECT i.actual_stock
                FROM inventory i
                WHERE i.product_id = p.product_id) AS stock
        FROM product p
        WHERE (p.title LIKE '{$productTitle}%')
          AND p.published = 1
        ORDER BY p.title
        ";

        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getAddNewProductMasterValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter product name');
        $validate->validateData('product_type', 'Please select product type');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddNewProductMasterSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddNewProductMasterValidate()){
            return $validate->getErrorMessageXML();
        }

        $title        = $fn->getPostParam('title');
        $product_type = $fn->getPostParam('product_type');
        
        $title = trim($title);

        $fa = array();
        $fa['title']         = $title;
        $fa['published']     = 1;
        $fa['product_type']  = $product_type;
        $fa['item_code']     = $this->getUpdateProductCode();
        $fa['created_by']    = $fn->getSessionParam('userName');
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insert1    = $dbUtil->getInsertSQLStringFromArray($fa, 'product');
        $result1    = $db->sql_query($insert1);
        $product_id = $db->sql_nextid();

        return $validate->getSuccessMessageXML();
    } 
}