<?
class CP_Admin_Modules_Party_Message_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT m.*
              ,ps.event_name AS party_title
        FROM message m
        LEFT JOIN (party_setup ps) ON (m.party_setup_id = ps.party_setup_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'm';

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "m.message_id  = '{$tv['record_id']}'";

        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       m.title LIKE '%{$tv['keyword']}%'
                    OR ps.event_name LIKE '%{$tv['keyword']}%'  
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

        $validate->validateData('party_setup_id', 'Please select the party title');

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

        $validate->validateData('party_setup_id', 'Please select the party title');

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
        $fn->returnAfterNewSave($id, 'detailFromEdit');
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'from_name');
        $fa = $fn->addToFieldsArray($fa, 'from_no');
        $fa = $fn->addToFieldsArray($fa, 'record_type');
        $fa = $fn->addToFieldsArray($fa, 'message');
        $fa = $fn->addToFieldsArray($fa, 'from_email');
        $fa = $fn->addToFieldsArray($fa, 'party_setup_id');

        return $fa;
    }
}
