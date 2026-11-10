<?
class CP_Admin_Modules_Hms_LabsSupplier_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT ls.*
              ,gc.name AS country_name
        FROM labs_supplier ls
        LEFT JOIN (geo_country gc) ON (ls.address_country = gc.country_code)
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
        $searchVar->mainTableAlias = 'ls';

        $status       = $fn->getReqParam('status');
        $labs_supplier_id   = $fn->getReqParam('labs_supplier_id');
       // $company_name = $fn->getReqParam('company_name');

        if ($labs_supplier_id != "") {
            $searchVar->sqlSearchVar[] = "ls.labs_supplier_id = '{$labs_supplier_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "ls.labs_supplier_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'ls.labs_supplier_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "ls.status = '{$status}'";
            }

           /* if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }*/

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    ls.category  LIKE '%{$tv['keyword']}%'
                    
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "ls.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(ls.flag != 1 OR ls.flag IS null)";
            }

            //$searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the Name');
        $validate->validateData('category', 'Please select the category');

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
        //$fa['category'] = 'Client';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the name');
        $validate->validateData('category', 'Please select the category');
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
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_flat');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_street');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_town');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_state');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_country');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'company_id'      => $phpExcel->getFldObj('Company ID')
             ,'company_name'    => $phpExcel->getFldObj('Company Name')
             ,'category'        => $phpExcel->getFldObj('Category')
             ,'company_size'    => $phpExcel->getFldObj('Company Size')
             ,'industry'        => $phpExcel->getFldObj('Industry')
             ,'source'          => $phpExcel->getFldObj('Source')
             ,'website'         => $phpExcel->getFldObj('Website')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')

             ,'address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'address_street'  => $phpExcel->getFldObj('Address Street')
             ,'address_town'    => $phpExcel->getFldObj('Address Town')
             ,'address_state'   => $phpExcel->getFldObj('Address State')
             ,'address_country' => $phpExcel->getFldObj('Address Country')

             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comment_by'      => $phpExcel->getFldObj('Comment By')
             ,'notes'           => $phpExcel->getFldObj('Notes')
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
    function getImportData(){
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
             'module'              => 'hms_company'
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
    /**
     *
     */
    function getHmsLabsSupplierHmsContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM labs_supplier b, contact a
        WHERE a.labs_supplier_id = b.labs_supplier_id
          AND b.labs_supplier_id = {$id}
        ";
    }
    /**
     *
     */
    function getHmsCompanyHmsDiscountLinkSQL($id) {

        return "
        SELECT d.discount_id
              ,pg.title
              ,c.title AS category_title
              ,d.margin
              ,d.discount_percent
        FROM discount d
        LEFT JOIN (product_group pg) ON (d.product_group_id = pg.product_group_id)
        LEFT JOIN (category c) ON (d.category_id = c.category_id)
        WHERE d.company_id = {$id}
        ORDER BY pg.sort_order
        ";
    }

    /**
     *
     */
    function getHmsCompanyHmsCompanyGroupLinkSQL1($id) {

        return "
        SELECT a.company_id
              ,a.company_name
              ,a.status
        FROM company_group b, company a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }

    /**
     *
     */
    function getHmsLabsSupplierHmsLabsSupplierLinkSQL($id) {

        return "
        SELECT a.labs_supplier_id
              ,c.labs_suppliercategory_id
              ,c.title
        FROM `labs_supplier` a
        LEFT JOIN (labs_suppliercategorylink b) ON (b.labs_supplier_id = a.labs_supplier_id)
        LEFT JOIN (labs_suppliercategory c) ON (c.labs_suppliercategory_id = b.labs_suppliercategory_id)
        WHERE b.labs_supplier_id = {$id}
         ";
    }

    /**
     *
     */
    function getAddRemoveSupplierCategoryLink() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $labs_suppliercategory_id = $fn->getReqParam('labs_suppliercategory_id');
        $labs_supplier_id         = $fn->getReqParam('labs_supplier_id');
        $removeCategory           = $fn->getReqParam('removeCategory');

        if($removeCategory == 1){
            $SQLDeleteCategory = "
            DELETE FROM labs_suppliercategorylink
            WHERE labs_supplier_id = {$labs_supplier_id}
            AND labs_suppliercategory_id = {$labs_suppliercategory_id}
            "; 
            $resultDeleteCategory = $db->sql_query($SQLDeleteCategory);
        }
        else{
            $fa = array();

            $fa['labs_suppliercategory_id'] = $labs_suppliercategory_id;
            $fa['labs_supplier_id']         = $labs_supplier_id;
            $fa['creation_date']            = date("Y-m-d H:i:s");

            $insertSupplier = $dbUtil->getInsertSQLStringFromArray($fa, 'labs_suppliercategorylink');
            $resultSupplier = $db->sql_query($insertSupplier);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddRemoveCategoryAll() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $labs_supplier_id         = $fn->getReqParam('labs_supplier_id');
        $removeCategory           = $fn->getReqParam('removeCategory');

        if($removeCategory == 1){
            $SQLDeleteCategory = "
            DELETE FROM labs_suppliercategorylink
            WHERE labs_supplier_id = {$labs_supplier_id}
            "; 
            $resultDeleteCategory = $db->sql_query($SQLDeleteCategory);
        }
        else{
            $sqlCategory = "
            SELECT labs_suppliercategory_id
                  ,title
            FROM `labs_suppliercategory`
            ";
            $resultCategory = $db->sql_query($sqlCategory);
            
            While($rowCategory  = $db->sql_fetchrow($resultCategory)){
                $SQLCheck = "
                SELECT labs_suppliercategory_id
                FROM labs_suppliercategorylink
                WHERE labs_supplier_id = {$labs_supplier_id}
                AND labs_suppliercategory_id = {$rowCategory['labs_suppliercategory_id']}
                ";
                $resultCheck  = $db->sql_query($SQLCheck);
                $numRowsCheck = $db->sql_numrows($resultCheck);    

                if($numRowsCheck > 0){  
                }else{
                    $fa = array();

                    $fa['labs_suppliercategory_id'] = $rowCategory['labs_suppliercategory_id'];
                    $fa['labs_supplier_id']         = $labs_supplier_id;
                    $fa['creation_date']            = date("Y-m-d H:i:s");

                    $insertSupplier = $dbUtil->getInsertSQLStringFromArray($fa, 'labs_suppliercategorylink');
                    $resultSupplier = $db->sql_query($insertSupplier);
                }
            }
        }

        return $validate->getSuccessMessageXML();
    }
}
