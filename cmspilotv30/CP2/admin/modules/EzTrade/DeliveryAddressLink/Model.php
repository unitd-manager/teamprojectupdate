<?
class CP_Admin_Modules_EzTrade_DeliveryAddressLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    //========================================================//
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('address_town', 'Please enter the City');
        $validate->validateData('address_country', 'Please enter the country');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    //==================================================================//
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    //========================================================//
    function getEditValidate() {
        return $this->getNewValidate();
    }

    //==================================================================//
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    //==================================================================//
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');

        return $fa;
    }
    /**
     *
     */
    function getShipToLocationSQL($company_id) {
        $sql = "
        SELECT delivery_address_id
              ,CONCAT_WS(', '
                         ,NULLIF(address_flat, '')
                         ,NULLIF(address_street, '')
                         ,NULLIF(address_town, '')
                         ,NULLIF(address_state, '')
                         ,NULLIF(address_country, '')
                         ,NULLIF(address_po_code, '')
                        ) AS address
        FROM  delivery_address a
        WHERE company_id = {$company_id}
        ORDER BY delivery_address_id
        ";
        return $sql;
    }

}