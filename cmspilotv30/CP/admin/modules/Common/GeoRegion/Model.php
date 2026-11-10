<?
class CP_Admin_Modules_Common_GeoRegion_Model extends CP_Common_Modules_Common_GeoRegion_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('name', 'Please enter the region name');

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

        $validate->validateData('name', 'Please enter the region name');

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

        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'region_code');
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'description');

        return $fa;
    }

    /**
     *
     */
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'region_code'  => $phpExcel->getImportFldObj('Region Code')
             ,'name'         => $phpExcel->getImportFldObj('Title')
             ,'country_code' => $phpExcel->getImportFldObj('Country Code')
             ,'published'    => $phpExcel->getImportFldObj('Published')
        );

        $fa['published']['defaultValue'] = 1;

        $config = array(
             'module'        => 'common_geoRegion'
            ,'matchFieldArr' => array('region_code')
            ,'fldsArr'       => $fa
        );

        return $phpExcel->importData($config);
    }
}
