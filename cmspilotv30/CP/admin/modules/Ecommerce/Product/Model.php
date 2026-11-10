<?
class CP_Admin_Modules_Ecommerce_Product_Model extends CP_Common_Modules_Ecommerce_Product_Model
{

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
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'section_id');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'product_group_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'price');

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description2', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);
        $fa = $fn->addToFieldsArray($fa, 'weight_grams');
        $fa = $fn->addToFieldsArray($fa, 'embed_code');

        if(isset($_POST['published'])){
            $fa = $fn->addToFieldsArray($fa, 'published');
        }

        $fa = $fn->addToFieldsArray($fa, 'qty_in_stock');
        $fa = $fn->addToFieldsArray($fa, 'product_code');
        $fa = $fn->addToFieldsArray($fa, 'member_only');
        $fa = $fn->addToFieldsArray($fa, 'latest');
        $fa = $fn->addToFieldsArray($fa, 'favourite');
        $fa = $fn->addToFieldsArray($fa, 'slideshow');

        if ($cpCfg['m.ecommerce.product.showMetaData'] == 1) {
            $cpCfg = Zend_Registry::get('cpCfg');
            $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);
        }

        return $fa;
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
             'module'              => 'ecommerce_product'
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
            $sourceFilePath = realpath('../media_import') . "/{$fa['picture']}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $fa['picture']
            );
            $media->model->createMedia('ecommerce_product', 'picture', $product_id, $exp);
        }
    }

    /**
     *
     */
    function getGenerateBulkVouchers() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');

        $product_id= $fn->getReqParam('id');

        $formAction = "index.php?module=ecommerce_product&_spAction=generateVoucherFormSubmit&showHTML=0";

        $text = "
        <form name='portalForm' id='portalForm' method='post' action='{$formAction}'>
            <fieldset>
                <div class='floatbox'>
                    <div class='float_left'>
                     {$formObj->getTBRow('Number of Records', 'no_of_records')}
                    </div>
                </div>
                <input type='hidden' name='product_id' value='{$product_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getGenerateVoucherFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');

        if (!$this->getGenerateVoucherValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id    = $fn->getReqParam('product_id');
        $no_of_records = $fn->getReqParam('no_of_records');

        for ($i = 1; $i <= $no_of_records ; $i++){
            $fa = array();
            $rand_no = mt_rand();
            $fa['voucher_no']    = $rand_no;
            $fa['product_id'] = $product_id;
            $id = $fn->addRecord($fa, 'product_voucher');
        }

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getGenerateVoucherValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        //$validate->validateData("email"       , $ln->gd("cp.form.fld.email.err")      , "email");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintVoucher() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new FPDF();
        $pdf->SetFont('Arial','B',14);

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $product_id  = $fn->getReqParam('id');
		$invoice_terms = '';
		$notes  = '';
        $total = '';

		$SQL = "
		SELECT pv.voucher_no
            ,pv.product_id
            ,p.title as product_title
		FROM product_voucher pv
		JOIN product p ON (pv.product_id = p.product_id)
		WHERE pv.product_id = {$product_id}
		ORDER BY pv.product_voucher_id
		";

        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
		if ($numRows == 0){
            $pdf->SetXY(60,30);
            $pdf->Cell(50, 20, "Please set the values for your Voucher and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $rows = "";

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                $pdf->Image('images/sgdealon_banner.jpg',0,0,210, 30);
                $pdf->SetY(32);
                $product_title = "Please find the Voucher Codes for the Product : " ;
                //$pdf->WordWrap($product_title, 200);
                $pdf->Write(5, $product_title);
                $pdf->Ln(8);
                $pdf->drawTextBox($row['product_title'], 195, 32, 'L', 'T', 0);
                $pdf->Ln(10);
            }
             //Table Content
            $voucher_no = $row['voucher_no'];
            $count++;
            //$pdf->Write(5, "Voucher No " . $count . ': ' . $voucher_no);
            $pdf->Cell(60, 5, "Voucher No " . $count . ': ' . $voucher_no, 1);
            if ($count % 3){
            }
            else{
                $pdf->Ln(10);
            }
        }
        //Final Values
        $pdf->Output();
    }


    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'p';

        $product_id   = $fn->getReqParam('product_id');
        $company_id   = $fn->getReqParam('company_id');
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $industry_id  = $fn->getReqParam('industry_id');
        $substrate_id  = $fn->getReqParam('substrate_id');
        $special_search  = $fn->getReqParam('special_search');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "p.published = 1";
        }

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.product_id');

            if($tv['linkName'] == 'product#product'){
                $searchVar->sqlSearchVar[] = "p.product_id != {$tv['linkMasterTableID']}";
            }

            if ($tv['section_id'] != '') {
                $searchVar->sqlSearchVar[] = "p.section_id  = {$tv['section_id']}";
            }

            if ($tv['category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.category_id = '{$tv['category_id']}'";
            }

            if ($industry_id != '' ) {
                $searchVar->sqlSearchVar[] = "p.industry_id = '{$industry_id}'";
            }

            if ($substrate_id != '' ) {
                $searchVar->sqlSearchVar[] = "p.substrate_id = '{$substrate_id}'";
            }

            if ($tv['subRoom'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.category_id = '{$tv['subRoom']}'";
            }

            if ($tv['sub_category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.sub_category_id = '{$tv['sub_category_id']}'";
            }

            if ($company_id != '' ) {
                $searchVar->sqlSearchVar[] = "p.company_id = '{$tv['company_id']}'";
            }

            if ($tv['subCat'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.sub_category_id = '{$tv['subCat']}'";
            }

            if ($special_search != '' ) {
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "p.published = 1";
                }

                if ($special_search == 'Not-Published') {
                    $searchVar->sqlSearchVar[] = "p.published = 0 OR p.published IS NULL OR p.published = ''";
                }

                if ($special_search == 'Latest' ) {
                    $searchVar->sqlSearchVar[] = "p.latest = 1";
                }

                if ($special_search == 'Favourite' ) {
                    $searchVar->sqlSearchVar[] = "p.favourite = 1";
                }

                if ($special_search == 'Flag' ) {
                    $searchVar->sqlSearchVar[] = "p.flag = 1";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                p.title LIKE '%{$tv['keyword']}%'
                OR p.description  LIKE '%{$tv['keyword']}%'
                )";
            }

            if($cpCfg['m.ecommerce.product.hasSortOrderFld']){
                $searchVar->sortOrder = "p.sort_order ASC";
            }
        }

    }
}
