<?
class CP_Admin_Modules_Common_Country_Model extends CP_Common_Modules_Common_Country_Model
{
    /**
     *
     */
    function getNewValidate() {
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

        $validate->validateData('title', 'Please enter the country name');
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

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'admin_email');
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'idd_code');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'top_level_domain');
        $fa = $fn->addToFieldsArray($fa, 'default_country');
        $fa = $fn->addToFieldsArray($fa, 'default_language');
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }

    /**
     *
     */
    function getCountrySQL() {

        $SQL = "
        SELECT c.country_id
              ,c.title
        FROM country c
       	WHERE c.published = 1
        ";

        return $SQL;
    }

    /**
     *
     */
    function getCommonCountryCommonLanguageLinkSQL($id) {

        $SQL = "
        SELECT l.language_id
              ,l.title
              ,l.lang_prefix
              ,l.published
        FROM language l
        WHERE l.country_id = '{$id}'
        ORDER BY l.title
        ";

        return $SQL;
    }

    /**
     *
     */
    function getCountryLanguageLinkSQL($id) {
        $SQL = "
        SELECT a.language_id
              ,a.title
              ,a.lang_prefix
              ,(CASE
                    WHEN (a.published = 1)
                    THEN 'Yes'
                    ELSE 'No'
                    END) AS published
        FROM country b
            ,language a
        WHERE a.country_id = b.country_id
        AND b.country_id = {$id}
        ";

         return $SQL;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('title')
             ,'default_language' => $phpExcel->getFldObj('Default Language')
             ,'admin_email' => $phpExcel->getFldObj('Email')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }     
}
