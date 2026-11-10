<?
class CPL_Admin_Modules_EnggCrm_Invoice_Model extends CP_Admin_Modules_EnggCrm_Invoice_Model
{
    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $invoice_id    = $fn->getReqParam('invoice_id');
        $record_id     = $fn->getReqParam('record_id');
        $project_id    = $fn->getReqParam('project_id');
        $company_id    = $fn->getReqParam('company_id');
        $company_name  = $fn->getReqParam('company_name');
        $title         = $fn->getReqParam('title');
        $status        = $fn->getReqParam('status');
        $invoice_type  = $fn->getReqParam('invoice_type');
        $invoice_date1 = $fn->getReqParam('invoice_date_1');
        $invoice_date2 = $fn->getReqParam('invoice_date_2');
        $due_date1     = $fn->getReqParam('due_date_1');
        $due_date2     = $fn->getReqParam('due_date_2');
        $paid_date1    = $fn->getReqParam('paid_date_1');
        $paid_date2    = $fn->getReqParam('paid_date_2');
        $yearMonth     = $fn->getReqParam('yearMonth');
        $branch_id     = $fn->getReqParam('branch_id');
        $paid_to_kk   = $fn->getReqParam('paid_to_kk');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$tv['record_id']}'";
        } else if ($invoice_id != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$invoice_id}'";
        } else {
    
            if ($status == "" && $tv['searchDone'] == 0 && $tv['record_id'] == '') {
                $status = 'Due';
            }
    
            if ($invoice_date1 != "" && $invoice_date2 != "") {
                $searchVar->sqlSearchVar[] = "(
                    i.invoice_date BETWEEN  '{$invoice_date1}' AND '{$invoice_date2}'
                )";
            }
    
            if ($due_date1 != "" && $due_date2 != "") {
                $searchVar->sqlSearchVar[] = "(
                    i.invoice_due_date BETWEEN  '{$due_date1}' AND '{$due_date2}'
                )";
            }
    
            if ($paid_date1 != "" && $paid_date2 != "") {
                $searchVar->sqlSearchVar[] = "(
                    i.invoice_paid_date BETWEEN  '{$paid_date1}' AND '{$paid_date2}'
                )";
            }

            if ($branch_id != "") {
                $searchVar->sqlSearchVar[] = "p.branch_id = '{$branch_id}'";
            }

            if ($status != "") {
                if ($status == "Due" ) {
                    $searchVar->sqlSearchVar[] = "(i.status =  'Due' || i.status  =  'Late')" ;
                } else {
                    $searchVar->sqlSearchVar[] = "i.status   = '{$status}'";
                }
            }    
    
            if ($project_id != "") {
                $searchVar->sqlSearchVar[] = "i.project_id   = '{$project_id}'";
            }
    
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "c.company_id   = '{$company_id}'";
            }
        
            if ($invoice_id != "") {
                $searchVar->sqlSearchVar[] = "i.invoice_id   = '{$invoice_id}'";
            }
    
            if ($invoice_type != "") {
                $searchVar->sqlSearchVar[] = "i.invoice_type   = '{$invoice_type}'";
            }
    
            if ($record_id != "") {
                $searchVar->sqlSearchVar[] = "i.invoice_id   = '{$record_id}'";
            }
    
            if ($paid_to_kk == "Yes") {
                $searchVar->sqlSearchVar[] = "i.paid_to_kk   = '1'";
            }
    
            if ($paid_to_kk == "No") {
                $searchVar->sqlSearchVar[] = "i.paid_to_kk   = '0'";
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        i.invoice_code   LIKE '%{$tv['keyword']}%' OR
                                        i.invoice_amount LIKE '%{$tv['keyword']}%' OR
                                        p.title          LIKE '%{$tv['keyword']}%'OR
                                        i.project_id     LIKE '%{$tv['keyword']}%'OR
                                        c.company_name   LIKE '%{$tv['keyword']}%'
                                       )";
            }
                   
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "i.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(i.flag != 1 OR i.flag IS null)";
            }

            if ($yearMonth != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$yearMonth}'";
            }
            
            //------------------------------------------------------------------------//    
            $searchVar->sortOrder = "
            CASE
            WHEN (i.status = 'Late' ) THEN 1
            WHEN (i.invoice_due_date != '' AND i.invoice_due_date IS NOT NULL AND i.invoice_due_date != '0000-00-00' ) THEN 2
            ELSE 3
            END, i.invoice_due_date
            ";
        }
    }

}
