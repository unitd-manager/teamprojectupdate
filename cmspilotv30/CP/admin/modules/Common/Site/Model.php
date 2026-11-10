<?
class CP_Admin_Modules_Common_Site_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL   = "
        SELECT s.*
        FROM `site` s
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $searchVar->mainTableAlias = 's';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.site_id = {$tv['record_id']}";
        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    s.title LIKE '%{$tv['keyword']}%'
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

        $validate->validateData('title', 'Please enter the site name');

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
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if ($tv['lang'] == 'eng') {
            $validate->validateData('title', 'Please enter the site name');
        }

        $validate->validateData('default_language' , 'Please choose default language');

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
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'admin_email');
        $fa = $fn->addToFieldsArray($fa, 'site_url');
        $fa = $fn->addToFieldsArray($fa, 'site_url_alias');
        $fa = $fn->addToFieldsArray($fa, 'google_analytics_id');
        $fa = $fn->addToFieldsArray($fa, 'default_language');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'tag_line');
        $fa = $fn->addToFieldsArray($fa, 'tag_line2');
        $fa = $fn->addToFieldsArray($fa, 'theme');
        $fa = $fn->addToFieldsArray($fa, 'skin');
        $fa = $fn->addToFieldsArray($fa, 'site_key');
        $fa = $fn->addToFieldsArray($fa, 'ad_ops_site_name');
        $fa = $fn->addToFieldsArray($fa, 'additional_meta_tags');
        $fa = $fn->addToFieldsArray($fa, 'additional_analytics_script');
        $fa = $fn->addToFieldsArray($fa, 'caption', '', true);

        $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);

        return $fa;
    }

    /**
     *
     */
    function getSiteSQL() {

        $SQL = "
        SELECT s.site_id
              ,s.title
        FROM site s
       	WHERE s.published = 1
        ";

        return $SQL;
    }

    /**
     *
     */
    function getCommonSiteCommonLanguageLinkSQL($id) {
                
        $SQL = "
        SELECT l.language_id
              ,l.title
              ,l.lang_prefix
              ,l.published
        FROM language l
        WHERE l.site_id = {$id}                
        ";        

        return $SQL;
    }
}
