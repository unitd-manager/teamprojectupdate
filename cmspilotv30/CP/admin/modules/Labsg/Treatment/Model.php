<?
class CP_Admin_Modules_Labsg_Treatment_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT t.*
        FROM treatment t
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
        $searchVar->mainTableAlias = 't';

        $status       = $fn->getReqParam('status');
        $treatment_id   = $fn->getReqParam('treatment_id');
       // $company_name = $fn->getReqParam('company_name');

        if ($treatment_id != "") {
            $searchVar->sqlSearchVar[] = "t.treatment_id = '{$treatment_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.treatment_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 't.treatment_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

           /* if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }*/

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "t.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(t.flag != 1 OR t.flag IS null)";
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
        $validate->validateData('title', 'Please enter the Title');

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
        $fa = $fn->addToFieldsArray($fa, 'treatment_code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'fees');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'instruction');
        $fa = $fn->addToFieldsArray($fa, 'no_of_days');
        $fa = $fn->addToFieldsArray($fa, 'qty');
        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addToFieldsArray($fa, 'treatment_medicine_template_id');

        return $fa;
    }

    /**
     *
     *
     */
   /* function getSearchMedicineDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $MedicineDetail = $extractor[0];

        $SQL = "
        SELECT p.product_id AS id
              ,p.title AS Medicine_Name
        FROM product p
        WHERE (p.product_id LIKE '%{$MedicineDetail}%'
        OR p.title LIKE '%{$patientDetail}%'
        ORDER BY p.title
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }*/

    /**
     *
     */
    function getMedicineTemplateFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getMedicineTemplateValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id   = $fn->getPostParam('product_id');
        $treatment_id = $fn->getPostParam('treatment_id');
        $no_of_days   = $fn->getPostParam('no_of_days');
        $qty          = $fn->getPostParam('qty');
        $instruction  = $fn->getPostParam('instruction');
        $title        = $fn->getPostParam('title');
        //$Medicine_Name= $fn->getPostParam('Medicine_Name');

        $fa = array();

        $fa['product_id']     = $product_id;
        $fa['no_of_days']     = $no_of_days;
        $fa['qty']            = $qty;
        $fa['instruction']    = $instruction;
        $fa['title']          = $title;
        $fa['treatment_id']   = $treatment_id;
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');

        $insertTreatmentSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'treatment_medicine_template');
        $resultTreatmentSQL = $db->sql_query($insertTreatmentSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditMedicineTemplateFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getMedicineTemplateValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id                     = $fn->getPostParam('product_id');
        //$treatment_id                 = $fn->getPostParam('treatment_id');
        $no_of_days                     = $fn->getPostParam('no_of_days');
        $qty                            = $fn->getPostParam('qty');
        $instruction                    = $fn->getPostParam('instruction');
        $title                          = $fn->getPostParam('title');
        $treatment_medicine_template_id = $fn->getPostParam('treatment_medicine_template_id');

        $fa1 = array();

        $fa1['product_id']                       = $product_id;
        $fa1['no_of_days']                      = $no_of_days;
        $fa1['qty']                             = $qty;
        $fa1['instruction']                     = $instruction;
        $fa1['title']                           = $title;
       // $fa1['treatment_id']                  = $treatment_id;
        $fa1['treatment_medicine_template_id']  = $treatment_medicine_template_id;
        $fa1['modification_date']               = date("Y-m-d H:i:s");
        $fa1['modified_by']                     = $fn->getSessionParam('userName');

        $whereConditionTreatment = "WHERE treatment_medicine_template_id = {$treatment_medicine_template_id}" ;
        $sqlUpdateTreatment      = $dbUtil->getUpdateSQLStringFromArray($fa1, "treatment_medicine_template", $whereConditionTreatment);
        $resultUpdateTreatment   = $db->sql_query($sqlUpdateTreatment);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getMedicineTemplateValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('product_group_id', 'Please select the product group');
        $validate->validateData('product_id', 'Please enter Medicine Name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getDeleteMedicineTemplate(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $treatment_id = $fn->getReqParam('treatment_id');
        $treatment_medicine_template_id = $fn->getReqParam('treatment_medicine_template_id');

        $SQL ="
               DELETE FROM treatment_medicine_template
               WHERE treatment_medicine_template_id = {$treatment_medicine_template_id}
               ";
        $result = $db->sql_query($SQL);
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
             'module'              => 'labsg_company'
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
    function getlabsgCompanylabsgContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }
    /**
     *
     */
    function getlabsgCompanylabsgDiscountLinkSQL($id) {

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
    function getlabsgCompanylabsgCompanyGroupLinkSQL1($id) {

        return "
        SELECT a.company_id
              ,a.company_name
              ,a.status
        FROM company_group b, company a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }

  }
