<?
class CP_Admin_Modules_Wine_Grape_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL   = "
        SELECT g.*
        FROM grape g
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'g';


        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "g.grape_id = {$tv['record_id']}";

        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    g.title LIKE '%{$tv['keyword']}%'  
                    OR g.chi_title LIKE '%{$tv['keyword']}%'  
                    OR g.synonyms LIKE '%{$tv['keyword']}%'  
                    OR g.chi_synonyms LIKE '%{$tv['keyword']}%'  
                )";
            }
        }
        
        $searchVar->sortOrder = "g.sort_order ASC, g.title ASC";
    }
    
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the grape name');

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
        $fa['sort_order']   = $fn->getNextSortOrder('grape');
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the grape name');

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
        $fa = $fn->addToFieldsArray($fa, 'synonyms', '', true);
        $fa = $fn->addToFieldsArray($fa, 'show_in_nav');
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'title'            => $phpExcel->getImportFldObj('GRAPE E')
             ,'chi_title'        => $phpExcel->getImportFldObj('GRAPE C')
             ,'synonyms'         => $phpExcel->getImportFldObj('SYNONYMS E')
             ,'chi_synonyms'     => $phpExcel->getImportFldObj('SYNONYMS C')
             ,'published'        => $phpExcel->getImportFldObj('Published')

        );

        $fa['published']['defaultValue'] = 1;

        /****************************************/
        $config = array(
             'module'              => 'wine_grape'
            ,'matchFieldArr'       => array('title')
            ,'mandatoryFldsArr'    => array('title')
            ,'fldsArr'             => $fa
        );

        return $phpExcel->importData($config);
    }

}
