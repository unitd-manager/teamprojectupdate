<?
class CP_Admin_Modules_Tradingsg_Inventory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT i.*
              ,p.product_id AS productId
              ,p.title AS product_name
              ,p.model AS product_model
              ,p.carton_no AS product_carton_no
              ,p.batch_no AS product_batch_no
              ,p.item_code
              ,p.unit
              ,
                (SELECT SUM(qty) FROM po_product pop
                WHERE pop.product_id = p.product_id)
                -
                if(
                    (SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,(SELECT SUM(oi.qty) FROM order_item oi
                    LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                    WHERE oi.record_id = p.product_id
                      AND o.order_status = 'Paid'
                      AND o.record_type = 'POS'
                    )
                    ,''
                )
                -
                if(
                    (SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                    )
                    ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE invItem.record_id = p.product_id
                      AND o.record_type != 'POS'
                    )
                    ,''
                ) AS stock
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
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
        $searchVar->mainTableAlias = 'i';

        $inventory_id   = $fn->getReqParam('inventory_id');

        if ($inventory_id != "") {
            $searchVar->sqlSearchVar[] = "i.inventory_id = '{$inventory_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.inventory_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'i.inventory_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.title LIKE '%{$tv['keyword']}%'  OR
                    p.carton_no LIKE '%{$tv['keyword']}%'  OR
                    p.batch_no LIKE '%{$tv['keyword']}%'  OR
                    p.model LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            $searchVar->sortOrder = "p.item_code";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('company_name', 'Please enter the company name');

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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'code');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $db      = Zend_Registry::get('db');
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'product_name'     => $phpExcel->getFldObj('Product Name')
             ,'item_code'        => $phpExcel->getFldObj('Item Code')
             ,'stock'            => $phpExcel->getFldObj('Stock')

        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getImportData1(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'      => $phpExcel->getImportFldObj('Product Code')
             ,'title'             => $phpExcel->getImportFldObj('Title')
             ,'description_short' => $phpExcel->getImportFldObj('Short Description')
             ,'description'       => $phpExcel->getImportFldObj('Description')
             ,'picture'           => $phpExcel->getImportFldObj('Picture Ref')
             ,'published'         => $phpExcel->getImportFldObj('Published')
             ,'category_id'       => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'   => $phpExcel->getImportFldObj('Sub Category')
        );

        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Product');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        /****************************************/
        $config = array(
             'module'              => 'trading_company'
            ,'matchFieldArr'       => array('product_code')
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($product_id, $fa) {
        $media = Zend_Registry::get('media');

        if ($fa['picture'] != ''){
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            $media->model->createMedia('ecommerce_product', 'picture', $product_id, $exp);
        }
    }

}
