<?
class CPL_Admin_Modules_EnggCrm_Project_Model extends CP_Admin_Modules_EnggCrm_Project_Model
{
    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpCfg = Zend_Registry::get('cpCfg');

        $title              = $fn->getReqParam('title');
        $category           = $fn->getReqParam('category');
        $company_id         = $fn->getReqParam('company_id');
        $contact_id         = $fn->getReqParam('contact_id');
        $project_id         = $fn->getReqParam('project_id');
        $service_id         = $fn->getReqParam('service_id');
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');
        $yearMonthFinish    = $fn->getReqParam('yearMonthFinish');
        $company_id         = $fn->getReqParam('company_id');
        $project_month      = $fn->getReqParam('project_month');
        $start_date1        = $fn->getReqParam('start_date_1');
        $start_date2        = $fn->getReqParam('start_date_2');
        $end_date1          = $fn->getReqParam('end_date_1');
        $end_date2          = $fn->getReqParam('end_date_2');
        $branch_id          = $fn->getReqParam('branch_id');
        $client_type        = $fn->getReqParam('client_type');
        $status             = $fn->getReqParam('status');
        $project_manager_id = $fn->getReqParam('project_manager_id');
        $staff_id           = $fn->getReqParam('staff_id');

        if ($project_id != "") {
            $searchVar->sqlSearchVar[] = "p.project_id = {$project_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.project_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.project_id');

            if ($status != '') {
                $searchVar->sqlSearchVar[] = "p.status = '{$status}'";
            } else if ($tv['searchDone'] == 0) {
                $searchVar->sqlSearchVar[] = "p.status = 'WIP'";
            }

            if ($title != '') {
                $searchVar->sqlSearchVar[] = "p.title LIKE '%{$title}%'";
            }

            if ($category != '') {
                $searchVar->sqlSearchVar[] = "p.category = '{$category}'";
            }

            if ($project_manager_id != "") {
                $searchVar->sqlSearchVar[] = "p.project_manager_id   = {$project_manager_id}";
            }

            if ($project_month != "") {
                $searchVar->sqlSearchVar[] = "p.paid_on  = '{$project_month}'";
            }

            if ($contact_id != "") {
                $searchVar->sqlSearchVar[] = "p.contact_id   = {$contact_id}";
            }

            if ($service_id != "") {
                $searchVar->sqlSearchVar[] = "p.service_id   = {$service_id}";
            }

            if ($tv['status'] != "") {
                $searchVar->sqlSearchVar[] = "p.status   = '{$tv['status']}'";
            }

            if ($branch_id != "") {
                $searchVar->sqlSearchVar[] = "p.branch_id = '{$branch_id}'";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "p.company_id  = {$company_id}";
            }

            if ($client_type != '') {
                $searchVar->sqlSearchVar[] = "p.client_type = '{$client_type}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.title          LIKE '%{$tv['keyword']}%'  OR
                    p.project_code   LIKE '%{$tv['keyword']}%'  OR
                    p.project_id     LIKE '%{$tv['keyword']}%'  OR
                    p.description    LIKE '%{$tv['keyword']}%'  OR
                    c.company_name   LIKE '%{$tv['keyword']}%'  OR
                    p.notes          LIKE '%{$tv['keyword']}%'  OR
                    p.quote_ref      LIKE '%{$tv['keyword']}%'  OR
                    ser.service_code LIKE '%{$tv['keyword']}%'  OR
                    ser.title        LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($tv['staff_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.project_id     =  ps_hist.project_id";
                $searchVar->sqlSearchVar[] = "ps_hist.staff_id = {$tv['staff_id']}";
            }

            if ($yearMonthStart != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(start_date, '%Y-%m') = '{$yearMonthStart}'";
            }

            if ($yearMonthFinish != '' ) {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(estimated_finish_date, '%Y-%m') = '{$yearMonthFinish}'";
            }

            if ($start_date1 != "" && $start_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(p.start_date BETWEEN '{$start_date1}' AND '{$start_date2}')";
            }

            if ($end_date1 != "" && $end_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(p.estimated_finish_date BETWEEN '{$end_date1}' AND '{$end_date2}')";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "p.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(p.flag != 1 OR p.flag IS null)";
            }

        }

        //------------------------------------------------------------------------//
        if ($tv['searchDone'] == 0){
            $searchVar->sortOrder = "c.company_name";
        } else {
            $searchVar->sortOrder = "p.project_code DESC";
        }

    }

}
