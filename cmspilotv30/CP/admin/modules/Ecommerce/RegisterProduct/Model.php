<?
class CP_Admin_Modules_Ecommerce_RegisterProduct_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT rp.*
              ,p.title AS product_name
        FROM `register_product` rp
        LEFT JOIN product p ON (p.product_id = rp.product_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'rp';

        $creation_date1 = $fn->getReqParam('creation_date1');
        $creation_date2 = $fn->getReqParam('creation_date2');
        $country_id     = $fn->getReqParam('country_id');

        if ($country_id != '') {
            $searchVar->sqlSearchVar[] = "rp.country_id = {$country_id}";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "rp.register_product_id = '{$tv['record_id']}'";
        } else {

            if ($creation_date1 != "" && $creation_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(rp.creation_date BETWEEN '{$creation_date1} 00:00:00' AND '{$creation_date2} 23:59:59')";
            }

            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(
                    rp.comments   LIKE '%{$tv['keyword']}%'
                 OR rp.first_name LIKE '%{$tv['keyword']}%'
                 OR rp.last_name  LIKE '%{$tv['keyword']}%'
                 OR rp.email      LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
        $searchVar = $fnModCountry->setCountrySearch($searchVar, 'rp');

        $searchVar->sortOrder = "rp.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

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
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'product_name');
        $fa = $fn->addToFieldsArray($fa, 'product_serial');
        $fa = $fn->addToFieldsArray($fa, 'comments');


        return $fa;
    }

}
