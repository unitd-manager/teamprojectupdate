<?
class CP_Admin_Modules_Project_Company_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT a.*
        FROM company a
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

        $category     = $fn->getReqParam('category');
        $status       = $fn->getReqParam('status');
        $company_id   = $fn->getReqParam('company_id');
        $company_name = $fn->getReqParam('company_name');

        if ($company_id != "") {
            $searchVar->sqlSearchVar[] = "a.company_id = '{$company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.company_id');
    
            if ($status != "") {
                $searchVar->sqlSearchVar[] = "a.status = '{$status}'";
            }
    
            if ($category != "") {
                $searchVar->sqlSearchVar[] = "a.category = '{$category}'";
            }
    
            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "a.company_name LIKE '%{$company_name}%'";
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    a.company_name  LIKE '%{$tv['keyword']}%'
                    OR a.group_name LIKE '%{$tv['keyword']}%'  
                    OR a.email      LIKE '%{$tv['keyword']}%'
                )";
            }
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "a.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(a.flag != 1 OR a.flag IS null)";
            }
    
            $searchVar->sortOrder = "a.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the company name');

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
     */
    function getAddRenewal(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $company_id = $fn->getReqParam('company_id');

        $date     = $fn->getCurrentDate();
        $due_date = date('Y-m-d', strtotime("+365 days"));

        $formAction = "index.php?_topRm=opportunity&module=project_company&_spAction=addRenewalFormSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);

        $renewalType     = $fn->getValueListSQL('renewalType');
        $projectCategory = $fn->getValueListSQL('projectCategory');
        $projectStatus   = $fn->getValueListSQL('projectStatus');
        $sqlRegistrar    = $fn->getValueListSQL('registrar');
        $sqlServerName   = $fn->getValueListSQL('serverName');

        $expVl     = array('sqlType' => 'OneField');

        $sqlCompany = "
        SELECT website
        FROM company
        WHERE company_id = {$company_id}
        ";
        $resultCompany  = $db->sql_query($sqlCompany);
        $rowCompany     = $db->sql_fetchrow($resultCompany);

        $renewalArr  = array('Project'
                            ,'Invoice');

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $expBranch = array('detailValue' => '');
            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch, '', $expBranch);
        }

        $projectfieldset ="
            {$formObj->getDDRowBySQL('Category', 'project_category', $projectCategory,'', $expVl)}
            {$branch}
            {$formObj->getDDRowBySQL('Status', 'project_status', $projectStatus,'', $expVl)}
        ";

        $renewalCheck ="<div class ='renewalCheckBox'>{$formObj->getCheckBoxArrRowByArr(' ', 'renewal_check', $renewalArr ,$renewalArr)}</div>
                        {$formObj->getYesNoRRow('Chargeable', 'renewal_chargeable','')}";

        $sqlCurrency = $fn->getValueListSQL('currency');
        $expcurrency = array('sqlType' => 'OneField');
        $currency    = $formObj->getDDRowBySQL('Currency *', 'currency', $sqlCurrency,'', $expcurrency);

        $sqlStaffname = "
        SELECT staff_id
               ,CONCAT_WS(' ', first_name, last_name) AS staff_name
        FROM `staff`
        ";

        $renewalFieldset = "
            {$formObj->getDDRowBySQL('Renewal Type *', 'renewal_type', $renewalType,'', $expVl)}
            {$currency}
            {$formObj->getTBRow('Domain *', 'domain', $rowCompany['website'])}
            {$formObj->getTBRow('Total Amount *', 'renewal_amount','')}
            {$formObj->getDateRow('Start Date *', 'renewal_start_date', $date)}
            {$formObj->getDateRow('End Date *', 'renewal_end_date', $due_date)}
            {$formObj->getDDRowBySQL('Remind To *', 'remind_to', $sqlStaffname, $expVl)}
            {$formObj->getDDRowBySQL('Registrar', 'registrar', $sqlRegistrar, '', $expVl)}
            {$formObj->getDDRowBySQL('Server Name', 'server_name', $sqlServerName, '', $expVl)}
            {$formObj->getTARow('Notes', 'renewal_notes', '')}
        ";

        $text = "
        <form id='portalForm' class='yform columnar renewalLink' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getFieldSetWrapped('Please Check The Related Check Box', $renewalCheck)}
                {$formObj->getFieldSetWrapped('Project Details', $projectfieldset)}
                {$formObj->getFieldSetWrapped('Renewals Details', $renewalFieldset)}
                <input type='hidden' name='company_id' value='{$company_id}'>
            </fieldset>
        </form>
        ";

        return $text;

    }

    /**
     *
     */
    function getRenewalValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $company_id   = $fn->getReqParam('company_id');
        $renewal_type = $fn->getPostParam('renewal_type');
        $domain       = $fn->getPostParam('domain');

        $validate->resetErrorArray();
        $validate->validateData('renewal_amount', 'Please enter the amount');
        $validate->validateData('renewal_type', 'Please select the renewal type');
        $validate->validateData('currency', 'Please select the currency');
        $validate->validateData('renewal_start_date', 'Please select start date');
        $validate->validateData('renewal_end_date', 'Please select end date');
        $validate->validateData('remind_to', 'Please select the person');
        $validate->validateData('domain', 'Please enter the domain');
        $renewal_check = $fn->getPostParam('renewal_check', array());

        if($renewal_type != ''){
            $sqlRenewals = "
            SELECT company_id
                   ,renewal_type
            FROM renewals
            WHERE company_id = {$company_id}
            AND renewal_type = '{$renewal_type}'
            AND domain       = '{$domain}'
            ";
            $resultRenewals  = $db->sql_query($sqlRenewals);
            $numRowsRenewals = $db->sql_numrows($resultRenewals);

            if($numRowsRenewals > 0 ){
                $validate->errorArray['renewal_type']['name'] = "renewal_type";
                $validate->errorArray['renewal_type']['msg']  = $renewal_type." already exist.";
                $validate->errorArray['domain']['name'] = "domain";
                $validate->errorArray['domain']['msg']  = $domain." already exist.";
            }
        }

        if (in_array('Invoice', $renewal_check)) {

            if (in_array('Project', $renewal_check)) {

            }else{
                $validate->errorArray['renewal_check']['name'] = 'renewal_check';
                $validate->errorArray['renewal_check']['msg']  = 'Please check project';
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
    function getEditRenewalValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $company_id    = $fn->getReqParam('company_id');
        $renewal_id    = $fn->getReqParam('renewal_id');
        $renewal_type  = $fn->getPostParam('renewal_type');
        $domain        = $fn->getPostParam('domain');
        $renewal_type_current = $fn->getReqParam('renewal_type_current');
        $domain_name_current  = $fn->getReqParam('domain_name_current');
        $renewal_check = $fn->getPostParam('renewal_check', array());

        $projectCount = $fn->getRecordCount('project', "renewal_id = {$renewal_id}");
        $invoiceCount = $fn->getRecordCount('invoice', "renewal_id = {$renewal_id}");

        $validate->resetErrorArray();
        $validate->validateData('renewal_amount', 'Please enter the amount');
        $validate->validateData('renewal_type', 'Please select the renewal type');
        $validate->validateData('currency', 'Please select the currency');
        $validate->validateData('renewal_start_date', 'Please select start date');
        $validate->validateData('renewal_end_date', 'Please select end date');
        $validate->validateData('remind_to', 'Please select the person');
        $validate->validateData('domain', 'Please enter the domain');

        if($renewal_type != ''){
            $sqlRenewals = "
            SELECT company_id
                   ,renewal_type
            FROM renewals
            WHERE company_id  =  {$company_id}
            AND renewal_type  = '{$renewal_type}'
            AND domain        = '{$domain}'
            AND renewal_type != '{$renewal_type_current}'
            AND domain       != '{$domain_name_current}'
            ";
            $resultRenewals  = $db->sql_query($sqlRenewals);
            $numRowsRenewals = $db->sql_numrows($resultRenewals);

            if($numRowsRenewals > 0 ){
                $validate->errorArray['renewal_type']['name'] = "renewal_type";
                $validate->errorArray['renewal_type']['msg']  = $renewal_type." already exist.";
                $validate->errorArray['domain']['name'] = "domain";
                $validate->errorArray['domain']['msg']  = $domain." already exist.";
            }
        }


        if (in_array('Invoice', $renewal_check)) {

            if (in_array('Project', $renewal_check)) {

            }else{
                if($projectCount == 0){
                    $validate->errorArray['renewal_check']['name'] = 'renewal_check';
                    $validate->errorArray['renewal_check']['msg']  = 'Please create project before create invoice';
                }
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
    function getExtendRenewalValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $company_id    = $fn->getReqParam('company_id');
        $renewal_id    = $fn->getReqParam('renewal_id');
        $renewal_type  = $fn->getPostParam('renewal_type');
        $renewal_type_current = $fn->getReqParam('renewal_type_current');
        $renewal_check = $fn->getPostParam('renewal_check', array());

        $projectCount = $fn->getRecordCount('project', "renewal_id = {$renewal_id}");
        $invoiceCount = $fn->getRecordCount('invoice', "renewal_id = {$renewal_id}");

        $validate->resetErrorArray();
        $validate->validateData('renewal_type', 'Please select the renewal type');
        $validate->validateData('renewal_amount', 'Please enter the amount');
        $validate->validateData('currency', 'Please select the currency');
        $validate->validateData('renewal_start_date', 'Please select start date');
        $validate->validateData('renewal_end_date', 'Please select end date');
        $validate->validateData('remind_to', 'Please select the person');
        $validate->validateData('domain', 'Please enter the domain');

        if (in_array('Invoice', $renewal_check)) {

            if (in_array('Project', $renewal_check)) {

            }else{
                if($projectCount == 0){
                    $validate->errorArray['renewal_check']['name'] = 'renewal_check';
                    $validate->errorArray['renewal_check']['msg']  = 'Please create project before create invoice';
                }
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
    function getEditRenewal(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_topRm=opportunity&module=project_company&_spAction=editRenewalFormSubmit&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('renewals', 'renewal_id', $id);

        $sqlProject = "
        SELECT category
               ,status
               ,branch_id
        FROM project
        WHERE renewal_id = {$id}
        ";
        $resultProject  = $db->sql_query($sqlProject);
        $rowProject     = $db->sql_fetchrow($resultProject);
        $numRowsProject = $db->sql_numrows($resultProject);

        $sqlInvoice = "
        SELECT invoice_id
        FROM invoice
        WHERE renewal_id = {$id}
        AND status != 'Cancelled'
        ";
        $resultInvoice  = $db->sql_query($sqlInvoice);
        $rowInvoice     = $db->sql_fetchrow($resultInvoice);
        $numRowsInvoice = $db->sql_numrows($resultInvoice);

        $arrProject = '';
        if($numRowsProject > 0){
            $arrProject = 'Project';
        }

        $arrInvoice = '';
        if($numRowsInvoice > 0){
            $arrInvoice = 'Invoice';
        }

        $renewalArrCheck = array($arrProject
                                ,$arrInvoice);

        $renewalType     = $fn->getValueListSQL('renewalType');
        $projectCategory = $fn->getValueListSQL('projectCategory');
        $projectStatus   = $fn->getValueListSQL('projectStatus');
        $sqlRegistrar    = $fn->getValueListSQL('registrar');
        $sqlServerName   = $fn->getValueListSQL('serverName');

        $expVl     = array('sqlType' => 'OneField');

        $renewalArr  = array('Project'
                            ,'Invoice');

        $sqlStaffname = "
        SELECT staff_id
               ,CONCAT_WS(' ', first_name, last_name) AS staff_name
        FROM `staff`
        ";

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $expBranch = array('detailValue' => '');
            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch, $rowProject['branch_id'], $expBranch);
        }

        $projectfieldset ="
            {$formObj->getDDRowBySQL('Category', 'project_category', $projectCategory ,$rowProject['category'], $expVl)}
            {$branch}
            {$formObj->getDDRowBySQL('Status', 'project_status', $projectStatus ,$rowProject['status'], $expVl)}
        ";

        $renewalCheck ="<div class ='renewalCheckBox'>{$formObj->getCheckBoxArrRowByArr(' ', 'renewal_check', $renewalArr ,$renewalArrCheck)}</div>
                        {$formObj->getYesNoRRow('Chargeable', 'renewal_chargeable',$row['chargeable'])}";

        $sqlCurrency = $fn->getValueListSQL('currency');
        $expcurrency = array('sqlType' => 'OneField');
        $currency    = $formObj->getDDRowBySQL('Currency *', 'currency', $sqlCurrency,$row['currency'], $expcurrency);

        $renewalFieldset = "
            {$formObj->getDDRowBySQL('Renewal Type *', 'renewal_type', $renewalType,$row['renewal_type'], $expVl)}
            {$currency}
            {$formObj->getTBRow('Domain *', 'domain', $row['domain'])}
            {$formObj->getTBRow('Total Amount *', 'renewal_amount',$row['amount'])}
            {$formObj->getDateRow('Start Date *', 'renewal_start_date', $row['start_date'])}
            {$formObj->getDateRow('End Date *', 'renewal_end_date', $row['end_date'])}
            {$formObj->getDDRowBySQL('Remind To *', 'remind_to', $sqlStaffname, $row['remind_to'])}
            {$formObj->getDDRowBySQL('Registrar', 'registrar', $sqlRegistrar, $row['registrar'], $expVl)}
            {$formObj->getDDRowBySQL('Server Name', 'server_name', $sqlServerName, $row['server_name'], $expVl)}
            {$formObj->getTARow('Notes', 'renewal_notes', $row['notes'])}
        ";

        $text = "
        <form id='portalForm' class='yform columnar renewalLink' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getFieldSetWrapped('Please Check The Related Check Box', $renewalCheck)}
                {$formObj->getFieldSetWrapped('Project Details', $projectfieldset)}
                {$formObj->getFieldSetWrapped('Renewals Details', $renewalFieldset)}
                <input type='hidden' name='company_id'           value='{$row['company_id']}'>
                <input type='hidden' name='renewal_id'           value='{$id}'>
                <input type='hidden' name='renewal_type_current' value='{$row['renewal_type']}'>
                <input type='hidden' name='domain_name_current'  value='{$row['domain']}'>
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getExtendRenewalForm(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_topRm=opportunity&module=project_company&_spAction=extendRenewalFormSubmit&showHTML=0";
        $id = $fn->getReqParam('company_id');
        $renewal_id = $fn->getReqParam('renewal_id');

        $SQL = "
        SELECT *
        FROM renewals
        WHERE company_id = {$id}
        AND renewal_id   = {$renewal_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $start_date  = $row['end_date'];
        $start_date  = date("Y-m-d", strtotime("$start_date +1 days"));
        $end_date    = date("Y-m-d", strtotime("$start_date +365 days"));

        $sqlProject = "
        SELECT category
               ,status
               ,branch_id
        FROM project
        WHERE renewal_id = {$row['renewal_id']}
        ";
        $resultProject  = $db->sql_query($sqlProject);
        $rowProject     = $db->sql_fetchrow($resultProject);
        $numRowsProject = $db->sql_numrows($resultProject);

        $sqlInvoice = "
        SELECT invoice_id
        FROM invoice
        WHERE renewal_id = {$row['renewal_id']}
        AND status != 'Cancelled'
        ";
        $resultInvoice  = $db->sql_query($sqlInvoice);
        $rowInvoice     = $db->sql_fetchrow($resultInvoice);
        $numRowsInvoice = $db->sql_numrows($resultInvoice);

        $arrProject = '';
        if($numRowsProject > 0){
            $arrProject = 'Project';
        }

        $arrInvoice = '';
        if($numRowsInvoice > 0){
            $arrInvoice = 'Invoice';
        }

        $renewalArrCheck = array($arrProject
                                ,$arrInvoice);

        $renewalType     = $fn->getValueListSQL('renewalType');
        $projectCategory = $fn->getValueListSQL('projectCategory');
        $projectStatus   = $fn->getValueListSQL('projectStatus');
        $sqlRegistrar    = $fn->getValueListSQL('registrar');
        $sqlServerName   = $fn->getValueListSQL('serverName');

        $expVl     = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);

        $renewalArr  = array('Project'
                            ,'Invoice');

        $sqlStaffname = "
        SELECT staff_id
               ,CONCAT_WS(' ', first_name, last_name) AS staff_name
        FROM `staff`
        ";

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $expBranch = array('detailValue' => '');
            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch, $rowProject['branch_id'], $expBranch);
        }

        $projectfieldset ="
            {$formObj->getDDRowBySQL('Category', 'project_category', $projectCategory ,$rowProject['category'], $expVl)}
            {$branch}
            {$formObj->getDDRowBySQL('Status', 'project_status', $projectStatus ,$rowProject['status'], $expVl)}
        ";

        $renewalCheck ="<div class ='renewalCheckBox'>{$formObj->getCheckBoxArrRowByArr(' ', 'renewal_check', $renewalArr ,$renewalArrCheck)}</div>
                        {$formObj->getYesNoRRow('Chargeable', 'renewal_chargeable',$row['chargeable'])}";

        $sqlCurrency = $fn->getValueListSQL('currency');
        $expcurrency = array('sqlType' => 'OneField');
        $currency    = $formObj->getDDRowBySQL('Currency *', 'currency', $sqlCurrency,$row['currency'], $expcurrency);

        $renewalFieldset = "
            {$formObj->getDDRowBySQL('Renewal Type *', 'renewal_type', $renewalType, $row['renewal_type'], $expVl)}
            {$currency}
            {$formObj->getTBRow('Domain *', 'domain', $row['domain'])}
            {$formObj->getTBRow('Total Amount *', 'renewal_amount',$row['amount'])}
            {$formObj->getDateRow('Start Date *', 'renewal_start_date', $start_date)}
            {$formObj->getDateRow('End Date *', 'renewal_end_date', $end_date)}
            {$formObj->getDDRowBySQL('Remind To *', 'remind_to', $sqlStaffname, $row['remind_to'])}
            {$formObj->getDDRowBySQL('Registrar', 'registrar', $sqlRegistrar, $row['registrar'], $expVl)}
            {$formObj->getDDRowBySQL('Server Name', 'server_name', $sqlServerName, $row['server_name'], $expVl)}
            {$formObj->getTARow('Notes', 'renewal_notes', $row['notes'])}
        ";

        $text = "
        <form id='portalForm' class='yform columnar renewalLink' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getFieldSetWrapped('Please Check The Related Check Box', $renewalCheck)}
                {$formObj->getFieldSetWrapped('Project Details', $projectfieldset)}
                {$formObj->getFieldSetWrapped('Renewals Details', $renewalFieldset)}
                <input type='hidden' name='company_id'   value='{$row['company_id']}'>
                <input type='hidden' name='renewal_id'   value='{$id}'>
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddRenewalFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getRenewalValidate()){
            return $validate->getErrorMessageXML();
        }

        $company_id         = $fn->getReqParam('company_id');
        $start_date         = $fn->getPostParam('renewal_start_date');
        $end_date           = $fn->getPostParam('renewal_end_date');
        $amount             = $fn->getPostParam('renewal_amount');
        $chargeable         = $fn->getPostParam('renewal_chargeable');
        $notes              = $fn->getPostParam('renewal_notes');
        $renewal_check      = $fn->getPostParam('renewal_check', array());
        $project_category   = $fn->getPostParam('project_category');
        $branch_id          = $fn->getPostParam('branch_id');
        $project_status     = $fn->getPostParam('project_status');
        $currency           = $fn->getPostParam('currency');
        $renewal_type       = $fn->getPostParam('renewal_type');
        $remind_to          = $fn->getPostParam('remind_to');
        $domain             = $fn->getPostParam('domain');
        $registrar          = $fn->getPostParam('registrar');
        $server_name        = $fn->getPostParam('server_name');

        $rowCompany  = $fn->getRecordRowByID('company', 'company_id', $company_id);

        $fa = array();
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $fa['start_date']    = $start_date;
        $fa['end_date']      = $end_date;
        $fa['amount']        = $amount;
        $fa['notes']         = $notes;
        $fa['renewal_type']  = $renewal_type;
        $fa['chargeable']    = $chargeable;

        if($chargeable == 0){
            $fa['renewal_status'] = 'Not Chargeable';
        }else{
            $fa['renewal_status'] = 'Due';
        }

        $fa['company_id']    = $company_id;
        $fa['currency']      = $currency;
        $fa['remind_to']     = $remind_to;
        $fa['domain']        = $domain;
        $fa['registrar']     = $registrar;
        $fa['server_name']   = $server_name;

        $insertRenewalSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'renewals');
        $resultSQL          = $db->sql_query($insertRenewalSQL);
        $renewal_id         = $db->sql_nextid();

        if (in_array('Project', $renewal_check)) {

            $startYear = date('Y', strtotime("$start_date"));
            $endYear   = date('Y', strtotime("$end_date"));

            $rowContact  = $fn->getRecordRowByID('contact', 'company_id', $company_id);

            $fa2 = array();

            $fa2['project_code'] = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
            $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
            $result = $db->sql_query($SQL);

            if($currency == 'INR'){
                $fa2['project_value']      = $amount;
                $fa2['project_value_ref']  = $amount;
            }elseif ($currency == 'US$') {
                $fa2['project_value']      = $amount;
                $fa2['project_value_base'] = $amount;
            }else{
                $fa2['project_value']      = $amount;
            }

            $fa2['category']              = $project_category;
            $fa2['branch_id']             = $branch_id;
            $fa2['status']                = $project_status;
            $fa2['title']                 = $renewal_type.' Renewal for '.$rowCompany['company_name'].' ('.$domain.') '.$startYear.'-'.$endYear;
            $fa2['project_manager_id']    = '9';
            $fa2['company_id']            = $company_id;
            $fa2['contact_id']            = $rowContact['contact_id'];
            $fa2['start_date']            = $start_date;
            $fa2['estimated_finish_date'] = $end_date;
            $fa2['currency']              = $currency;
            $fa2['renewal_id']            = $renewal_id;
            $fa2['notes']                 = $notes;
            $fa2['creation_date']         = date("Y-m-d H:i:s");
            $fa2['created_by']            = $fn->getSessionParam('userName');

            $insertProjectSQL   = $dbUtil->getInsertSQLStringFromArray($fa2, 'project');
            $resultProjectSQL   = $db->sql_query($insertProjectSQL);
            $project_id         = $db->sql_nextid();
        }

        if (in_array('Invoice', $renewal_check)) {

            if($chargeable == 1){
                $invoice_due_date = date('Y-m-d', strtotime("+14 days"));
                $invoice_sequence = $this->getNextInvoiceSeq($project_id);

                $fa3 = array();

                if($currency == 'INR'){
                    $fa3['invoice_amount']          = $amount;
                    $fa3['invoice_amount_ref']  = $amount;
                }elseif ($currency == 'US$') {
                    $fa3['invoice_amount']      = $amount;
                    $fa3['invoice_amount_base'] = $amount;
                }else{
                    $fa3['invoice_amount'] = $amount;
                }

                $fa3['invoice_type']       = 'Invoice';
                $fa3['project_id']         = $project_id;
                $fa3['status']             = 'Due';
                $fa3['inv_currency']       = $currency;
                $fa3['invoice_amount']     = $amount;
                $fa3['invoice_date']       = $fn->getCurrentDate();
                $fa3['invoice_due_date']   = $invoice_due_date;
                $fa3['notes']              = $notes;
                $fa3['invoice_sequence']   = $invoice_sequence;
                $fa3['invoice_terms']      = 'This invoice is due in 30 days. Please make cheque payable to Universal Software Solutions.';
                $fa3['renewal_id']         = $renewal_id;
                $fa3['created_by']         = $fn->getSessionParam('userName');
                $fa3['creation_date']      = date("Y-m-d H:i:s");

                $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa3, 'invoice');
                $resultInvoiceSQL   = $db->sql_query($insertInvoiceSQL);
                $invoice_id         = $db->sql_nextid();

                $invoice_code = $this->getInvoicecodeUpdate($invoice_id, $project_id, $invoice_sequence);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getExtendRenewalFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getExtendRenewalValidate()){
            return $validate->getErrorMessageXML();
        }

        $company_id         = $fn->getReqParam('company_id');
        $start_date         = $fn->getPostParam('renewal_start_date');
        $end_date           = $fn->getPostParam('renewal_end_date');
        $amount             = $fn->getPostParam('renewal_amount');
        $chargeable         = $fn->getPostParam('renewal_chargeable');
        $notes              = $fn->getPostParam('renewal_notes');
        $renewal_check      = $fn->getPostParam('renewal_check', array());
        $project_category   = $fn->getPostParam('project_category');
        $branch_id          = $fn->getPostParam('branch_id');
        $project_status     = $fn->getPostParam('project_status');
        $currency           = $fn->getPostParam('currency');
        $renewal_type       = $fn->getPostParam('renewal_type');
        $remind_to          = $fn->getPostParam('remind_to');
        $domain             = $fn->getPostParam('domain');
        $registrar          = $fn->getPostParam('registrar');
        $server_name        = $fn->getPostParam('server_name');

        $fa = array();
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $fa['start_date']    = $start_date;
        $fa['end_date']      = $end_date;
        $fa['amount']        = $amount;
        $fa['notes']         = $notes;
        $fa['renewal_type']  = $renewal_type;
        $fa['chargeable']    = $chargeable;
        $fa['company_id']    = $company_id;
        $fa['currency']      = $currency;

        if($chargeable == 0){
            $fa['renewal_status'] = 'Not Chargeable';
        }else{
            $fa['renewal_status'] = 'Due';
        }

        $fa['remind_to']    = $remind_to;
        $fa['domain']       = $domain;
        $fa['registrar']    = $registrar;
        $fa['server_name']  = $server_name;

        $insertRenewalSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'renewals');
        $resultSQL          = $db->sql_query($insertRenewalSQL);
        $renewal_id         = $db->sql_nextid();

        $rowCompany  = $fn->getRecordRowByID('company', 'company_id', $company_id);

        if (in_array('Project', $renewal_check)) {

            $startYear = date('Y', strtotime("$start_date"));
            $endYear   = date('Y', strtotime("$end_date"));

            $rowContact  = $fn->getRecordRowByID('contact', 'company_id', $company_id);

            $fa2 = array();

            $fa2['project_code'] = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
            $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
            $result = $db->sql_query($SQL);

            if($currency == 'INR'){
                $fa2['project_value']      = $amount;
                $fa2['project_value_ref']  = $amount;
            }elseif ($currency == 'US$') {
                $fa2['project_value']      = $amount;
                $fa2['project_value_base'] = $amount;
            }else{
                $fa2['project_value']      = $amount;
            }

            $fa2['category']              = $project_category;
            $fa2['branch_id']             = $branch_id;
            $fa2['status']                = $project_status;
            $fa2['title']                 = $renewal_type.' Renewal for '.$rowCompany['company_name'].' ('.$domain.') '.$startYear.'-'.$endYear;
            $fa2['project_manager_id']    = '9';
            $fa2['company_id']            = $company_id;
            $fa2['contact_id']            = $rowContact['contact_id'];
            $fa2['start_date']            = $start_date;
            $fa2['estimated_finish_date'] = $end_date;
            $fa2['currency']              = $currency;
            $fa2['renewal_id']            = $renewal_id;
            $fa2['notes']                 = $notes;
            $fa2['creation_date']         = date("Y-m-d H:i:s");
            $fa2['created_by']            = $fn->getSessionParam('userName');

            $insertProjectSQL   = $dbUtil->getInsertSQLStringFromArray($fa2, 'project');
            $resultProjectSQL   = $db->sql_query($insertProjectSQL);
            $project_id         = $db->sql_nextid();
        }

        if (in_array('Invoice', $renewal_check)) {

            if($chargeable == 1){
                $invoice_due_date = date('Y-m-d', strtotime("+14 days"));
                $invoice_sequence = $this->getNextInvoiceSeq($project_id);

                $fa3 = array();

                if($currency == 'INR'){
                    $fa3['invoice_amount']      = $amount;
                    $fa3['invoice_amount_ref']  = $amount;
                }elseif ($currency == 'US$') {
                    $fa3['invoice_amount']      = $amount;
                    $fa3['invoice_amount_base'] = $amount;
                }else{
                    $fa3['invoice_amount'] = $amount;
                }

                $fa3['invoice_type']       = 'Invoice';
                $fa3['project_id']         = $project_id;
                $fa3['status']             = 'Due';
                $fa3['inv_currency']       = $currency;
                $fa3['invoice_amount']     = $amount;
                $fa3['invoice_date']       = $fn->getCurrentDate();
                $fa3['invoice_due_date']   = $invoice_due_date;
                $fa3['notes']              = $notes;
                $fa3['invoice_sequence']   = $invoice_sequence;
                $fa3['invoice_terms']      = 'This invoice is due in 30 days. Please make cheque payable to Universal Software Solutions.';
                $fa3['renewal_id']         = $renewal_id;
                $fa3['created_by']         = $fn->getSessionParam('userName');
                $fa3['creation_date']      = date("Y-m-d H:i:s");

                $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa3, 'invoice');
                $resultInvoiceSQL   = $db->sql_query($insertInvoiceSQL);
                $invoice_id         = $db->sql_nextid();

                $invoice_code = $this->getInvoicecodeUpdate($invoice_id, $project_id, $invoice_sequence);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getInvoicecodeUpdate($invoice_id, $project_id, $sequence){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($project_id != "") {
            $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
            $project_code = $projRec['project_code'];

            $invoice_prefix = $fn->getSettingsValueByKey("invoiceCodePrefix");
            $project_prefix = $fn->getSettingsValueByKey("projectCodePrefix");
            $invCodeStartIndex = strlen($project_prefix) + 1;

            if ($cpCfg['m.project.invoice.hasAutoAffix'] == 0){
                $SQL = "
                UPDATE invoice
                SET invoice_code = CONCAT_WS('', '{$invoice_prefix}'
                                                ,SUBSTRING('{$projRec['project_code']}' FROM {$invCodeStartIndex})
                                                ,'-'
                                                , '{$sequence}'
                                            )
                WHERE invoice_id = {$invoice_id}
                ";
                $result = $db->sql_query($SQL);
            } else {
                $prefixZeros    = "000000";
                $invoiceSerial  = $fn->getSettingsValueByKey("nextInvoiceCode");

                $SQL     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
                $result  = $db->sql_query($SQL);
                $project_code = substr($project_code, strlen($project_prefix));

                $invoiceCode = $invoice_prefix. $project_code ."-".  substr ($prefixZeros, 0, 6 - strlen($invoiceSerial)) . $invoiceSerial;

                $SQL = "
                UPDATE invoice
                SET invoice_code = '{$invoiceCode}'
                WHERE invoice_id = {$invoice_id}
                ";
                $result = $db->sql_query($SQL);
            }
        }
    }

    /**
     *
     */
    function getNextInvoiceSeq($project_id) {
        $db = Zend_Registry::get('db');

        $SQL    = "
        SELECT MAX(invoice_sequence)
        FROM invoice
        WHERE project_id = {$project_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        return $row[0]+1;
    }

    /**
     *
     */
    function getEditRenewalFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditRenewalValidate()){
            return $validate->getErrorMessageXML();
        }

        $renewal_id        = $fn->getReqParam('renewal_id');
        $company_id        = $fn->getReqParam('company_id');
        $start_date        = $fn->getPostParam('renewal_start_date');
        $end_date          = $fn->getPostParam('renewal_end_date');
        $amount            = $fn->getPostParam('renewal_amount');
        $chargeable        = $fn->getPostParam('renewal_chargeable');
        $notes             = $fn->getPostParam('renewal_notes');
        $renewal_check     = $fn->getPostParam('renewal_check', array());
        $project_category  = $fn->getPostParam('project_category');
        $branch_id         = $fn->getPostParam('branch_id');
        $project_status    = $fn->getPostParam('project_status');
        $currency          = $fn->getPostParam('currency');
        $renewal_type      = $fn->getPostParam('renewal_type');
        $remind_to         = $fn->getPostParam('remind_to');
        $domain            = $fn->getPostParam('domain');
        $registrar         = $fn->getPostParam('registrar');
        $server_name       = $fn->getPostParam('server_name');

        $project_id = '';

        $fa1 = array();
        $fa1['start_date']         = $start_date;
        $fa1['end_date']           = $end_date;
        $fa1['amount']             = $amount;
        $fa1['notes']              = $notes;
        $fa1['chargeable']         = $chargeable;
        $fa1['currency']           = $currency;
        $fa1['renewal_type']       = $renewal_type;

        if($chargeable == 0){
            $fa1['renewal_status'] = 'Not Chargeable';
        }else{
            $fa1['renewal_status'] = 'Due';
        }

        $fa1['remind_to']          = $remind_to;
        $fa1['domain']             = $domain;
        $fa1['registrar']          = $registrar;
        $fa1['server_name']        = $server_name;
        $fa1['modification_date']  = date("Y-m-d H:i:s");

        $whereCondition  = "WHERE renewal_id = {$renewal_id} AND company_id = {$company_id}";
        $sqlUpdate       = $dbUtil->getUpdateSQLStringFromArray($fa1, "renewals", $whereCondition);
        $resultUpdate    = $db->sql_query($sqlUpdate);

        $rowCompany  = $fn->getRecordRowByID('company', 'company_id', $company_id);

        $sqlProject = "
        SELECT category
               ,status
               ,branch_id
               ,project_id
        FROM project
        WHERE renewal_id = {$renewal_id}
        ";
        $resultProject  = $db->sql_query($sqlProject);
        $rowProject     = $db->sql_fetchrow($resultProject);
        $numRowsProject = $db->sql_numrows($resultProject);

        if (in_array('Project', $renewal_check)) {

            $startYear = date('Y', strtotime("$start_date"));
            $endYear   = date('Y', strtotime("$end_date"));

            $rowContact  = $fn->getRecordRowByID('contact', 'company_id', $company_id);

            if($numRowsProject > 0){

                $fa2 = array();

                if($currency == 'INR'){
                    $fa2['project_value']      = $amount;
                    $fa2['project_value_ref']  = $amount;
                }elseif ($currency == 'US$') {
                    $fa2['project_value']      = $amount;
                    $fa2['project_value_base'] = $amount;
                }else{
                    $fa2['project_value']      = $amount;
                }

                $fa2['category']              = $project_category;
                $fa2['branch_id']             = $branch_id;
                $fa2['status']                = $project_status;
                $fa2['title']                 = $renewal_type.' Renewal for '.$rowCompany['company_name'].' ('.$domain.') '.$startYear.'-'.$endYear;
                $fa2['contact_id']            = $rowContact['contact_id'];
                $fa2['start_date']            = $start_date;
                $fa2['estimated_finish_date'] = $end_date;
                $fa2['currency']              = $currency;
                $fa2['notes']                 = $notes;
                $fa2['modification_date']     = date("Y-m-d H:i:s");
                $fa2['modified_by']           = $fn->getSessionParam('userName');

                $whereConditionProject = "WHERE renewal_id = {$renewal_id}";
                $sqlUpdateProject      = $dbUtil->getUpdateSQLStringFromArray($fa2, "project", $whereConditionProject);
                $resultUpdateProject   = $db->sql_query($sqlUpdateProject);
            }else{

                $fa2 = array();

                $fa2['project_code'] = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
                $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
                $result = $db->sql_query($SQL);

                if($currency == 'INR'){
                    $fa2['project_value']      = $amount;
                    $fa2['project_value_ref']  = $amount;
                }elseif ($currency == 'US$') {
                    $fa2['project_value']      = $amount;
                    $fa2['project_value_base'] = $amount;
                }else{
                    $fa2['project_value']      = $amount;
                }

                $fa2['category']              = $project_category;
                $fa2['branch_id']             = $branch_id;
                $fa2['status']                = $project_status;
                $fa2['title']                 = $renewal_type.' Renewal for '.$rowCompany['company_name'].' '.$startYear.'-'.$endYear;
                $fa2['project_manager_id']    = '9';
                $fa2['company_id']            = $company_id;
                $fa2['contact_id']            = $rowContact['contact_id'];
                $fa2['start_date']            = $start_date;
                $fa2['estimated_finish_date'] = $end_date;
                $fa2['currency']              = $currency;
                $fa2['renewal_id']            = $renewal_id;
                $fa2['notes']                 = $notes;
                $fa2['creation_date']         = date("Y-m-d H:i:s");
                $fa2['created_by']            = $fn->getSessionParam('userName');

                $insertProjectSQL   = $dbUtil->getInsertSQLStringFromArray($fa2, 'project');
                $resultProjectSQL   = $db->sql_query($insertProjectSQL);
                $project_id         = $db->sql_nextid();

            }
        }

        if (in_array('Invoice', $renewal_check)) {

            $sqlInvoice = "
            SELECT invoice_id
            FROM invoice
            WHERE renewal_id = {$renewal_id}
            AND status != 'Cancelled'
            ";
            $resultInvoice  = $db->sql_query($sqlInvoice);
            $rowInvoice     = $db->sql_fetchrow($resultInvoice);
            $numRowsInvoice = $db->sql_numrows($resultInvoice);

            if($numRowsInvoice > 0){

                if($chargeable == 1){
                    $invoice_due_date = date('Y-m-d', strtotime("+14 days"));
                    $fa3 = array();

                    if($currency == 'INR'){
                        $fa3['invoice_amount']          = $amount;
                        $fa3['invoice_amount_ref']  = $amount;
                    }elseif ($currency == 'US$') {
                        $fa3['invoice_amount']      = $amount;
                        $fa3['invoice_amount_base'] = $amount;
                    }else{
                        $fa3['invoice_amount'] = $amount;
                    }

                    $fa3['inv_currency']      = $currency;
                    $fa3['invoice_amount']    = $amount;
                    $fa3['notes']             = $notes;
                    $fa3['modified_by']       = $fn->getSessionParam('userName');
                    $fa3['modification_date'] = date("Y-m-d H:i:s");

                    $whereConditionInvoice = "WHERE renewal_id = {$renewal_id}";
                    $sqlUpdateInvoice      = $dbUtil->getUpdateSQLStringFromArray($fa3, "invoice", $whereConditionInvoice);
                    $resultUpdateInvoice   = $db->sql_query($sqlUpdateInvoice);
                }
            }
            else{

                if($chargeable == 1){
                    $invoice_due_date = date('Y-m-d', strtotime("+14 days"));

                    if($rowProject['project_id'] != ''){
                        $project_id = $rowProject['project_id'];
                    }

                    $invoice_sequence = $this->getNextInvoiceSeq($project_id);

                    $fa3 = array();

                    if($currency == 'INR'){
                        $fa3['invoice_amount']          = $amount;
                        $fa3['invoice_amount_ref']  = $amount;
                    }elseif ($currency == 'US$') {
                        $fa3['invoice_amount']      = $amount;
                        $fa3['invoice_amount_base'] = $amount;
                    }else{
                        $fa3['invoice_amount'] = $amount;
                    }

                    $fa3['invoice_type']       = 'Invoice';
                    $fa3['project_id']         = $project_id;
                    $fa3['status']             = 'Due';
                    $fa3['inv_currency']       = $currency;
                    $fa3['invoice_amount']     = $amount;
                    $fa3['invoice_date']       = $fn->getCurrentDate();
                    $fa3['invoice_due_date']   = $invoice_due_date;
                    $fa3['notes']              = $notes;
                    $fa3['invoice_sequence']   = $invoice_sequence;
                    $fa3['invoice_terms']      = 'This invoice is due in 30 days. Please make cheque payable to Universal Software Solutions.';
                    $fa3['renewal_id']         = $renewal_id;
                    $fa3['created_by']         = $fn->getSessionParam('userName');
                    $fa3['creation_date']      = date("Y-m-d H:i:s");

                    $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa3, 'invoice');
                    $resultInvoiceSQL   = $db->sql_query($insertInvoiceSQL);
                    $invoice_id         = $db->sql_nextid();

                    $invoice_code = $this->getInvoicecodeUpdate($invoice_id, $project_id, $invoice_sequence);
                }

            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDeleteRenewalRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $renewal_id = $fn->getReqParam('renewal_id');
        $company_id = $fn->getReqParam('company_id');

        $SQL ="
               DELETE FROM renewals
               WHERE renewal_id = {$renewal_id}
               ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'supplier_type');
        $fa = $fn->addToFieldsArray($fa, 'chi_company_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_company_address');

        return $fa;
    }

    /**
     *
     */
    function getProjectCompanyProjectContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.last_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";

    }

    /**
     *
     */
    function getProjectCompanyProjectOpportunityLinkSQL($id) {
        $fn = Zend_Registry::get('fn');

        $status = $fn->getReqParam('opp_status');
        
        $extraFields = "";

        if ($status != "") {
            $whereSQL = " AND a.status = '{$status}'";
        } else {
            $whereSQL = " AND (a.status != 'Cancelled')";
        }

        $SQL = "
        SELECT a.opportunity_id
              ,a.opportunity_code
              ,a.title AS title
              ,FORMAT(a.estimated_value,0)  AS estimated_value
              ,a.status {$extraFields}
        FROM company b
            ,opportunity a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
          {$whereSQL}
        ORDER BY title
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProjectCompanyProjectInvoiceLinkSQL($id) {
        $fn = Zend_Registry::get('fn');

        $status   = $fn->getReqParam('invoice_status');

        $whereSQL = '';

        if ($status != "") {
            $whereSQL .= " AND a.status = '{$status}'";
        } else {
            $whereSQL .= " AND (a.status = 'Due' OR a.status = 'Late')";
        }

        $SQL = "
        SELECT a.invoice_id
              ,a.invoice_code
              ,b.project_code
              ,a.invoice_type AS title
              ,a.invoice_date
              ,a.invoice_due_date
              ,FORMAT(a.invoice_amount, 0) AS invoice_amount
              ,a.status
        FROM invoice a
        LEFT JOIN (project b) ON (a.project_id = b.project_id)
        LEFT JOIN (company d) ON (b.company_id = d.company_id)
        WHERE d.company_id = {$id}
              {$whereSQL}
        ORDER BY title
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProjectCompanyProjectProjectLinkSQL($id) {
        $fn = Zend_Registry::get('fn');

        $still_to_bill_sql = "(
        SELECT FORMAT(sum(invoice_amount), 0) AS total_cost
        FROM invoice c
        WHERE c.project_id = a.project_id
        )
        ";

        $status      = $fn->getReqParam('project_status', 'WIP');
        
        $whereSQL    = "";
        $extraFields = "";

        if ($status != "") {
            $whereSQL .= " AND a.status = '{$status}'";
        }

        $SQL = "
        SELECT a.project_id
              ,a.project_code
              ,a.title AS title
              ,FORMAT(a.project_value, 0) AS project_value
              ,a.status
        FROM company b
            ,project a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
          {$whereSQL}
        ORDER BY title
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProjectCompanyProjectCompanyAddressLinkSQL($id) {
        $SQL = "
        SELECT a.company_address_id
              ,a.address_flat
              ,a.address_street
              ,a.address_town
              ,a.address_state
              ,a.address_country
              ,a.address_po_code
        FROM company b
            ,company_address a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProjectCompanyWebBasicProductLinkSQL1($id) {

        return "
        SELECT a.product_id
				a.t
        FROM `staff` a, `project_staff` b
        WHERE a.staff_id = b.staff_id
          AND b.project_id = '{$id}'
        ORDER BY title
        ";

    }

    /**
     *
     */
    function getExportData1(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Company_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Company ID");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Company Name");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Category");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Company Size");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Industry");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Source");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Website");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Phone");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Fax");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address Flat");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address Street");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address Town");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address State");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address Country");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Status");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Comment By");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Notes");
        
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

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['category']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_size']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['industry']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['source']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['website']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['phone']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['fax']);

            if ($cpCfg['m.project.hasMultipleCompanyAddress'] == 1) {
                $sqlAdd = "
                SELECT * 
                FROM company_address
                WHERE company_id = {$row['company_id']}
                LIMIT 0, 1
                ";
                $resultAdd = $db->sql_query($sqlAdd);
                $rowAdd    = $db->sql_fetchrow($resultAdd);

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_country']);
            } else {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_country']);
            }    

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comment_by']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['notes']);
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
              'company_id'      => $phpExcel->getFldObj('Company ID')
             ,'company_name'    => $phpExcel->getFldObj('Company Name')
             ,'category'        => $phpExcel->getFldObj('Category')
             ,'company_size'    => $phpExcel->getFldObj('Company Size')
             ,'industry'        => $phpExcel->getFldObj('Industry')
             ,'source'          => $phpExcel->getFldObj('Source')
             ,'website'         => $phpExcel->getFldObj('Website')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')
        
             ,'address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'address_street'  => $phpExcel->getFldObj('Address Street')
             ,'address_town'    => $phpExcel->getFldObj('Address Town')
             ,'address_state'   => $phpExcel->getFldObj('Address State')
             ,'address_country' => $phpExcel->getFldObj('Address Country')

             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comment_by'      => $phpExcel->getFldObj('Comment By')
             ,'notes'           => $phpExcel->getFldObj('Notes')
        );
        
        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }       
}
