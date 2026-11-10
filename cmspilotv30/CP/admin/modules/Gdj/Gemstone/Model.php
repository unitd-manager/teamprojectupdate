<?
class CP_Admin_Modules_Gdj_Gemstone_Model extends CP_Common_Modules_Gdj_Gemstone_Model
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
        $fa['record_type'] = 'Gemstones';

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
        
        $fa = $fn->addToFieldsArray($fa, 'shape');
        $fa = $fn->addToFieldsArray($fa, 'color');
        $fa = $fn->addToFieldsArray($fa, 'carat');
        $fa = $fn->addToFieldsArray($fa, 'measurement');
        $fa = $fn->addToFieldsArray($fa, 'cut');
        $fa = $fn->addToFieldsArray($fa, 'origin');
        $fa = $fn->addToFieldsArray($fa, 'type');
        $fa = $fn->addToFieldsArray($fa, 'hardness');
        $fa = $fn->addToFieldsArray($fa, 'luster');
        $fa = $fn->addToFieldsArray($fa, 'treatment');
        $fa = $fn->addToFieldsArray($fa, 'lab');
        
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'cost_a');
        $fa = $fn->addToFieldsArray($fa, 'cost_b');
        $fa = $fn->addToFieldsArray($fa, 'margin_b');        
        $fa['asking_price'] = ((($fa['cost_a'] + $fa['cost_b'])*$fa['margin_b'])/100);        
        $fa = $fn->addToFieldsArray($fa, 'pieces_qty');
        $fa = $fn->addToFieldsArray($fa, 'qty_in_stock');
        $fa = $fn->addToFieldsArray($fa, 'item_location');
        $fa = $fn->addToFieldsArray($fa, 'supplier');
        $fa = $fn->addToFieldsArray($fa, 'supplier_code');
        
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'show_price');
        $fa = $fn->addToFieldsArray($fa, 'member_only');
        $fa = $fn->addToFieldsArray($fa, 'latest');
        
        if ($cpCfg['m.gdj.gemstone.showShortDescInGemstone'] == 1){
            $fa = $fn->addToFieldsArray($fa, 'description_short');
        }

        $fa = $fn->addToFieldsArray($fa, 'description');

        if ($cpCfg['m.gdj.gemstone.showMetaDataForGemstone'] == 1) {
            $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);
        }
        
        return $fa;
    }

    /**
     *
     */
    function getGdjGemstoneGdjGemstoneLinkSQL($id) {
        return $SQL = "
        SELECT rp.related_product_id
              ,p.title
              ,c.title AS category_title
              ,p.product_id
        FROM related_product rp
        JOIN product p  ON (p.product_id = rp.product_id_rel)
        JOIN category c ON (c.category_id = p.category_id)
        WHERE rp.product_id = {$id}
        ";
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

             ,'shape'               => $phpExcel->getImportFldObj('Shape')
             ,'color'               => $phpExcel->getImportFldObj('Color')
             ,'carat'               => $phpExcel->getImportFldObj('Carat')
             ,'measurement'         => $phpExcel->getImportFldObj('Measurement')
             ,'cut'                 => $phpExcel->getImportFldObj('Cut')
             ,'type'                => $phpExcel->getImportFldObj('Type')
             ,'hardness'            => $phpExcel->getImportFldObj('Hardness')
             ,'luster'              => $phpExcel->getImportFldObj('Luster')
             ,'treatment'           => $phpExcel->getImportFldObj('Treatment')
             ,'lab'                 => $phpExcel->getImportFldObj('Lab')

             ,'price'               => $phpExcel->getImportFldObj('Price')
             ,'cost_a'              => $phpExcel->getImportFldObj('Cost A')
             ,'cost_b'              => $phpExcel->getImportFldObj('Cost B')
             ,'margin_b'            => $phpExcel->getImportFldObj('Margin B')
             ,'asking_price'        => $phpExcel->getImportFldObj('Asking Price')
             ,'pieces_qty'          => $phpExcel->getImportFldObj('Pieces / Qty')
             ,'qty_in_stock'        => $phpExcel->getImportFldObj('Qty in stock')
             ,'item_location'       => $phpExcel->getImportFldObj('Item Location')
             ,'supplier'            => $phpExcel->getImportFldObj('Supplier')
             ,'supplier_code'       => $phpExcel->getImportFldObj('Supplier Code')
             ,'published'       => $phpExcel->getImportFldObj('Published')
        );

        $fa['published']['defaultValue']= 1;
        $fa['record_type']['defaultValue'] = 'Gemstone';
        
        /******** SPECIAL MANIPULATIONS ********/
        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Gemstone');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        $fa['status']['specialType'] = 'valuelist';
        $fa['status']['exp'] = array(
             'keyText' => 'gemstoneStatus'
        );

        $fa['shape']['specialType'] = 'valuelist';
        $fa['shape']['exp'] = array(
             'keyText' => 'gemstoneShape'
        );

        $fa['color']['specialType'] = 'valuelist';
        $fa['color']['exp'] = array(
             'keyText' => 'gemstoneColor'
        );

        $fa['cut']['specialType'] = 'valuelist';
        $fa['cut']['exp'] = array(
             'keyText' => 'gemstoneCut'
        );

        $fa['origin']['specialType'] = 'valuelist';
        $fa['origin']['exp'] = array(
             'keyText' => 'gemstoneOrigin'
        );

        $fa['type']['specialType'] = 'valuelist';
        $fa['type']['exp'] = array(
             'keyText' => 'gemstoneType'
        );

        $fa['hardness']['specialType'] = 'valuelist';
        $fa['hardness']['exp'] = array(
             'keyText' => 'gemstoneHardness'
        );

        $fa['luster']['specialType'] = 'valuelist';
        $fa['luster']['exp'] = array(
             'keyText' => 'gemstoneLuster'
        );

        $fa['treatment']['specialType'] = 'valuelist';
        $fa['treatment']['exp'] = array(
             'keyText' => 'gemstoneTreatment'
        );

        $fa['lab']['specialType'] = 'valuelist';
        $fa['lab']['exp'] = array(
             'keyText' => 'gemstoneLab'
        );

        $fa['item_location']['specialType'] = 'valuelist';
        $fa['item_location']['exp'] = array(
             'keyText' => 'gemstoneItemLocation'
        );

        /****************************************/
        $config = array(
             'module'        => 'gdj_gemstone'
            ,'matchFieldArr' => array('item_code')
            ,'fldsArr'       => $fa
        );

        return $phpExcel->importData($config);
    }
}
