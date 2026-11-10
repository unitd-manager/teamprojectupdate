<?
class CP_Admin_Modules_Ecommerce_ProductItem_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "SELECT pi.*
                FROM product_item pi
                JOIN product p on (p.product_id = pi.product_id)
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

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pi.product_item_id = '{$tv['record_id']}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "( pi.color_code  LIKE '%{$tv['keyword']}%' OR
                                            pi.sku_no      LIKE '%{$tv['keyword']}%'
                                          )";
        }

    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('product_id', 'Please enter the Product Name');
        $validate->validateData('sku_no', 'Please enter the SKU No');

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
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('sku_no', 'Please enter the SKU No');

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
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addToFieldsArray($fa, 'sku_no');
        $fa = $fn->addToFieldsArray($fa, 'color_code');
        $fa = $fn->addToFieldsArray($fa, 'size');
        $fa = $fn->addToFieldsArray($fa, 'stock');
        $fa = $fn->addToFieldsArray($fa, 'price');

        return $fa;
    }

    //==================================================================//
    function getImportData(){
        $lang = Zend_Registry::get('lang');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $fileName = realpath('../data/') . '/Product-Template.xls';

        //die($fileName);
        
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($fileName);
        $this->worksheet = $objPHPExcel->getActiveSheet();
        $countRows       = $this->worksheet->getHighestRow();
        $countCols       = $this->worksheet->getHighestColumn();

        for ($i = 'A'; $i <= $countCols; $i++) {
            $cellPos = $i . '1';
            $fieldName      = $this->worksheet->getCell($cellPos)->getValue();
            $fieldsArray[]  = $fieldName;
            $this->fieldsArrayPos[$fieldName] = $i;
        }

        for ($curRow = 2; $curRow <= $countRows; $curRow++) {
            $productCode  = trim($this->getExcelFieldValue('product code', $curRow));

            if ($productCode == ''){
                continue;
            }

            $SQL     = "SELECT * FROM product_item WHERE product_code = '{$dbUtil->replaceForDB($productCode)}'";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            $fa = array();


            $fa['product_code'] = $this->getExcelFieldValue('product code', $curRow);
            $fa['color_code']        = $this->getExcelFieldValue('colour code', $curRow);
            $fa['sku_no']  = $this->getExcelFieldValue('barcode', $curRow);
            $fa['size']        = $this->getExcelFieldValue('size', $curRow);
            $fa['stock']  = $this->getExcelFieldValue('stock', $curRow);

            if ($numRows > 0){
                $row = $db->sql_fetchrow($result);
                $fa['modification_date'] = date("Y-m-d H:i:s");
                $whereCondition = "WHERE product_item_id = {$row['product_item_id']}";
                $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "product_item", $whereCondition);
                $result         = $db->sql_query($SQL);

            } else {
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $SQL        = $dbUtil->getInsertSQLStringFromArray($fa, "product_item");
                $result     = $db->sql_query($SQL);
                $product_item_id = $db->sql_nextid();
            }

        }


        $text = "
        <h3>Import Complete.</h3>
        ";

        return $text;
    }

    //==================================================================//
    function getExcelFieldValue($fieldName, $rowNo, $emptyValue = ""){
      global $dbUtil;

      $fieldValue = "";
      require_once 'PHPExcel/RichText.php';

      $fieldsArrayPos = $this->fieldsArrayPos;

      $hasColumn =   array_key_exists($fieldName , $fieldsArrayPos) ? 1 : 0;

      if ($hasColumn){
         $cellPos    = array_key_exists($fieldName , $fieldsArrayPos) ? $fieldsArrayPos[$fieldName] : "";

         if ($cellPos != ""){
            $cellAbsPos = $cellPos . $rowNo;
            $fieldValue = $this->worksheet->getCell($cellAbsPos)->getValue();

            if (gettype($fieldValue)=="object") {
                $fieldValue = $fieldValue->getPlainText();
            }

            $fieldValue = trim($fieldValue);
         }
      }
      else {
         $fieldValue = $emptyValue;
      }

      return $fieldValue;
    }

}
