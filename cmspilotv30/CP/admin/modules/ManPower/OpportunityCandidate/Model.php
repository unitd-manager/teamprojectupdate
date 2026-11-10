<?
class CP_Admin_Modules_ManPower_OpportunityCandidate_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL   = "
        SELECT oc.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS candidate_name
              ,o.title AS opportunity_title
              ,c.travel_document_no
              ,o.opportunity_code
              ,CONCAT_WS(' ', a.first_name, a.last_name ) AS agent_name
        FROM opportunity_candidate oc
        LEFT JOIN candidate c ON (c.candidate_id = oc.candidate_id)
        LEFT JOIN opportunity o ON (o.opportunity_id = oc.opportunity_id)
        LEFT JOIN agent a ON (a.agent_id = oc.agent_id)
        LEFT JOIN (company cmp) ON (o.company_id = cmp.company_id)
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
        $searchVar->mainTableAlias = 'oc';

        $opportunity_candidate_id = $fn->getReqParam('opportunity_candidate_id');
        $process_status           = $fn->getReqParam('process_status');
        $response_status          = $fn->getReqParam('response_status');
        $company_id               = $fn->getReqParam('company_id');

        if ($opportunity_candidate_id != "") {
            $searchVar->sqlSearchVar[] = "oc.opportunity_candidate_id = '{$opportunity_candidate_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "oc.opportunity_candidate_id = '{$tv['record_id']}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'oc.opportunity_candidate_id');
    
            if ($process_status != "") {
                $searchVar->sqlSearchVar[] = "oc.process_status = '{$process_status}'";
            }

            if ($response_status != "") {
                $searchVar->sqlSearchVar[] = "oc.response_status = '{$response_status}'";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "o.company_id   = {$company_id}";
            }

            //------------------------------------------------------------------------//    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.first_name   LIKE '%{$tv['keyword']}%'
                    OR c.last_name    LIKE '%{$tv['keyword']}%'
                    OR c.company_name LIKE '%{$tv['keyword']}%'
                    OR c.email        LIKE '%{$tv['keyword']}%'
                )";
            }
    
            if ($_SESSION['userGroupType'] != "Super Administrator") {
                $searchVar->sqlSearchVar[] = "o.staff_id  = '{$_SESSION['staff_id']}'";
            }

            $searchVar->sortOrder = "c.last_name, c.first_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
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

        $fa = $fn->addToFieldsArray($fa, 'opportunity_id');
        $fa = $fn->addToFieldsArray($fa, 'candidate_id');
        $fa = $fn->addToFieldsArray($fa, 'process_status');
        $fa = $fn->addToFieldsArray($fa, 'response_status');
        $fa = $fn->addToFieldsArray($fa, 'percent_win');
        
        return $fa;
    }

}
