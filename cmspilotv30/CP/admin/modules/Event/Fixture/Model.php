<?
class CP_Admin_Modules_Event_Fixture_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT f.*
              ,ca.title AS category_title
              ,sc.title AS sub_category_title
              ,v.title  AS venue_title
        FROM fixture f
        LEFT JOIN category ca     ON (f.category_id     = ca.category_id)
        LEFT JOIN sub_category sc ON (f.sub_category_id = sc.sub_category_id)
        LEFT JOIN venue v         ON (f.venue_id        = v.venue_id)
        "; 

        
        return $SQL;
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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
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
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id'); 
        $fa = $fn->addToFieldsArray($fa, 'status');    
        $fa = $fn->addToFieldsArray($fa, 'opposite_team');    
        $fa = $fn->addToFieldsArray($fa, 'venue_id');    
        $fa = $fn->addToFieldsArray($fa, 'date');    
        $fa = $fn->addToFieldsArray($fa, 'time');    
        $fa = $fn->addToFieldsArray($fa, 'result');    
        $fa = $fn->addToFieldsArray($fa, 'published');    
        $fa = $fn->addToFieldsArray($fa, 'latest');    

        return $fa;
    }

    /**
     *
     */
    function getSQLContact() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name) AS contact_name
        FROM contact
        ORDER BY contact_name
        "; 

        return $SQL;
    }

    /**
     *
     */
    function getFixtureFixtureContactLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        
        $nameFld = ($tv['action'] == 'edit') ? 'fc.contact_id' : "CONCAT_WS(' ', c.first_name, c.last_name)";
        $SQL = "
        SELECT fc.fixture_contact_id
              ,{$nameFld}
              ,fc.status
              ,fc.position
              ,fc.tries
              ,fc.points
              ,fc.comment
        FROM fixture_contact fc
        LEFT JOIN contact c ON (fc.contact_id = c.contact_id) 
        WHERE fc.fixture_id = {$id}
        ORDER BY
        CASE
        WHEN (fc.status = 'In' AND fc.position > 0) THEN 1
        WHEN (fc.status = 'In') THEN 2
        WHEN (fc.status = 'May be') THEN 3
        WHEN (fc.status != '') THEN 4
        ELSE 5
        END, fc.position, fc.status, CONCAT_WS(' ', c.first_name, c.last_name)
        ";

        return $SQL;
    }
}
