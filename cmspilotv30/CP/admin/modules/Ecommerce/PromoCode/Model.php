<?
class CP_Admin_Modules_Ecommerce_PromoCode_Model extends CP_Common_Modules_Ecommerce_PromoCode_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

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
        $validate->validateData('title', 'Please enter the title');

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
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'valid_vip');
        $fa = $fn->addToFieldsArray($fa, 'discount_type');
        $fa = $fn->addToFieldsArray($fa, 'discount_amount');
        $fa = $fn->addToFieldsArray($fa, 'expiry_date');
        $fa = $fn->addToFieldsArray($fa, 'start_date');
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'city_id');

        return $fa;
    }
    
    /**
     *
     */
    function getEcommercePromoCodeDirectoryCityLinkSQL($id) {
        $SQL = "
        SELECT pcc.promo_code_city_id
              ,c.title
              ,c.city_code
              ,co.title AS country_title
        FROM promo_code_city pcc
        JOIN city c ON (pcc.city_id = c.city_id)
        LEFT JOIN country co ON (c.country_id = co.country_id)
        WHERE pcc.promo_code_id = {$id}
        ORDER BY co.title, c.title    
        ";
        return $SQL;
    }    
}
