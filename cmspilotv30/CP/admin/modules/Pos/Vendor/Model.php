<?
class CP_Admin_Modules_Pos_Vendor_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {

        $SQL = "
        SELECT v.*
        FROM vendor v
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'v';

   
        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "v.vendor_id = {$tv['record_id']}";

        } 
        
        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
            v.code LIKE '%{$tv['keyword']}%'
            OR v.title  LIKE '%{$tv['keyword']}%'
            )";
        }        

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the Vendor Name');

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
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        if ($tv['lang'] == 'eng') {
        $validate->validateData('title', 'Please enter the Vendor Name');
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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $rowVendor = $fn->getSettingsRowByKey('vendor');
        $vendor_id = $fn->getPostParam('vendor_id');
        $code = $fn->getPostParam('code');
        $length = $rowVendor['length'] - strlen($vendor_id);

        $i = 0;
        $extraNo = '';
        while ($i < $length) {
            $extraNo .= '0';
            $i++;
        } 
        $vendorCodeValue = $rowVendor['value'] . $extraNo . $vendor_id;

        if ($rowVendor['auto_generate_no'] == 1) {
            if ($rowVendor['add_separator'] == 1){
                $vendorCodeValue = $rowVendor['value'] . '-' . $extraNo . $vendor_id;
            }
            $fa['code'] = $vendorCodeValue;
        } else if ($rowVendor['auto_generate_no'] == 2) {
            if($code == ''){
                $fa['code'] = $vendorCodeValue;
            } else {
                $fa['code'] = $code;
            }
        } else {
            $fa['code'] = $code;
        }

        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'code', '', true);
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'address', '', true);
        $fa = $fn->addToFieldsArray($fa, 'contact_person', '', true);
        $fa = $fn->addToFieldsArray($fa, 'email', '', true);
        $fa = $fn->addToFieldsArray($fa, 'telephone', '', true);
        $fa = $fn->addToFieldsArray($fa, 'mobile', '', true);
        $fa = $fn->addToFieldsArray($fa, 'fax', '', true);
        $fa = $fn->addToFieldsArray($fa, 'default_currency', '', true);
        $fa = $fn->addToFieldsArray($fa, 'shipment_id', '', true);
        $fa = $fn->addToFieldsArray($fa, 'payment_id', '', true);
        $fa = $fn->addToFieldsArray($fa, 'shipment_via_id', '', true);
        $fa = $fn->addToFieldsArray($fa, 'status', '', true);


        return $fa;
    }

    /**
     *
     */
    function getVendorCodeSQL() {
        $SQL = "
        SELECT vendor_id
              ,code 
        FROM vendor
        ORDER BY code
        ";
        
        return $SQL;
    }
}
