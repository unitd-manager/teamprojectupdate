<?
class CP_Admin_Modules_ManPower_CandidateLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $candidate_id   = $fn->getReqParam('candidate_id');

        $validate->resetErrorArray();
        if ($candidate_id) {
            $sql = "
            SELECT * FROM opportunity_candidate
            WHERE opportunity_id = {$opportunity_id}
              AND candidate_id = {$candidate_id}
            ";
            $result = $db->sql_query($sql);
            $numRows = $db->sql_numrows($result);

            if ($numRows) {
                $msg = 'Candidate already selected';
                $validate->validateData('error_box', $msg);
            }
        }

        $validate->validateData('candidate_id', 'Please select the candidate');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditCandidateFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditPortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $opportunity_candidate_id = $fn->getReqParam('opportunity_candidate_id');
        $candidate_id = $fn->getReqParam('candidate_id');
        $candidateRec = $fn->getRecordRowById('candidate', 'candidate_id', $candidate_id);

        $fa = $this->getFields();
        $fa['modified_by']       = $fn->getSessionParam('userName');
        $fa['modification_date'] = date("Y-m-d H:i:s");
        $fa['agent_id']          = $candidateRec['agent_id'];
        $fa['passport_no']       = $candidateRec['travel_document_no'];

        $whereCondition = "WHERE opportunity_candidate_id = {$opportunity_candidate_id}";
        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "opportunity_candidate", $whereCondition);
        $resultUpdate      = $db->sql_query($sqlUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddCandidateFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $candidate_id = $fn->getReqParam('candidate_id');
        $candidateRec = $fn->getRecordRowById('candidate', 'candidate_id', $candidate_id);

        $fa = $this->getFields();
        $fa['created_by']       = $fn->getSessionParam('userName');
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['agent_id']         = $candidateRec['agent_id'];
        $fa['passport_no']      = $candidateRec['travel_document_no'];

        $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'opportunity_candidate');
        $resultSQL          = $db->sql_query($insertInvoiceSQL);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDeleteCandidateRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $opportunity_candidate_id = $fn->getReqParam('opportunity_candidate_id');
        $candidate_id             = $fn->getReqParam('candidate_id');

        $SQL ="
               DELETE FROM opportunity_candidate
               WHERE opportunity_candidate_id = {$opportunity_candidate_id}
               ";
        $result = $db->sql_query($SQL);
        //$cpUtil->redirect("index.php?_topRm=opportunity&module=manPower_opportunity&opportunity_id={$opportunity_id}&_action=edit");
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

        $candidate_id = $fn->getReqParam('candidate_id');
        $candidateRec = $fn->getRecordRowById('candidate', 'candidate_id', $candidate_id);

        $fa = $this->getFields();
        $fa['agent_id'] = $candidateRec['agent_id'];

        $id = $fn->addRecord($fa);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPortalValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $candidate_id   = $fn->getReqParam('candidate_id');
        $opportunity_candidate_id   = $fn->getReqParam('opportunity_candidate_id');

        $validate->resetErrorArray();
        if ($candidate_id) {
            $sql = "
            SELECT * FROM opportunity_candidate
            WHERE opportunity_id = {$opportunity_id}
              AND candidate_id = {$candidate_id}
              AND opportunity_candidate_id != {$opportunity_candidate_id}
            ";
            $result = $db->sql_query($sql);
            $numRows = $db->sql_numrows($result);

            if ($numRows) {
                $msg = 'Candidate already selected';
                $validate->validateData('error_box', $msg);
            }
        }

        $validate->validateData('candidate_id', 'Please select the candidate');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }

    }

    /**
     *
     */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        
        if (!$this->getEditPortalValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $fa = $this->getFields();

        $candidate_id = $fn->getReqParam('candidate_id');
        $candidateRec = $fn->getRecordRowById('candidate', 'candidate_id', $candidate_id);

        $fa = $this->getFields();
        $fa['agent_id'] = $candidateRec['agent_id'];
        
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
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
        //$fa = $fn->addToFieldsArray($fa, 'passport_no');
        return $fa;
    }
    
    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';
        
        $agent_id                 = $fn->getReqParam('agent_id');
        $opportunity_candidate_id = $fn->getReqParam('opportunity_candidate_id');
        $position                 = $fn->getReqParam('position');
        $candidate_id             = $fn->getReqParam('candidate_id');
        $first_name               = $fn->getReqParam('first_name');
        $last_name                = $fn->getReqParam('last_name');
        $subscribe                = $fn->getReqParam('subscribe');
        $special_search           = $fn->getReqParam('special_search');
        $status                   = $fn->getReqParam('status');

        if ($tv['srcRoom'] == 'manPower_agent') {
            if ($candidate_id != "") {
                $searchVar->sqlSearchVar[] = "c.candidate_id = '{$candidate_id}'";
            } else if ($tv['record_id'] != '') {
                $searchVar->sqlSearchVar[] = "c.candidate_id = '{$tv['record_id']}'";
            } else {

                $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.candidate_id');

                if ($tv['keyword'] != "") {
                    $searchVar->sqlSearchVar[] = "(
                           c.first_name   LIKE '%{$tv['keyword']}%'
                        OR c.last_name    LIKE '%{$tv['keyword']}%'
                        OR c.email        LIKE '%{$tv['keyword']}%'
                    )";
                }
            }
        } else {
            if($position != ''){
                $searchVar->sqlSearchVar[] = "pc.position_title = '{$position}'";
            }
            else if ($opportunity_candidate_id != '') {
                $searchVar->sqlSearchVar[] = "oc.opportunity_candidate_id = '{$opportunity_candidate_id}'";
                $searchVar->sqlSearchVar[] = "AND c.edit_locked IS NULL OR c.edit_locked = 0";
            }
            else if ($tv['record_id'] != '') {
                $searchVar->sqlSearchVar[] = "oc.opportunity_candidate_id = '{$tv['record_id']}'";
                $searchVar->sqlSearchVar[] = "AND c.edit_locked IS NULL OR c.edit_locked = 0";
            }
            else {

                $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'oc.opportunity_candidate_id');
                //------------------------------------------------------------------------//
                if ($tv['special_search'] == "Subscribed") {
                    $searchVar->sqlSearchVar[] = "c.subscribe = 1";
                }

                if ($tv['special_search'] == "Not-Subscribed") {
                    $searchVar->sqlSearchVar[] = "(c.subscribe != 1 OR c.subscribe IS null)";
                }

                if ($tv['special_search'] == "Flagged") {
                    $searchVar->sqlSearchVar[] = "c.flag = 1";
                }

                if ($tv['special_search'] == "Not-Flagged") {
                    $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
                }

                if ($tv['special_search']  == 'Published') {
                    $searchVar->sqlSearchVar[] = "c.published = 1";
                }

                if ($tv['special_search'] == 'Not-Published' ) {
                    $searchVar->sqlSearchVar[] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
                }

                if ($agent_id != "") {
                    $searchVar->sqlSearchVar[] = "c.agent_id = {$agent_id}";
                }

                if ($tv['keyword'] != "") {
                    $searchVar->sqlSearchVar[] = "(
                           c.first_name   LIKE '%{$tv['keyword']}%'
                        OR c.last_name    LIKE '%{$tv['keyword']}%'
                        OR c.email        LIKE '%{$tv['keyword']}%'
                    )";
                }
                $searchVar->sqlSearchVar[] = "c.edit_locked IS NULL OR c.edit_locked = 0";
                $searchVar->groupBy = "c.candidate_id";
            }
        }
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['batch_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }

    /**
     *
     */
    function getCandidateLinkByCandidateJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $candidate_id   = $fn->getReqParam('candidate_id');

        $json  = array();
        
        if ($candidate_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT candidate_id
              ,CONCAT_WS(' ', first_name, last_name) AS candidate_name
        FROM candidate 
        WHERE candidate_id = '{$candidate_id}'
        ORDER BY candidate_name
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['candidate_id'], "caption" => $row['candidate_name']);
        }
        
        return json_encode($json);
    }
}
