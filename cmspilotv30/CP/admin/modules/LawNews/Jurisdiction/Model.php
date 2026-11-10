<?
class CP_Admin_Modules_LawNews_Jurisdiction_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $extraTableNames = "";
        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if ($site_id != "") {
                $extraTableNames .= "JOIN site_link sl ON (j.jurisdiction_id = sl.record_id AND sl.module ='lawNews_jurisdiction')";
            }
        }        

        $SQL = "
        SELECT j.*
              ,v.value AS region_name
        FROM jurisdiction j
        LEFT JOIN (valuelist v) ON (j.region_id = v.valuelist_id )
        {$extraTableNames}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $jurisdiction_id    = $fn->getReqParam('jurisdiction_id');
        $region_id          = $fn->getReqParam('region_id');

        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id'); 
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "sl.site_id = '{$site_id}'";   
            }                  
        }

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "j.published = 1";
        }

        if ($jurisdiction_id != "") {
            $searchVar->sqlSearchVar[] = "j.jurisdiction_id = '{$jurisdiction_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "j.jurisdiction_id = '{$tv['record_id']}'";
        } else {

            if ($region_id != '') {
                $searchVar->sqlSearchVar[] = "j.region_id = '{$region_id}'";
            }

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'j.jurisdiction_id');
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "j.subscribe = 1";
            }
    
            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(j.subscribe != 1 OR j.subscribe IS null)";
            }
    
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "j.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(j.flag != 1 OR j.flag IS null)";
            }
    
            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "j.published = 1";
            }
    
            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "j.published = 0 OR j.published IS NULL OR j.published = ''";
            }
   
            //------------------------------------------------------------------------//

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    j.title       LIKE '%{$tv['keyword']}%'  OR
                    j.description LIKE '%{$tv['keyword']}%'
                )";
            }            

            
        }
    }
    
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');

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
        $fa['sort_order']   = $fn->getNextSortOrder('jurisdiction');
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');

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

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'region_id');    
        $fa = $fn->addToFieldsArray($fa, 'published');    
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'title_ref');

        return $fa;
    }

    /**
     *
     */
    function getJurisdictionSQL() {
        $SQL = "
        SELECT jurisdiction_id
              ,title
        FROM jurisdiction
        ORDER BY title
        ";  
        
        return $SQL;
    }    
}
