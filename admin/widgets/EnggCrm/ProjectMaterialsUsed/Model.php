<?
class CPL_Admin_Widgets_EnggCrm_ProjectMaterialsUsed_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;        
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_projectMaterialsUsed');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getEditForMaterialUsedValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $product_id    = $fn->getPostParam('product_id');
        $quantity      = $fn->getPostParam('quantity');
        $materialStock = $fn->getReqParam('materialStock');

        $validate->resetErrorArray();

        if($materialStock == "") {
            $materialStock = 0;
        }

        if($quantity > $materialStock) {
            $validate->errorArray['quantity']['name'] = "quantity";
            $validate->errorArray['quantity']['msg']  = "Please enter quantity less than or equal to ".$materialStock;
        }

        if($product_id == "") {
            $validate->errorArray['title']['name'] = "title";
            $validate->errorArray['title']['msg']  = "Please search and select product";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Material Used Edit Form Submit
     */
    function getEditForMaterialUsedSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getEditForMaterialUsedValidate()){
            return $validate->getErrorMessageXML();
        }

        $project_id           = $fn->getReqParam('project_id');
        $project_materials_id = $fn->getReqParam('project_materials_id');

        $SQLPM = "
        SELECT pm.*
        FROM project_materials pm
        WHERE pm.project_materials_id = {$project_materials_id}
        ";
        $resultPM = $db->sql_query($SQLPM);
        $rowPM    = $db->sql_fetchrow($resultPM);
                         
        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'material_used_date');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'unit');
        $fa = $fn->addToFieldsArray($fa, 'quantity');
        //$fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'project_materials');

        $whereCondition = "WHERE project_materials_id = {$project_materials_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "project_materials", $whereCondition);
        $db->sql_query($SQL);

        $quantity   = $fn->getPostParam('quantity');
        $product_id = $fn->getPostParam('product_id');
        $totalQty   = $quantity - $rowPM['quantity'];

        $SQLInventory = "
        SELECT product_id
              ,actual_stock
              ,inventory_id
        FROM inventory
        WHERE product_id = '{$product_id}'
        ";
        $resultInventory  = $db->sql_query($SQLInventory);
        $rowInventory     = $db->sql_fetchrow($resultInventory);

        $stockCalc = $rowInventory['actual_stock'] - $totalQty;

        $fa2 = array();
        $fa2['product_id']     = $product_id;
        $fa2['inventory_id']   = $rowInventory['inventory_id'];
        $fa2['materials_used'] = $quantity;
        $fa2['current_stock']  = $rowInventory['actual_stock'];
        $fa2       = $fn->addCreationDetailsToFieldsArray($fa2, 'adjust_stock_log');
        $SQLLog    = $dbUtil->getInsertSQLStringFromArray($fa2, 'adjust_stock_log');
        $resultLog = $db->sql_query($SQLLog);

        $SQLUpdateProduct = "
        UPDATE product SET qty_in_stock = {$stockCalc}
        WHERE product_id = '{$product_id}'
        ";
        $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

        $SQLUpdateInventory = "
        UPDATE inventory SET actual_stock = {$stockCalc}
        WHERE product_id = '{$product_id}'
        ";
        $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddMultipleMaterialsValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $title_arr = $fn->getPostParam('title', array());

        $validate->resetErrorArray();

        $filterArray1 = array_filter($title_arr);
        if (count($filterArray1) == 0) {
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please enter details in atlease 1 item";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add Materials submit in new window
     */
    function getAddMultipleMaterialsSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id      = $fn->getPostParam('project_id');
        //$part_no_arr     = $fn->getPostParam('partno', array());
        $title_arr       = $fn->getPostParam('title', array());
        $amount_arr      = $fn->getPostParam('amount', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());
        $unit_arr        = $fn->getPostParam('unit', array());
        $product_id_arr  = $fn->getPostParam('product_id', array());
        $description_arr = $fn->getPostParam('description', array());
        $virescoFactory_arr = $fn->getPostParam('virescoFactory', array());

        if (!$this->getAddMultipleMaterialsValidate()){
            return $validate->getErrorMessageXML();
        }

        $filterArray  = array_filter($title_arr);
        $count        = count($filterArray);
        $filterArray2 = array_filter($virescoFactory_arr);

        for ($i= 0; $i < $count; $i++) {
            //$part_no        = $part_no_arr[$i];
            $title            = $title_arr[$i];
            //$amount           = $amount_arr[$i];
            $quantity         = $quantity_arr[$i];
            $unit             = $unit_arr[$i];
            $description      = $description_arr[$i];
            $product_id       = $product_id_arr[$i];
            
            $viresco_factory = "";
            if(count($filterArray2) > 0) {
                $viresco_factory  = $virescoFactory_arr[$i];
            }
        
            if ($title) {
                $fa = array();
                $fa['project_id']         = $project_id;
                //$fa['part_no']            = $part_no;
                $fa['title']              = $title;
                //$fa['amount']             = $amount;
                $fa['quantity']           = $quantity;
                $fa['unit']               = $unit;
                $fa['description']        = $description;
                $fa['product_id']         = $product_id;
                $fa['viresco_factory']    = $viresco_factory;
                $fa['status']             = 'Used';
                $fa['material_used_date'] = date('Y-m-d');
                $fa['creation_date']      = date('Y-m-d H:i:s');
                $fa['created_by']         = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'project_materials');
                $result = $db->sql_query($insert);
                $project_materials_id = $db->sql_nextid();

                $SQLInventory = "
                SELECT product_id
                      ,actual_stock
                      ,inventory_id
                FROM inventory
                WHERE product_id = '{$product_id}'
                ";
                $resultInventory  = $db->sql_query($SQLInventory);
                $rowInventory     = $db->sql_fetchrow($resultInventory);

                $stockCalc = $rowInventory['actual_stock'] - $quantity;

                $fa2 = array();
                $fa2['product_id']     = $product_id;
                $fa2['inventory_id']   = $rowInventory['inventory_id'];
                $fa2['materials_used'] = $quantity;
                $fa2['current_stock']  = $rowInventory['actual_stock'];
                $fa2       = $fn->addCreationDetailsToFieldsArray($fa2, 'adjust_stock_log');
                $SQLLog    = $dbUtil->getInsertSQLStringFromArray($fa2, 'adjust_stock_log');
                $resultLog = $db->sql_query($SQLLog);

                $SQLUpdateProduct = "
                UPDATE product SET qty_in_stock = {$stockCalc}
                WHERE product_id = '{$product_id}'
                ";
                $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                $SQLUpdateInventory = "
                UPDATE inventory SET actual_stock = {$stockCalc}
                WHERE product_id = '{$product_id}'
                ";
                $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCreationModificationMU() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $dateUtil  = Zend_Registry::get('dateUtil');

        $project_materials_id = $fn->getReqParam('project_materials_id');

        $header = "
        <thead>
            <tr>
                <td>Created By/Creation Date</td>
                <td>Modified By/Modification Date</td>
            </tr>
        </thead>
        ";

        $SQLPO ="
        SELECT q.creation_date
              ,q.created_by
              ,q.modification_date
              ,q.modified_by
        FROM project_materials q
        WHERE q.project_materials_id = {$project_materials_id}
        ";
        $resultPo = $db->sql_query($SQLPO);
        $row    = $db->sql_fetchrow($resultPo);

        if($row['modified_by'] != ""){
            $modified_by = "{$row['modified_by']} - <br/>". $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
        }else{
            $modified_by = "";
        }

        if($row['created_by'] != ""){
            $created_by = "{$row['created_by']} - <br/>". $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
        }else{
            $created_by = "";
        }

        $rows = "
        <tbody>
            <tr>
                <td>{$created_by}</td>
                <td>{$modified_by}</td>
            </tr>
        </tbody>
        ";

        $text = "
        <form id='creationModificationPo' class='creationModificationPo' method='post'>
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getSearchProductTitle() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title) AS label
              ,p.category_id AS category
              ,p.product_type
              ,(SELECT i.actual_stock
                FROM inventory i
                WHERE i.product_id = p.product_id) AS stock
        FROM product p
        WHERE (p.title LIKE '{$productTitle}%')
          AND p.published = 1
        ORDER BY p.title
        ";

        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     * Update Material status to Cancelled
     */
    function getCancelMaterial() {
        $fn = Zend_Registry::get('fn');

        $project_materials_id = $fn->getReqParam('project_materials_id');        
        /* Update Project material status */
        $faPm = array();
        $faPm['status']   = 'Cancelled';
        $faPm['modification_date'] = date('Y-m-d H:i:s');
        $faPm['modified_by'] = $fn->getSessionParam('userName');
        $fn->saveRecord($faPm, 'project_materials', 'project_materials_id', $project_materials_id);
    }

    /**
     *
     */
    function getReturnMaterialUsedValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $quantity      = $fn->getPostParam('quantity');
        $qtyValidate = $fn->getReqParam('qtyValidate');

        $validate->resetErrorArray();
        $validate->validateData('quantity', 'Please enter quantity');

        if($qtyValidate == "") {
            $qtyValidate = 0;
        }

        if($quantity > $qtyValidate) {
            $validate->errorArray['quantity']['name'] = "quantity";
            $validate->errorArray['quantity']['msg']  = "Please enter quantity less than or equal to ".$qtyValidate;
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Material Used Edit Form Submit
     */
    function getReturnMaterialUsedSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getReturnMaterialUsedValidate()){
            return $validate->getErrorMessageXML();
        }

        $project_id           = $fn->getReqParam('project_id');
        $project_materials_id = $fn->getReqParam('project_materials_id');                         
        $product_id = $fn->getPostParam('product_id');
        $quantity = $fn->getPostParam('quantity');

        $fa2 = array();
        $fa2['product_id']     = $product_id;
        $fa2['quantity']   = $quantity;
        $fa2['project_materials_id'] = $project_materials_id;
        $fa2       = $fn->addCreationDetailsToFieldsArray($fa2, 'materials_returned');
        $SQLLog    = $dbUtil->getInsertSQLStringFromArray($fa2, 'materials_returned');
        $resultLog = $db->sql_query($SQLLog);

        $SQLInventory = "
        SELECT product_id
              ,actual_stock
              ,inventory_id
        FROM inventory
        WHERE product_id = '{$product_id}'
        ";
        $resultInventory  = $db->sql_query($SQLInventory);
        $rowInventory     = $db->sql_fetchrow($resultInventory);

        $stockCalc = $rowInventory['actual_stock'] + $quantity;

        $SQLUpdateProduct = "
        UPDATE product SET qty_in_stock = {$stockCalc}
        WHERE product_id = '{$product_id}'
        ";
        $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

        $SQLUpdateInventory = "
        UPDATE inventory SET actual_stock = {$stockCalc}
        WHERE product_id = '{$product_id}'
        ";
        $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);

        return $validate->getSuccessMessageXML();
    }

    /**
     * Update Material Viresco factory
     */
    function getUpdateVirescoFactory() {
        $fn = Zend_Registry::get('fn');

        $project_materials_id = $fn->getReqParam('project_materials_id');        
        $checkedVal = $fn->getReqParam('checkedVal');

        $faPm = array();
        $faPm['viresco_factory']   = $checkedVal;
        $fn->saveRecord($faPm, 'project_materials', 'project_materials_id', $project_materials_id);
    }
}