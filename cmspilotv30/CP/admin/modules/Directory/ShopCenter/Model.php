<?
class CP_Admin_Modules_Directory_ShopCenter_Model extends CP_Common_Modules_Directory_ShopCenter_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the shop center name');

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
        $validate->validateData('title', 'Please enter the shop center name');

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
        $fa = $fn->addToFieldsArray($fa, 'state_id');
        $fa = $fn->addToFieldsArray($fa, 'city_id');
        $fa = $fn->addToFieldsArray($fa, 'borough_id');
        $fa = $fn->addToFieldsArray($fa, 'area_id');
        $fa = $fn->addToFieldsArray($fa, 'street_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        return $fa;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
     
        $fa = array(
              'title' => $phpExcel->getFldObj('Shop Center')
             ,'country_title' => $phpExcel->getFldObj('Country')
             ,'state_title' => $phpExcel->getFldObj('State')
             ,'city_title' => $phpExcel->getFldObj('City')
             ,'area_title' => $phpExcel->getFldObj('Area')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    } 
}
