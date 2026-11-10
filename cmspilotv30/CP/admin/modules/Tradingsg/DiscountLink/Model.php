<?
class CP_Admin_Modules_Tradingsg_DiscountLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getNewValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        $product_group_id = $fn->getReqParam('product_group_id');
        $company_id = $fn->getReqParam('company_id');
        $category_id = $fn->getReqParam('category_id');

        $validate->resetErrorArray();

        if ($category_id != '') {
            $appendSql = "AND category_id = {$category_id}";
        } else {
            $appendSql = "AND category_id IS NULL";
        }

        if ($product_group_id) {
            $sql = "
            SELECT *
            FROM discount
            WHERE company_id = {$company_id}
              AND product_group_id = {$product_group_id}
              {$appendSql}
            ";
            $result = $db->sql_query($sql);
            $numRows = $db->sql_numrows($result);

            if ($numRows) {
                $msg = 'Program group already linked to the company';
                $validate->validateData('error_box', $msg);
            }
        }

        if ($cpCfg['m.tradingsg.discountLink.showDiscount']) {
            $validate->validateData('product_group_id', 'Please select product group');
            $validate->validateData('margin', 'Please enter margin percent');
            $validate->validateData('discount_percent', 'Please enter discount percent');
        } else {
            $validate->validateData('product_group_id', 'Please select product group');
            $validate->validateData('margin', 'Please enter margin percent');
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

    /**
     *
     */
    function getEditPortalValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        $product_group_id = $fn->getReqParam('product_group_id');
        $company_id = $fn->getReqParam('company_id');
        $category_id = $fn->getReqParam('category_id');

        $validate->resetErrorArray();
        if ($product_group_id) {
            $sql = "
            SELECT *
            FROM discount
            WHERE company_id = {$company_id}
              AND product_group_id = {$product_group_id}
              AND category_id = {$category_id}
            ";
            $result = $db->sql_query($sql);
            $numRows = $db->sql_numrows($result);

        }

        if ($cpCfg['m.tradingsg.discountLink.showDiscount']) {
            $validate->validateData('margin', 'Please enter margin percent');
            $validate->validateData('discount_percent', 'Please enter discount percent');
        } else {
            $validate->validateData('margin', 'Please enter margin percent');
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
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditPortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'product_group_id');
        $fa = $fn->addToFieldsArray($fa, 'margin');
        $fa = $fn->addToFieldsArray($fa, 'discount_percent');
        $fa = $fn->addToFieldsArray($fa, 'category_id');

        return $fa;
    }
}
