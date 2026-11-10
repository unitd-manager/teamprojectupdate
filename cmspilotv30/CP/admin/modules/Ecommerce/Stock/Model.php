<?
class CP_Admin_Modules_Ecommerce_Stock_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        
        $SQL = "
        SELECT pi.*
              ,p.title AS product_title
              ,(SELECT SUM(qty)
                FROM stock_in
                WHERE product_item_id = pi.product_item_id
              )AS stock_in_total
              ,(SELECT SUM(qty)
                FROM order_item
                WHERE child_id = pi.product_item_id
              )AS stock_out_total 
        FROM `product_item` pi
        LEFT JOIN (product p) ON (p.product_id = pi.product_id)
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
        $searchVar->mainTableAlias = 'pi';

        $product_id = $fn->getReqParam('product_id');
        $product_code = $fn->getReqParam('product_code');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pi.product_item_id = '{$tv['record_id']}'";
        } else {

            if ($product_id != '') {
                $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";
            }

            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(   
                    pi.sku_no   LIKE '%{$tv['keyword']}%'
                 OR p.title LIKE '%{$tv['keyword']}%'
                )";
            }
                        
        }
        
        $searchVar->sortOrder = "pi.creation_date DESC";
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

        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addToFieldsArray($fa, 'sku_no');
        $fa = $fn->addToFieldsArray($fa, 'stock');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');

        return $fa;
    }

}
