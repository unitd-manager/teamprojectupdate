<?
class CP_Admin_Modules_Directory_City_Model extends CP_Common_Modules_Directory_City_Model
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['m.directory.city.hasState']){
            $SQL = "
            SELECT c.*
            	  ,co.title AS country_name
            	  ,s.title AS state_title
            FROM city c
            LEFT JOIN (country co) ON (c.country_id = co.country_id)
            LEFT JOIN (state s) ON (c.state_id = s.state_id)
            ";
        } else {
            $SQL = "
            SELECT c.*
            	  ,co.title AS country_name
            FROM city c
            LEFT JOIN (country co) ON (c.country_id = co.country_id)
            ";
        }
        return $SQL;
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the city name');

        $expCode = array(
             'validationType' => 'regEx'
            ,'minLength' => 2
            ,'maxLength' => 2
            ,'ignoreEmpty' => true
            ,'regEx' => "/^[a-zA-Z][a-zA-Z]$/"
        );
        $validate->validateData2('city_code', 'City Code must be 2 letters.', $expCode);


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
        $fa['country_id'] = $fn->getSessionParam('cp_country_id');

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the city name');

        $expCode = array(
             'validationType' => 'regEx'
            ,'minLength' => 2
            ,'maxLength' => 2
            ,'ignoreEmpty' => true
            ,'regEx' => "/^[a-zA-Z][a-zA-Z]$/"
        );
        $validate->validateData2('city_code', 'City Code must be 2 letters.', $expCode);

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
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'city_code');
        $fa = $fn->addToFieldsArray($fa, 'state_id');

        return $fa;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('City')
             ,'state_title' => $phpExcel->getFldObj('State')
             ,'country_name' => $phpExcel->getFldObj('Country')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'c';

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id = $fn->getReqParam('state_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.city_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.city_id');
            if ($country_id != '' ) {
                $searchVar->sqlSearchVar[] = "c.country_id = '{$country_id}'";
            }

            if ($state_id != '' ) {
                $searchVar->sqlSearchVar[] = "c.state_id = '{$state_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.title   LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "c.title";
    }
}
