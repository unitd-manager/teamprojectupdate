<?
class CP_Admin_Modules_Pos_Discount_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {

        $SQL = "
        SELECT d.*
              ,i.title AS interest_title
        FROM discount d
        LEFT JOIN interest i ON (i.interest_id = d.interest_id)
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
        $searchVar->mainTableAlias = 'd';

        $title = $fn->getReqParam('title');
        $discount_id     = $fn->getReqParam('discount_id');
       
        if ($discount_id != "") {
            $searchVar->sqlSearchVar[] = "d.discount_id = '{$discount_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "d.discount_id = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'd.discount_id');

        }
            
        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
            d.code LIKE '%{$tv['keyword']}%'
            OR d.description  LIKE '%{$tv['keyword']}%'
            )";
        }
       
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('code', 'Please enter the code');

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
            $validate->validateData('code', 'Please enter the code');
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
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'start_date');
        $fa = $fn->addToFieldsArray($fa, 'start_time');
        $fa = $fn->addToFieldsArray($fa, 'end_date');
        $fa = $fn->addToFieldsArray($fa, 'end_time');
        $fa = $fn->addToFieldsArray($fa, 'type');
        $fa = $fn->addToFieldsArray($fa, 'interest_id');
        $fa = $fn->addToFieldsArray($fa, 'discount_percentage');
        $fa = $fn->addToFieldsArray($fa, 'less_amount');
        $fa = $fn->addToFieldsArray($fa, 'mix_qty_required');
        $fa = $fn->addToFieldsArray($fa, 'mix_rules');
        $fa = $fn->addToFieldsArray($fa, 'mix_currency');
        $fa = $fn->addToFieldsArray($fa, 'mix_amount_required');

        return $fa;
    }

    /**
     *
     */
    function getPosDiscountPosShopLinkSQL2($id) {

        return "
        SELECT a.shop_id
              ,a.title
        FROM shop a, discount_shop b
        WHERE a.discount_id = b.discount_id
        AND b.shop_id = {$id}
        ORDER BY title
        ";
    }

    /**
     *
     */
    function getPosDiscountPosShopLinkSQL1($id) {

        return "
        SELECT a.shop_id
              ,a.title
        FROM shop a
        WHERE a.shop_id = {$id}
        ORDER BY title
        ";
    }
}
