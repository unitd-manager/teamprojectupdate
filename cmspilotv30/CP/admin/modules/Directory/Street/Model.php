<?
class CP_Admin_Modules_Directory_Street_Model extends CP_Common_Modules_Directory_Street_Model
{

    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the street name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

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

    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the street name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

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

    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'state_id');
        $fa = $fn->addToFieldsArray($fa, 'city_id');
        $fa = $fn->addToFieldsArray($fa, 'borough_id');
        $fa = $fn->addToFieldsArray($fa, 'area_id');
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('Street')
             ,'area_name' => $phpExcel->getFldObj('Area')
             ,'borough_title' => $phpExcel->getFldObj('Borough')
             ,'city_name' => $phpExcel->getFldObj('City')
             ,'state_name' => $phpExcel->getFldObj('State')
             ,'country_name' => $phpExcel->getFldObj('Country')
        );

        $config = array(
             'fldsArr' => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_street&_spAction=importData
     */
    function getImportDataHK(){
        $fn = Zend_Registry::get('fn');
        //die();
        
        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        $country = 'Hong Kong';
        
        $countrySQL = "
        SELECT country_id
        FROM country
        WHERE title = '{$country}'
        ";
        $rowCountry = $fn->getRecordBySQL($countrySQL);

        $state = 'Kowloon';
        //$state = 'Hong Kong Island';
        $stateSQL = "
        SELECT state_id
        FROM state
        WHERE title = '{$state}'
        ";
        $rowState = $fn->getRecordBySQL($stateSQL);

        $city = 'Kowloon';
        //$city = 'Hong Kong Island';
        $citySQL = "
        SELECT city_id
        FROM city
        WHERE title = '{$city}'
        ";
        $rowCity = $fn->getRecordBySQL($citySQL);

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $fa = array(
              'title' => $phpExcel->getImportFldObj('English')
             ,'chi_title' => $phpExcel->getImportFldObj('Chinese')
             ,'pin_title' => $phpExcel->getImportFldObj('Pinyin')
             ,'country_id' => $phpExcel->getImportFldObj('Country')
             ,'state_id' => $phpExcel->getImportFldObj('State')
             ,'city_id' => $phpExcel->getImportFldObj('City')
             ,'published' => $phpExcel->getImportFldObj('Published')
        );
        $fa['country_id']['defaultValue'] = $rowCountry['country_id'];
        $fa['state_id']['defaultValue']   = '';
        $fa['city_id']['defaultValue']    = '';
        $fa['published']['defaultValue']  = 1;

        //$excelFile = realpath('../../resources/data/KowloonStreets.xls');
        $excelFile = realpath('../../resources/data/HK Data/Final/SBR - Streets.xls');
        $excelFile = realpath('../../resources/data/Macau Data//MacauStreets.xls');

        $config = array(
             'module' => 'directory_street'
            ,'matchFieldArr' => array('country_id', 'title')
            ,'fldsArr' => $fa
            ,'excelFilePath' => $excelFile
            ,'callbackAfterImport' => 'callbackAfterImport'
        );
        return $phpExcel->importData($config);
    }

    function getImportData(){
        $fn = Zend_Registry::get('fn');
        //die();
        
        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        $country = 'Macau';
        
        $countrySQL = "
        SELECT country_id
        FROM country
        WHERE title = '{$country}'
        ";
        $rowCountry = $fn->getRecordBySQL($countrySQL);

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $fa = array(
              'title' => $phpExcel->getImportFldObj('English')
             ,'chi_title' => $phpExcel->getImportFldObj('Chinese')
             ,'pin_title' => $phpExcel->getImportFldObj('Pinyin')
             ,'country_id' => $phpExcel->getImportFldObj('Country')
             ,'state_id' => $phpExcel->getImportFldObj('State')
             ,'area_id' => $phpExcel->getImportFldObj('Area')
             ,'published' => $phpExcel->getImportFldObj('Published')
        );
        $fa['country_id']['defaultValue'] = $rowCountry['country_id'];
        $fa['published']['defaultValue']  = 1;

        $fa['state_id']['exp']['extraFldsOnCreation'] = array(
            'country_id',
        );
        $fa['state_id']['exp']['refModule'] = 'directory_state';
        $fa['state_id']['specialType'] = 'fetchIdFromRefModule';
        
        $fa['area_id']['exp']['extraFldsOnCreation'] = array(
            'country_id',
        );
        $fa['area_id']['exp']['refModule'] = 'directory_area';
        $fa['area_id']['specialType'] = 'fetchIdFromRefModule';

        $excelFile = realpath('../../resources/data/Macau Data/MacauStreets.xls');

        $config = array(
             'module' => 'directory_street'
            ,'matchFieldArr' => array('country_id', 'title')
            ,'fldsArr' => $fa
            ,'excelFilePath' => $excelFile
            ,'callbackAfterImport' => 'callbackAfterImport'
        );
        return $phpExcel->importData($config);
    }

    function callbackAfterImport() {
        $db = Zend_Registry::get('db');

        $SQL = "
        UPDATE street
        SET title = proper(title);
        ";
        $db->sql_query($SQL);
    }
}
