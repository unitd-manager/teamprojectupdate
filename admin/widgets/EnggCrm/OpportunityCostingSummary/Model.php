<?
class CPL_Admin_Widgets_EnggCrm_OpportunityCostingSummary_Model extends CP_Common_Lib_WidgetModelAbstract
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_opportunityCostingSummary');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getCostingSummarySubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $opportunity_id                 = $fn->getPostParam('opportunity_id');
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
        $faInv['opportunity_id']                 = $opportunity_id;
        $faInv['salesman_commission_percentage'] = $salesman_commission_percentage;
        $faInv['finance_charges_percentage']     = $finance_charges_percentage;
        $faInv['office_overheads_percentage']    = $office_overheads_percentage;
        $faInv['transport_charges_percentage']   = $transport_charges_percentage;
        $faInv['creation_date']                  = date('Y-m-d H:i:s');
        $faInv['created_by']                     = $fn->getSessionParam('userName');
        
        $insertInv  = $dbUtil->getInsertSQLStringFromArray($faInv, 'opportunity_costing_summary');
        $resultInv  = $db->sql_query($insertInv);
        $opportunity_costing_summary_id = $db->sql_nextid();

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
                $faIi['opportunity_costing_summary_id'] = $opportunity_costing_summary_id;
                $faIi['supplier_id']                    = $supplier_id;
                $faIi['sub_con_id']                     = $sub_con_id;
                $faIi['product_id']                     = $product_id;
                $faIi['opportunity_id']                 = $opportunity_id;
                //$faIi['sketch']                         = $sketch;
                $faIi['quantity']                       = $quantity;
                $faIi['unit_price']                     = $unit_price;
                $faIi['unit']                           = $unit;
                $faIi['amount']                         = $amount;
                $faIi['creation_date']                  = date('Y-m-d H:i:s');
                
                $insert = $dbUtil->getInsertSQLStringFromArray($faIi, 'opportunity_costing_summary_history');
                $result = $db->sql_query($insert);
                $opportunity_costing_summary_history_id = $db->sql_nextid();
            }                
        }

        $SQLProject = "
        SELECT project_id 
        FROM project
        WHERE opportunity_id = '{$opportunity_id}'
        ";
        $resultProject  = $db->sql_query($SQLProject);
        $numRowsProject = $db->sql_numrows($resultProject);

        if($numRowsProject > 0) {
            $rowProject = $db->sql_fetchrow($resultProject);

            $SQlOCS ="
            SELECT *
            FROM opportunity_costing_summary
            WHERE opportunity_id = {$opportunity_id}
            ";
            $resultOCS = $db->sql_query($SQlOCS);
            while ($rowOCS = $db->sql_fetchrow($resultOCS)) {
                $facs = array();
                $facs['po_code']                        = $rowOCS['po_code'];
                $facs['invoice_code']                   = $rowOCS['invoice_code'];
                $facs['delivery_date']                  = $rowOCS['delivery_date'];
                $facs['no_of_worker_used']              = $rowOCS['no_of_worker_used'];
                $facs['no_of_days_worked']              = $rowOCS['no_of_days_worked'];
                $facs['labour_rates_per_day']           = $rowOCS['labour_rates_per_day'];
                $facs['po_price']                       = $rowOCS['po_price'];
                $facs['po_price_with_gst']              = $rowOCS['po_price_with_gst'];
                $facs['invoiced_price']                 = $rowOCS['invoiced_price'];
                $facs['invoiced_price_with_gst']        = $rowOCS['invoiced_price_with_gst'];
                $facs['profit_percentage']              = $rowOCS['profit_percentage'];
                $facs['profit']                         = $rowOCS['profit'];
                $facs['total_material_price']           = $rowOCS['total_material_price'];
                $facs['transport_charges']              = $rowOCS['transport_charges'];
                $facs['total_labour_charges']           = $rowOCS['total_labour_charges'];
                $facs['salesman_commission']            = $rowOCS['salesman_commission'];
                $facs['finance_charges']                = $rowOCS['finance_charges'];
                $facs['office_overheads']               = $rowOCS['office_overheads'];
                $facs['other_charges']                  = $rowOCS['other_charges'];
                $facs['total_cost']                     = $rowOCS['total_cost'];
                $facs['project_id']                     = $rowProject['project_id'];
                $facs['salesman_commission_percentage'] = $rowOCS['salesman_commission_percentage'];
                $facs['finance_charges_percentage']     = $rowOCS['finance_charges_percentage'];
                $facs['office_overheads_percentage']    = $rowOCS['office_overheads_percentage'];
                $facs['transport_charges_percentage']   = $rowOCS['transport_charges_percentage'];

                $SQlOCS1 ="
                SELECT *
                FROM costing_summary
                WHERE project_id = '{$rowProject['project_id']}'
                ";
                $resultOCS1  = $db->sql_query($SQlOCS1);
                $numRowsOCS1 = $db->sql_numrows($resultOCS1);
                
                if($numRowsOCS1 == 0) {
                    $facs['creation_date'] = date('Y-m-d H:i:s');
                    $facs['created_by']    = $fn->getSessionParam('userName');

                    $insertCs           = $dbUtil->getInsertSQLStringFromArray($facs, 'costing_summary');
                    $resultCs           = $db->sql_query($insertCs);
                    $costing_summary_id = $db->sql_nextid();
                } else {
                    $rowOCS1            = $db->sql_fetchrow($resultOCS1);
                    $costing_summary_id = $rowOCS1['costing_summary_id'];

                    $facs['modification_date'] = date('Y-m-d H:i:s');
                    $facs['modified_by']       = $fn->getSessionParam('userName');

                    $updateCs       = $dbUtil->getUpdateSQLStringFromArray($facs, 'costing_summary', "WHERE costing_summary_id = {$costing_summary_id}");
                    $resultupdateCs = $db->sql_query($updateCs);
                }

                $SQlOCSH ="
                SELECT *
                FROM opportunity_costing_summary_history
                WHERE opportunity_id = {$opportunity_id}
                  AND opportunity_costing_summary_id = {$rowOCS['opportunity_costing_summary_id']}
                ";
                $resultOCSH = $db->sql_query($SQlOCSH);
                while ($rowOCSH = $db->sql_fetchrow($resultOCSH)) {
                    $faIi = array();
                    $faIi['project_id']         = $rowProject['project_id'];
                    $faIi['costing_summary_id'] = $costing_summary_id;
                    $faIi['supplier_id']        = $rowOCSH['supplier_id'];
                    $faIi['sub_con_id']         = $rowOCSH['sub_con_id'];
                    $faIi['quantity']           = $rowOCSH['quantity'];
                    $faIi['unit_price']         = $rowOCSH['unit_price'];
                    $faIi['unit']               = $rowOCSH['unit'];
                    $faIi['amount']             = $rowOCSH['amount'];

                    $SQlOCSH1 ="
                    SELECT *
                    FROM costing_summary_history
                    WHERE project_id = '{$rowProject['project_id']}'
                      AND costing_summary_id = '{$costing_summary_id}'
                    ";
                    $resultOCSH1  = $db->sql_query($SQlOCSH1);
                    $numRowsOCSH1 = $db->sql_numrows($resultOCSH1);
                    
                    if($numRowsOCSH1 == 0) {
                        $faIi['creation_date'] = date('Y-m-d H:i:s');

                        $insert = $dbUtil->getInsertSQLStringFromArray($faIi, 'costing_summary_history');
                        $result = $db->sql_query($insert);
                    } else {
                        $rowOCSH1 = $db->sql_fetchrow($resultOCSH1);
                        $faIi['modification_date'] = date('Y-m-d H:i:s');

                        $updateCsH       = $dbUtil->getUpdateSQLStringFromArray($faIi, 'costing_summary_history', "WHERE costing_summary_history_id = {$rowOCSH1['costing_summary_history_id']}");
                        $resultupdateCsH = $db->sql_query($updateCsH);
                    }
                }
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

        $opportunity_id                 = $fn->getPostParam('opportunity_id');
        $opportunity_costing_summary_id = $fn->getPostParam('opportunity_costing_summary_id');
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
        $amount_arr      = $fn->getPostParam('amount', array());
        $unit_arr        = $fn->getPostParam('unit', array());

        $opportunity_costing_summary_history_id_arr = $fn->getPostParam('opportunity_costing_summary_history_id', array());

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
        $faInv['opportunity_id']                 = $opportunity_id;
        $faInv['transport_charges_percentage']   = $transport_charges_percentage;
        $faInv['salesman_commission_percentage'] = $salesman_commission_percentage;
        $faInv['finance_charges_percentage']     = $finance_charges_percentage;
        $faInv['office_overheads_percentage']    = $office_overheads_percentage;
        $faInv['creation_date']                  = date('Y-m-d H:i:s');
        $faInv['created_by']                     = $fn->getSessionParam('userName');
        
        $whereCondition = "WHERE opportunity_costing_summary_id = {$opportunity_costing_summary_id}";
        $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($faInv, 'opportunity_costing_summary', $whereCondition);
        $db->sql_query($SQLUpdate);
        if($opportunity_costing_summary_id != ''){
            $opportunity_costing_summary_id = $opportunity_costing_summary_id;
        } else {
            $opportunity_costing_summary_id = $db->sql_nextid();
        }

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

            $opportunity_costing_summary_history_id = $opportunity_costing_summary_history_id_arr[$i];

            if($product_id != "" || $sub_con_id != "") {
                if($opportunity_costing_summary_history_id != ''){
                    $faIi = array();
                    $faIi['opportunity_costing_summary_id'] = $opportunity_costing_summary_id;
                    $faIi['supplier_id']                    = $supplier_id;
                    $faIi['sub_con_id']                     = $sub_con_id;
                    $faIi['product_id']                     = $product_id;
                    $faIi['opportunity_id']                 = $opportunity_id;
                    //$faIi['sketch']      =                 $sketch;
                    $faIi['quantity']                       = $quantity;
                    $faIi['unit_price']                     = $unit_price;
                    $faIi['amount']                         = $amount;
                    $faIi['unit']                           = $unit;
                    $faIi['creation_date']                  = date('Y-m-d H:i:s');

                    $whereConditionLI = "WHERE opportunity_costing_summary_history_id = {$opportunity_costing_summary_history_id}";
                    $SQLUpdateLineItem = $dbUtil->getUpdateSQLStringFromArray($faIi, 'opportunity_costing_summary_history', $whereConditionLI);
                    $db->sql_query($SQLUpdateLineItem);                    
                } else {
                    $faIi = array();
                    $faIi['opportunity_costing_summary_id'] = $opportunity_costing_summary_id;
                    $faIi['supplier_id']                    = $supplier_id;
                    $faIi['sub_con_id']                     = $sub_con_id;
                    $faIi['product_id']                     = $product_id;
                    $faIi['opportunity_id']                 = $opportunity_id;
                    //$faIi['sketch']      =                 $sketch;
                    $faIi['quantity']                       = $quantity;
                    $faIi['unit_price']                     = $unit_price;
                    $faIi['unit']                           = $unit;
                    $faIi['amount']                         = $amount;
                    $faIi['creation_date']                  = date('Y-m-d H:i:s');
                    
                    $insert = $dbUtil->getInsertSQLStringFromArray($faIi, 'opportunity_costing_summary_history');
                    $result = $db->sql_query($insert);
                    $opportunity_costing_summary_history_id = $db->sql_nextid();
                }
            }                
        }

        $SQLProject = "
        SELECT project_id 
        FROM project
        WHERE opportunity_id = '{$opportunity_id}'
        ";
        $resultProject  = $db->sql_query($SQLProject);
        $numRowsProject = $db->sql_numrows($resultProject);

        if($numRowsProject > 0) {
            $rowProject = $db->sql_fetchrow($resultProject);

            $SQlOCS ="
            SELECT *
            FROM opportunity_costing_summary
            WHERE opportunity_id = {$opportunity_id}
            ";
            $resultOCS = $db->sql_query($SQlOCS);
            while ($rowOCS = $db->sql_fetchrow($resultOCS)) {
                $facs = array();
                $facs['po_code']                        = $rowOCS['po_code'];
                $facs['invoice_code']                   = $rowOCS['invoice_code'];
                $facs['delivery_date']                  = $rowOCS['delivery_date'];
                $facs['no_of_worker_used']              = $rowOCS['no_of_worker_used'];
                $facs['no_of_days_worked']              = $rowOCS['no_of_days_worked'];
                $facs['labour_rates_per_day']           = $rowOCS['labour_rates_per_day'];
                $facs['po_price']                       = $rowOCS['po_price'];
                $facs['po_price_with_gst']              = $rowOCS['po_price_with_gst'];
                $facs['invoiced_price']                 = $rowOCS['invoiced_price'];
                $facs['invoiced_price_with_gst']        = $rowOCS['invoiced_price_with_gst'];
                $facs['profit_percentage']              = $rowOCS['profit_percentage'];
                $facs['profit']                         = $rowOCS['profit'];
                $facs['total_material_price']           = $rowOCS['total_material_price'];
                $facs['transport_charges']              = $rowOCS['transport_charges'];
                $facs['total_labour_charges']           = $rowOCS['total_labour_charges'];
                $facs['salesman_commission']            = $rowOCS['salesman_commission'];
                $facs['finance_charges']                = $rowOCS['finance_charges'];
                $facs['office_overheads']               = $rowOCS['office_overheads'];
                $facs['other_charges']                  = $rowOCS['other_charges'];
                $facs['total_cost']                     = $rowOCS['total_cost'];
                $facs['project_id']                     = $rowProject['project_id'];
                $facs['salesman_commission_percentage'] = $rowOCS['salesman_commission_percentage'];
                $facs['finance_charges_percentage']     = $rowOCS['finance_charges_percentage'];
                $facs['office_overheads_percentage']    = $rowOCS['office_overheads_percentage'];
                $facs['transport_charges_percentage']   = $rowOCS['transport_charges_percentage'];

                $SQlOCS1 ="
                SELECT *
                FROM costing_summary
                WHERE project_id = '{$rowProject['project_id']}'
                ";
                $resultOCS1  = $db->sql_query($SQlOCS1);
                $numRowsOCS1 = $db->sql_numrows($resultOCS1);
                
                if($numRowsOCS1 == 0) {
                    $facs['creation_date'] = date('Y-m-d H:i:s');
                    $facs['created_by']    = $fn->getSessionParam('userName');

                    $insertCs           = $dbUtil->getInsertSQLStringFromArray($facs, 'costing_summary');
                    $resultCs           = $db->sql_query($insertCs);
                    $costing_summary_id = $db->sql_nextid();
                } else {
                    $rowOCS1            = $db->sql_fetchrow($resultOCS1);
                    $costing_summary_id = $rowOCS1['costing_summary_id'];

                    $facs['modification_date'] = date('Y-m-d H:i:s');
                    $facs['modified_by']       = $fn->getSessionParam('userName');

                    $updateCs       = $dbUtil->getUpdateSQLStringFromArray($facs, 'costing_summary', "WHERE costing_summary_id = {$costing_summary_id}");
                    $resultupdateCs = $db->sql_query($updateCs);
                }

                $SQlOCSH ="
                SELECT *
                FROM opportunity_costing_summary_history
                WHERE opportunity_id = {$opportunity_id}
                  AND opportunity_costing_summary_id = {$rowOCS['opportunity_costing_summary_id']}
                ";
                $resultOCSH = $db->sql_query($SQlOCSH);
                while ($rowOCSH = $db->sql_fetchrow($resultOCSH)) {
                    $faIi = array();
                    $faIi['project_id']         = $rowProject['project_id'];
                    $faIi['costing_summary_id'] = $costing_summary_id;
                    $faIi['supplier_id']        = $rowOCSH['supplier_id'];
                    $faIi['sub_con_id']         = $rowOCSH['sub_con_id'];
                    $faIi['quantity']           = $rowOCSH['quantity'];
                    $faIi['unit_price']         = $rowOCSH['unit_price'];
                    $faIi['unit']               = $rowOCSH['unit'];
                    $faIi['amount']             = $rowOCSH['amount'];

                    $SQlOCSH1 ="
                    SELECT *
                    FROM costing_summary_history
                    WHERE project_id = '{$rowProject['project_id']}'
                      AND costing_summary_id = '{$costing_summary_id}'
                    ";
                    $resultOCSH1  = $db->sql_query($SQlOCSH1);
                    $numRowsOCSH1 = $db->sql_numrows($resultOCSH1);
                    
                    if($numRowsOCSH1 == 0) {
                        $faIi['creation_date'] = date('Y-m-d H:i:s');

                        $insert = $dbUtil->getInsertSQLStringFromArray($faIi, 'costing_summary_history');
                        $result = $db->sql_query($insert);
                    } else {
                        $rowOCSH1 = $db->sql_fetchrow($resultOCSH1);
                        $faIi['modification_date'] = date('Y-m-d H:i:s');

                        $updateCsH       = $dbUtil->getUpdateSQLStringFromArray($faIi, 'costing_summary_history', "WHERE costing_summary_history_id = {$rowOCSH1['costing_summary_history_id']}");
                        $resultupdateCsH = $db->sql_query($updateCsH);
                    }
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
        
        $opportunity_id = $fn->getPostParam('opportunity_id');
        $title = $fn->getPostParam('title');
        $date = $fn->getPostParam('date');
        $amount = $fn->getPostParam('amount');
        $description = $fn->getPostParam('description');

        if (!$this->getActualChargesValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['opportunity_id']    = $opportunity_id;
        $fa['title']    = $title;
        $fa['date']    = $date;
        $fa['amount']    = $amount;
        $fa['description']    = $description;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'actual_opportunity_costing_summary');

        $fn->addRecord($fa, 'actual_opportunity_costing_summary');           

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

    /**
     *
     */
    function getUpdateProductCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Product Code */
        $nextProductItemCode = $fn->getSettingsValueByKey("nextProductItemCode");

        if($nextProductItemCode < 10){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '000' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 99){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '00' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 999){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '0' . $nextProductItemCode;
        }
        else{
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . $nextProductItemCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductItemCode'";
        $result = $db->sql_query($SQL);

        return $ProCode;
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
}