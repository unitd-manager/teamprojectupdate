<?
class CPL_Admin_Modules_EnggCrm_CompanyLink_Model extends CP_Admin_Modules_EnggCrm_CompanyLink_Model
{
    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'a';

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
            } else {
                $searchVar->sqlSearchVar[] = "a.category = 'Client'";
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
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $company_name = $fn->getPostParam('company_name');

        $validate->resetErrorArray();

        $validate->validateData('company_name', 'Please enter company name');
        // $validate->validateData('address_flat', 'Please enter Address 1');
        // $validate->validateData('address_po_code', 'Please enter Postal Code');
        // $validate->validateData('address_country', 'Please select Country');

        if ($company_name != ''){
            $rec = $fn->getRecordByCondition('company', "company_name = '{$company_name}'");
            if (is_array($rec)){
                $validate->errorArray['company_name']['name'] = "company_name";
                $validate->errorArray['company_name']['msg']  = "Company name already exists";
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
    function getEditValidate() {
        return $this->getNewValidate();
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
        
        return $fa;
    }

}