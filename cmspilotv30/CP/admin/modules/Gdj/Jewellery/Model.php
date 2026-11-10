<?
class CP_Admin_Modules_Gdj_Jewellery_Model extends CP_Common_Modules_Gdj_Jewellery_Model
{
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
        $fa['sort_order']  = $fn->getNextSortOrder('product');
        $fa['record_type'] = 'Jewelry';

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
            $validate->validateData('title', 'Please enter the title');
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'item_code');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'material');
        $fa = $fn->addToFieldsArray($fa, 'metal');
        $fa = $fn->addToFieldsArray($fa, 'color');
        $fa = $fn->addToFieldsArray($fa, 'stone');
        
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'qty_in_stock');
        
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'show_price');
        $fa = $fn->addToFieldsArray($fa, 'member_only');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'latest');

        if ($cpCfg['m.gdj.jewellery.showShortDescInJewellery'] == 1){
            $fa = $fn->addToFieldsArray($fa, 'description_short');
        }

        if ($cpCfg['m.gdj.jewellery.showMetaDataForJewellery'] == 1) {
            $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);
        }

        return $fa;
    }

    /**
     *
     */
    function getGdjJewelleryGdjJewelleryLinkSQL($id) {
        $SQL = "
        SELECT rp.related_product_id
              ,p.product_id
              ,p.title
              ,c.title AS category_title
        FROM related_product rp
        JOIN product p  ON (p.product_id = rp.product_id_rel)
        LEFT JOIN category c ON (c.category_id = p.category_id)
        WHERE rp.product_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'title'               => $phpExcel->getImportFldObj('Title')
             ,'category_id'         => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'     => $phpExcel->getImportFldObj('Sub Category')
             ,'item_code'           => $phpExcel->getImportFldObj('Item Code')
             ,'status'              => $phpExcel->getImportFldObj('Status')
             ,'material'            => $phpExcel->getImportFldObj('Material')
             ,'metal'               => $phpExcel->getImportFldObj('Metal')
             ,'color'               => $phpExcel->getImportFldObj('Color')
             ,'stone'               => $phpExcel->getImportFldObj('Stone')

             ,'price'               => $phpExcel->getImportFldObj('Price')
             ,'qty_in_stock'        => $phpExcel->getImportFldObj('Qty in stock')
        );

        $fa['published']['defaultValue']= 1;
        $fa['record_type']['defaultValue'] = 'Jewellery';
        
        /******** SPECIAL MANIPULATIONS ********/
        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Jewellery');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        $fa['status']['specialType'] = 'valuelist';
        $fa['status']['exp'] = array(
             'keyText' => 'jewelleryStatus'
        );

        $fa['material']['specialType'] = 'valuelist';
        $fa['material']['exp'] = array(
             'keyText' => 'jewelleryMaterial'
        );

        $fa['metal']['specialType'] = 'valuelist';
        $fa['metal']['exp'] = array(
             'keyText' => 'jewelleryMetal'
        );

        $fa['color']['specialType'] = 'valuelist';
        $fa['color']['exp'] = array(
             'keyText' => 'jewelleryColor'
        );

        /****************************************/
        $config = array(
             'module'        => 'gdj_jewellery'
            ,'matchFieldArr' => array('item_code')
            ,'fldsArr'       => $fa
        );

        return $phpExcel->importData($config);
    }
}
