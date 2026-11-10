<?
class CP_Admin_Modules_AceIms_Resources_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     */
    function getSQL() {
        $SQL = "
        SELECT r.*               
        FROM resources r
        ";
        
        return $SQL;
    }

    /**
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $resources_id     = $fn->getReqParam('resources_id');

        if ($resources_id != "") {
            $searchVar->sqlSearchVar[] = "r.resources_id = '{$resources_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "r.resources_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'r.resources_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       r.book_name      LIKE '%{$tv['keyword']}%'
                    OR r.author         LIKE '%{$tv['keyword']}%'
                    OR r.description    LIKE '%{$tv['keyword']}%'
                )";
            }
        }        
    }

    /**
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('book_name', 'Please enter book name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
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
        return $this->getNewValidate();
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

        $fa = $fn->addToFieldsArray($fa, 'book_name');
        $fa = $fn->addToFieldsArray($fa, 'author');
        $fa = $fn->addToFieldsArray($fa, 'cost');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'no_of_book');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'book_available');
        
        return $fa;
    }

    /**
     */
    function getAceImsResourcesAceImsContactLinkSQL($id) {

        return "
        SELECT b.resources_contact_id
              ,CONCAT_WS(' ', a.first_name, a.last_name ) AS contact_name
              ,b.from_date
              ,b.to_date
              ,b.status
        FROM resources_contact b, contact a
        WHERE a.contact_id = b.contact_id 
          AND b.resources_id = {$id}
        ";
    }
}
