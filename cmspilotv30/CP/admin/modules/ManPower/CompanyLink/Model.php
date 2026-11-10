<?
class CP_Admin_Modules_ManPower_CompanyLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'a';

        $status         = $fn->getReqParam('status');
        $special_search = $fn->getReqParam('special_search');

        if ($status != '') {
            $searchVar->sqlSearchVar[] = "a.status = '{$status}'";
        }

        $searchVar->sqlSearchVar[] = "a.company_type !='Referral'";

        if ($tv['special_search'] == "Flagged") {
            $searchVar->sqlSearchVar[] = "(a.flag = 1)";
        }

        if ($tv['special_search'] == "Not-Flagged") {
            $searchVar->sqlSearchVar[] = "(a.flag != 1 OR a.flag IS null)";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                   a.company_name LIKE '%{$tv['keyword']}%'
                OR a.category     LIKE '%{$tv['keyword']}%'
            )";
        }

        $searchVar->sortOrder = "a.company_name";

        //$searchVar->sqlSearchVar[] = "a.category = 'client'";
    }

    /**
     *
     */
    function getAddNewCompanyForCallRegistryFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $company_name = $fn->getPostParam('company_name');
        $category     = $fn->getPostParam('category');
        $phone        = $fn->getPostParam('phone');

        if (!$this->getAddNewCompanyForCallRegistryFormValidate($company_name)){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['company_name']  = $company_name;
        $fa['category']      = $category;
        $fa['phone']         = $phone;
        $fa['site_id']       = $_SESSION['cp_site_id'];
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $fa['created_by']    = $fn->getSessionParam('userName');
            
        $insertCompanySQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'company');
        $resultSQL          = $db->sql_query($insertCompanySQL);
        $company_id         = $db->sql_nextid();
            
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddNewCompanyForCallRegistryFormValidate($company_name) {
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        $sql = "
        SELECT * FROM company
        WHERE company_name = '{$company_name}'
        ";
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        
        $validate->resetErrorArray();

		if($numRows){
            $msg = 'Company already exists';
            $validate->validateData('error_box', $msg);
		}

        $validate->validateData('company_name' , 'Please enter company name');
        $validate->validateData('category' , 'Please select the category');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}