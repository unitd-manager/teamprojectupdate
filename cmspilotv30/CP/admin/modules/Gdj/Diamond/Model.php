<?
class CP_Admin_Modules_Gdj_Diamond_Model extends CP_Common_Modules_Gdj_Diamond_Model
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
        $fa['record_type'] = 'Diamonds';

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

        $discount = $fn->getReqParam('discount');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'item_code');
        $fa = $fn->addToFieldsArray($fa, 'status');
        
        $fa = $fn->addToFieldsArray($fa, 'shape');
        $fa = $fn->addToFieldsArray($fa, 'lab');
        $fa = $fn->addToFieldsArray($fa, 'color');
        $fa = $fn->addToFieldsArray($fa, 'carat');
        $fa = $fn->addToFieldsArray($fa, 'clarity');
        $fa = $fn->addToFieldsArray($fa, 'polish');
        $fa = $fn->addToFieldsArray($fa, 'symmetry');
        $fa = $fn->addToFieldsArray($fa, 'girdle');
        $fa = $fn->addToFieldsArray($fa, 'fluorescence');
        $fa = $fn->addToFieldsArray($fa, 'culet');
        $fa = $fn->addToFieldsArray($fa, 'cut');
        
        $fa = $fn->addToFieldsArray($fa, 'table');
        $fa = $fn->addToFieldsArray($fa, 'height');
        $fa = $fn->addToFieldsArray($fa, 'depth');
        $fa = $fn->addToFieldsArray($fa, 'measurement');
        
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'rap_price');
        $fa = $fn->addToFieldsArray($fa, 'less_price');
        
        if ($discount == '') {
            $fa['discount'] = (($fa['rap_price'] * $fa['less_price'])/100);        
        } else {
            $fa = $fn->addToFieldsArray($fa, 'discount');
        }

        $fa['total'] = ($fa['rap_price'] - $fa['discount']);        

        $fa = $fn->addToFieldsArray($fa, 'qty_in_stock');
        $fa = $fn->addToFieldsArray($fa, 'supplier');
        $fa = $fn->addToFieldsArray($fa, 'supplier_code');
        
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'show_price');
        $fa = $fn->addToFieldsArray($fa, 'member_only');
        $fa = $fn->addToFieldsArray($fa, 'latest');
        
        $fa = $fn->addToFieldsArray($fa, 'description');

        if ($cpCfg['m.gdj.diamond.showShortDescInDiamond'] == 1){
            $fa = $fn->addToFieldsArray($fa, 'description_short');
        }

        if ($cpCfg['m.gdj.diamond.showMetaDataForDiamond'] == 1) {
            $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);
        }
 
        return $fa;
    }

    /**
     *
     */
    function getGdjDiamondGdjDiamondLinkSQL($id) {
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

             ,'shape'               => $phpExcel->getImportFldObj('Shape')
             ,'lab'                 => $phpExcel->getImportFldObj('Lab')
             ,'color'               => $phpExcel->getImportFldObj('Color')
             ,'carat'               => $phpExcel->getImportFldObj('Carat')
             ,'clarity'             => $phpExcel->getImportFldObj('Clarity')
             ,'polish'              => $phpExcel->getImportFldObj('Polish')
             ,'symmetry'            => $phpExcel->getImportFldObj('Symmetry')
             ,'girdle'              => $phpExcel->getImportFldObj('Girdle')
             ,'fluorescence'        => $phpExcel->getImportFldObj('Fluorescence')
             ,'culet'               => $phpExcel->getImportFldObj('Culet')
             ,'cut'                 => $phpExcel->getImportFldObj('Cut')

             ,'table'               => $phpExcel->getImportFldObj('Table')
             ,'height'              => $phpExcel->getImportFldObj('Height')
             ,'depth'               => $phpExcel->getImportFldObj('Depth')
             ,'measurement'         => $phpExcel->getImportFldObj('Measurement')

             ,'price'               => $phpExcel->getImportFldObj('Price')
             ,'rap_price'           => $phpExcel->getImportFldObj('Rap Price')
             ,'less_price'          => $phpExcel->getImportFldObj('Less Price')
             ,'discount'            => $phpExcel->getImportFldObj('Discount')
             ,'total'               => $phpExcel->getImportFldObj('Total')
             ,'qty_in_stock'        => $phpExcel->getImportFldObj('Qty In Stock')
             ,'supplier'            => $phpExcel->getImportFldObj('Supplier')
             ,'supplier_code'       => $phpExcel->getImportFldObj('Supplier Code')
        );

        $fa['published']['defaultValue']= 1;
        $fa['record_type']['defaultValue'] = 'Diamond';
        
        /******** SPECIAL MANIPULATIONS ********/
        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Diamond');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        $fa['status']['specialType'] = 'valuelist';
        $fa['status']['exp'] = array(
             'keyText' => 'diamondStatus'
        );

        $fa['shape']['specialType'] = 'valuelist';
        $fa['shape']['exp'] = array(
             'keyText' => 'diamondShape'
        );

        $fa['lab']['specialType'] = 'valuelist';
        $fa['lab']['exp'] = array(
             'keyText' => 'diamondLab'
        );

        $fa['color']['specialType'] = 'valuelist';
        $fa['color']['exp'] = array(
             'keyText' => 'diamondColor'
        );

        $fa['carat']['specialType'] = 'valuelist';
        $fa['carat']['exp'] = array(
             'keyText' => 'diamondCarat'
        );

        $fa['clarity']['specialType'] = 'valuelist';
        $fa['clarity']['exp'] = array(
             'keyText' => 'diamondClarity'
        );

        $fa['polish']['specialType'] = 'valuelist';
        $fa['polish']['exp'] = array(
             'keyText' => 'diamondPolish'
        );

        $fa['symmetry']['specialType'] = 'valuelist';
        $fa['symmetry']['exp'] = array(
             'keyText' => 'diamondSymmetry'
        );

        $fa['girdle']['specialType'] = 'valuelist';
        $fa['girdle']['exp'] = array(
             'keyText' => 'diamondGirdle'
        );

        $fa['fluorescence']['specialType'] = 'valuelist';
        $fa['fluorescence']['exp'] = array(
             'keyText' => 'diamondFluorescence'
        );

        $fa['culet']['specialType'] = 'valuelist';
        $fa['culet']['exp'] = array(
             'keyText' => 'diamondCulet'
        );

        $fa['cut']['specialType'] = 'valuelist';
        $fa['cut']['exp'] = array(
             'keyText' => 'diamondCut'
        );

        /****************************************/
        $config = array(
             'module'        => 'gdj_diamond'
            ,'matchFieldArr' => array('item_code')
            ,'fldsArr'       => $fa
        );

        return $phpExcel->importData($config);
    }
}
