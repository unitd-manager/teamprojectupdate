<?
class CP_Admin_Modules_LawNews_Reporter_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT r.*
              ,c.title AS correspondent_title
        FROM reporter r
        LEFT JOIN correspondent c ON r.correspondent_id = c.correspondent_id
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

        $reporter_id = $fn->getReqParam('reporter_id');
        $correspondent_id = $fn->getReqParam('correspondent_id');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "r.published = 1";
        }

        if ($reporter_id != "") {
            $searchVar->sqlSearchVar[] = "r.reporter_id = '{$reporter_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "r.reporter_id = '{$tv['record_id']}'";
        } else {
    
            if ($correspondent_id != '') {
                $searchVar->sqlSearchVar[] = "c.correspondent_id = '{$correspondent_id}'";
            }

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'r.reporter_id');
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "r.subscribe = 1";
            }
    
            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(r.subscribe != 1 OR r.subscribe IS null)";
            }
    
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "r.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(r.flag != 1 OR r.flag IS null)";
            }
    
            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "r.published = 1";
            }
    
            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "r.published = 0 OR r.published IS NULL OR r.published = ''";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    r.title       LIKE '%{$tv['keyword']}%'  OR
                    r.description LIKE '%{$tv['keyword']}%'
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
        $fa = $fn->addToFieldsArray($fa, 'correspondent_id');    
        $fa = $fn->addToFieldsArray($fa, 'published');    
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);

        return $fa;
    }

    /**
     *
     */

}
