<?
class CP_Admin_Modules_Directory_Contact_Model extends CP_Common_Modules_Directory_Contact_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');

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
        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');

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
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_first_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'city_id');
        $fa = $fn->addToFieldsArray($fa, 'area_id');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'alias_name');
        
        return $fa;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'first_name' => $phpExcel->getFldObj('First Name')
             ,'last_name' => $phpExcel->getFldObj('Last Name')
             ,'email' => $phpExcel->getFldObj('Email')
             ,'mobile' => $phpExcel->getFldObj('Mobile')
             ,'city_name' => $phpExcel->getFldObj('City')
             ,'country_name' => $phpExcel->getFldObj('Country')
             ,'cards_linked' => $phpExcel->getFldObj('Loyalty Card')
             ,'comment_count' => $phpExcel->getFldObj('Review Count')
             ,'subscribe' => $phpExcel->getFldObj('Subscribed')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    } 
}
