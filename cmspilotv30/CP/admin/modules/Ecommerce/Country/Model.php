<?
class CP_Admin_Modules_Ecommerce_Country_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL   = "
        SELECT c.*
        FROM `country` c
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

        $validate->validateData('title', 'Please enter the country name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        if($fn->getPostParam('admin_email') != ''){
            $validate->validateData('admin_email', 'Please enter valid email address', 'email');
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'currency_code');
        $fa = $fn->addToFieldsArray($fa, 'admin_email');
        $fa = $fn->addToFieldsArray($fa, 'shop_email');
        $fa = $fn->addToFieldsArray($fa, 'shop_currency');
        $fa = $fn->addToFieldsArray($fa, 'currency_display');
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $searchVar->mainTableAlias = 'c';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.country_id = {$tv['record_id']}";

        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }    

    /**
     *
     */
    function getCountryDDSQL() {
        $append = (CP_SCOPE == 'www') ? 'WHERE c.published = 1' : '';
        
        $SQL = "
        SELECT c.country_code
              ,c.title
        FROM country c
        {$append}
        ORDER BY c.title
        ";

        return $SQL;
    }
}
