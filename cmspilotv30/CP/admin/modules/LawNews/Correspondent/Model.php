<?
class CP_Admin_Modules_LawNews_Correspondent_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $extraTableNames = "";
        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if ($site_id != "") {
                $extraTableNames .= "JOIN site_link sl ON (c.correspondent_id = sl.record_id AND sl.module ='lawNews_correspondent')";
            }        
        }

        $SQL = "
        SELECT c.*
              ,j.title AS jurisdiction_title            
        FROM correspondent c
        LEFT JOIN jurisdiction j ON c.jurisdiction_id = j.jurisdiction_id
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

        $correspondent_id = $fn->getReqParam('correspondent_id');
        $jurisdiction_id = $fn->getReqParam('jurisdiction_id');


        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id'); 
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "sl.site_id = '{$site_id}'";   
            }                  
        }

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "c.published = 1";
        }

        if ($correspondent_id != "") {
            $searchVar->sqlSearchVar[] = "c.correspondent_id = '{$correspondent_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.correspondent_id = '{$tv['record_id']}'";
        } else {
    
            if ($jurisdiction_id != '') {
                $searchVar->sqlSearchVar[] = "c.jurisdiction_id = '{$jurisdiction_id}'";
            }

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.correspondent_id');
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Active") {
                $searchVar->sqlSearchVar[] = "c.active = 1";
            }
    
            if ($tv['special_search'] == "Not-Active") {
                $searchVar->sqlSearchVar[] = "(c.active != 1 OR c.active IS null)";
            }
    
            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "c.published = 1";
            }
    
            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.title       LIKE '%{$tv['keyword']}%'  OR
                    c.description LIKE '%{$tv['keyword']}%'
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
        $fa['sort_order']   = $fn->getNextSortOrder('correspondent');        
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
        $fa = $fn->addToFieldsArray($fa, 'jurisdiction_id');    
        $fa = $fn->addToFieldsArray($fa, 'published');    
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'years_linked');
        $fa = $fn->addToFieldsArray($fa, 'active');
        $fa = $fn->addToFieldsArray($fa, 'title_ref');

        return $fa;
    }

    /**
     *
     */
    function getLawCorrespondentLawYearLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');

        $yearFld = ($formObj->mode == 'edit') ? 'cy.year_id' : 'v.value AS year_title';

        $SQL = "
        SELECT cy.correspondent_year_id
              ,{$yearFld}
        FROM correspondent_year cy
        LEFT JOIN valuelist v ON (cy.year_id = v.valuelist_id )
        WHERE cy.correspondent_id = '{$id}'
        ORDER BY cy.correspondent_year_id
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function getCorrespondentSQL() {
        $SQL = "
        SELECT correspondent_id
              ,title
        FROM correspondent
        ORDER BY title
        ";  
        
        return $SQL;
    }    
}
