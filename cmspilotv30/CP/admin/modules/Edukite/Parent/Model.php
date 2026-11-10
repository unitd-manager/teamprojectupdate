<?
class CP_Admin_Modules_Edukite_Parent_Model extends CP_Common_Modules_Edukite_Parent_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('email', 'Please enter the email');

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
        $validate->validateData('email', 'Please enter the email');

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
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'age');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_postal_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'published');

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
              'family_code'        => $phpExcel->getImportFldObj('Family Code')
             ,'first_name'         => $phpExcel->getImportFldObj('First Name')
             ,'last_name'          => $phpExcel->getImportFldObj('Family Name')
             ,'username'           => $phpExcel->getImportFldObj('User Name')
             ,'email'              => $phpExcel->getImportFldObj('Email')
             ,'pass_word'          => $phpExcel->getImportFldObj('Password')
             ,'academic_year'      => $phpExcel->getImportFldObj('Acadamic Year')
             ,'published'          => $phpExcel->getImportFldObj('Published')
             //,'parent_id'        => $phpExcel->getImportFldObj('Parent Id')
        );

        //$fa['academic_year']['defaultValue'] = date('Y');
        $fa['published']['defaultValue']     = 1;
        $fa['status']['defaultValue']     = 'Active';

        $config = array(
             'module'              => 'edukite_parent'
            //,'matchFieldArr'       => array('parent_id')
            ,'fldsArr'             => $fa
        );

        return $phpExcel->importData($config);
    }

}
