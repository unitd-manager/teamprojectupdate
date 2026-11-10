<?
class CPL_Admin_Modules_EnggCrm_Employee_Model extends CP_Admin_Modules_EnggCrm_Employee_Model
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $event_id       = $fn->getReqParam('event_id');
        $broadcast_id   = $fn->getReqParam('broadcast_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');
        $project_id     = $fn->getReqParam('project_id');
        $task_id        = $fn->getReqParam('task_id');
        
        $extraTableNames = "";
        if ($event_id != "") {
            $extraTableNames .= "event_employee eventemployee,";
        }

        if ($broadcast_id != "") {
            $extraTableNames .= "broadcast_employee hist1,";
        }

        if ($opportunity_id != "") {
            $extraTableNames .= "opportunity opp,";
        }

        if ($project_id != "") {
            $extraTableNames .= "project proj,";
        }

        if ($task_id != "") {
            $extraTableNames .= "task task,";
        }

        if ($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1) {
            $SQL   = "
            SELECT a.*
                   ,b.company_name    AS c_company_name
                   ,b.email           AS c_email
                   ,b.address_flat    AS c_address_flat
                   ,b.address_street  AS c_address_street
                   ,b.address_town    AS c_address_town
                   ,b.address_state   AS c_address_state
                   ,b.address_country AS c_address_country
                   ,b.address_po_code AS c_address_po_code
                   ,b.phone           AS c_phone
                   ,b.fax             AS c_fax
                   ,b.status          AS c_status
                   ,b.website         AS c_website
                   ,b.category        AS c_category
                   ,d.address_flat    AS comp_mul_address_flat
                   ,d.address_street  AS comp_mul_address_street
                   ,d.address_town    AS comp_mul_address_town
                   ,d.address_state   AS comp_mul_address_state
                   ,d.address_country AS comp_mul_address_country
                  ,gc.name AS country_name
                  ,IF(a.employee_work_type = 'Part time', add_hourly_rate, salary) AS employee_amt
            FROM {$extraTableNames}
            employee a
            LEFT JOIN geo_country gc ON (a.address_country = gc.country_code)
            LEFT JOIN (company b) ON ( a.company_id = b.company_id )
            LEFT JOIN (company_address d) ON ( a.company_address_id = d.company_address_id )
                    ";
        } else {
            $SQL   = "
            SELECT a.*,
            ji.emp_type,
            b.company_name    AS c_company_name,
            b.email           AS c_email,
            b.address_flat    AS c_address_flat,
            b.address_street  AS c_address_street,
            b.address_town    AS c_address_town,
            b.address_state   AS c_address_state,
            b.address_country AS c_address_country,
            b.address_po_code AS c_address_po_code,
            b.phone           AS c_phone,
            b.fax             AS c_fax,
            b.status          AS c_status,
            b.website         AS c_website,
            b.category        AS c_category,
            gc.name AS country_name
            FROM {$extraTableNames}
            employee a
            LEFT JOIN job_information ji ON (a.employee_id = ji.employee_id)
            LEFT JOIN geo_country gc ON (a.address_country = gc.country_code)
            LEFT JOIN (company b) ON ( a.company_id = b.company_id )
            ";
        }
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $employee_id        = $fn->getReqParam('employee_id');
        $special_search     = $fn->getReqParam('special_search');
        $employee_work_type = $fn->getReqParam('employee_work_type');
        $employee_status    = $fn->getReqParam('employee_status');

        if ($employee_id != "") {
            $searchVar->sqlSearchVar[] = "a.employee_id = '{$employee_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.employee_id = '{$tv['record_id']}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.employee_id');
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "a.subscribe = 1";
            }
    
            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(a.subscribe != 1 OR a.subscribe IS null)";
            }
    
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "a.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(a.flag != 1 OR a.flag IS null)";
            }
    
            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "a.published = 1";
            }
    
            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "a.published = 0 OR a.published IS NULL OR a.published = ''";
            }
    
            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       a.employee_name  LIKE '%{$tv['keyword']}%'
                    OR a.nric_no        LIKE '%{$tv['keyword']}%'
                    OR a.spass_no       LIKE '%{$tv['keyword']}%'
                )";
            }
    
            if ($employee_work_type != "") {
                $searchVar->sqlSearchVar[] = "ji.emp_type = '{$employee_work_type}'";
            }

            if ($employee_status != "") {
                $searchVar->sqlSearchVar[] = "a.status = '{$employee_status}'";
            /*}else{
                $searchVar->sqlSearchVar[] = "a.status = 'Current'";*/
            }
            
            $searchVar->sortOrder = "a.employee_name ASC";
        }
    }

    /**
     *
     */
    function getAdd1(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['status'] = 'Active';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'employee_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'phone_direct');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'company_address_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');

        $fa = $fn->addToFieldsArray($fa, 'salary');
        $fa = $fn->addToFieldsArray($fa, 'day_rate');
        $fa = $fn->addToFieldsArray($fa, 'add_hourly_rate');
        $fa = $fn->addToFieldsArray($fa, 'overtime_rate');
        $fa = $fn->addToFieldsArray($fa, 'employee_work_type');
        $fa = $fn->addToFieldsArray($fa, 'passport');
        $fa = $fn->addToFieldsArray($fa, 'spass_no');
        $fa = $fn->addToFieldsArray($fa, 'nric_no');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'date_of_expiry');
        $fa = $fn->addToFieldsArray($fa, 'status');
        
        return $fa;
    }

    /**
     *
     */
    function getEmployeeCategorySubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getEmployeeCategoryValidate()){
            return $validate->getErrorMessageXML();
        }

        $employee_id      = $fn->getReqParam('employee_id');
        $category         = $fn->getPostParam('category');

        $fa = array();
        $fa['category']        = $category;
        $fa['employee_id']     = $employee_id;
        $fa['creation_date']   = date("Y-m-d H:i:s");

        $insertSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'employee_category');
        $resultSQL = $db->sql_query($insertSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEmployeeCategoryValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('category', 'Please select category');

        $category = $fn->getPostParam('category', '', true);
        $employee_id = $fn->getPostParam('employee_id');

        if ($category != ''){
            $rec = $fn->getRecordByCondition('employee_category', "category = '{$category}' AND employee_id = {$employee_id}");
            if (is_array($rec)){
                $validate->errorArray['category']['name'] = "category";
                $validate->errorArray['category']['msg']  = "Category already added";
            }
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
    function getDeleteEmployeeCategory(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $employee_category_id = $fn->getReqParam('employee_category_id');

        $SQL ="
               DELETE FROM employee_category
               WHERE employee_category_id = {$employee_category_id}
               ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();

        $validate->validateData('employee_name', 'Please enter the name');
        /*$validate->validateData('spass_no', 'Please enter the s pass no');
        $validate->validateData('nric_no' , 'Please enter the nric no');
        $validate->validateData('employee_work_type' , 'Please select the work type');*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
