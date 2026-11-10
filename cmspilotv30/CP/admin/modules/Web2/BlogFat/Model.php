<?
class CP_Admin_Modules_Web2_BlogFat_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $tagsSQL = "(
        SELECT GROUP_CONCAT(DISTINCT t.tag_text ORDER BY t.tag_text SEPARATOR ',')
        FROM tags t, tags_history th
          WHERE th.record_id = b.blog_id
            AND t.tags_id    = th.tags_id
        ) AS tags,
        ";

        $SQL = "
        SELECT {$tagsSQL}
               b.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name 
        FROM  blog b
        LEFT JOIN contact c ON c.contact_id = b.contact_id
        ";

        
        return $SQL;
    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
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
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
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
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'published');
        
        return $fa;
    }

    /**
     *
     */
    function getBlogFatTagsLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL   = "
        SELECT t.tags_id
              ,t.tag_text
        FROM  tags t
        JOIN tags_history th ON (t.tags_id = th.tags_id)
        WHERE th.record_id ={$id}
          AND th.record_type = 'Blog'
        ORDER BY t.tag_text
        ";

        return $SQL;
    }
}
