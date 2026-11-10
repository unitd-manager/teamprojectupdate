<?
class CP_Admin_Modules_Gdj_Gemstone_View extends CP_Common_Modules_Gdj_Gemstone_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['title'])}
            {$listObj->getListDataCell($row['item_code'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['cost_a'])}
            {$listObj->getListDataCell($row['pieces_qty'])}
            {$listObj->getListDataCell($row['carat'])}
            {$listObj->getListDataCell($row['shape'])}
            {$listObj->getListDataCell($row['supplier'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['item_location'])}
            {$listObj->getListThumbMediaImage('gdj_gemstone', 'picture', $row['product_id'])}
            {$listObj->getListPublishedImage($row['published'], $row['product_id'])}
            {$listObj->getListDataCell($row['product_id'], 'center')}
            {$listObj->getListRowEnd($row['product_id'])}
            ";
            $count ++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$listObj->getListHeaderCell('Item Code', 'p.item_code')}
        {$listObj->getListHeaderCell('Category', 'ca.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('Price', 'p.price')}
        {$listObj->getListHeaderCell('Qty', 'p.pieces_qty')}
        {$listObj->getListHeaderCell('Carat', 'p.carat')}
        {$listObj->getListHeaderCell('Shape', 'p.shape')}
        {$listObj->getListHeaderCell('Supplier', 'p.supplier')}
        {$listObj->getListHeaderCell('Status', 'p.status')}
        {$listObj->getListHeaderCell('Item Location', 'p.item_location')}
        {$listObj->getListHeaderCell('Picture')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'p.product_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlStatus       = $fn->getValueListSQL('gemstoneStatus');
        $sqlShape        = $fn->getValueListSQL('gemstoneShape');
        $sqlColor        = $fn->getValueListSQL('gemstoneColor');
        $sqlCut          = $fn->getValueListSQL('gemstoneCut');
        $sqlOrigin       = $fn->getValueListSQL('gemstoneOrigin');
        $sqlType         = $fn->getValueListSQL('gemstoneType');
        $sqlHardness     = $fn->getValueListSQL('gemstoneHardness');
        $sqlLuster       = $fn->getValueListSQL('gemstoneLuster');
        $sqlTreatment    = $fn->getValueListSQL('gemstoneTreatment');
        $sqlLab          = $fn->getValueListSQL('gemstoneLab');
        $sqlItemLocation = $fn->getValueListSQL('gemstoneItemLocation');

        $expVl = array('sqlType' => 'OneField');

        $metaData = '';
        if ($cpCfg['m.gdj.gemstone.showMetaDataForGemstone'] == 1) {
            $metaData .= $formObj->getMetaData($row);
        }

        $descriptionShort = '';
        if ($cpCfg['m.gdj.gemstone.showShortDescInGemstone'] == 1){
            $descriptionShort = $formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short'));
        }

        $sqlCategory = "
        SELECT DISTINCT c.category_id
        	  ,c.title
        	  ,s.title
        FROM category c
            ,section s
        WHERE c.section_id = s.section_id
          AND s.section_type = 'Gemstone'
        ORDER BY s.title
        ";
        $expCategory = array('detailValue' => $row['category_title']);

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $expAskPrice = array('isEditable' => 0);

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
        {$formObj->getTBRow('Item Code', 'item_code', $row['item_code'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Shape', 'shape', $sqlShape, $row['shape'], $expVl)}
        {$formObj->getDDRowBySQL('Color', 'color', $sqlColor, $row['color'], $expVl)}
        {$formObj->getTBRow('Carat', 'carat', $row['carat'])}
        {$formObj->getTBRow('Measurement', 'measurement', $row['measurement'])}
        {$formObj->getDDRowBySQL('Cut', 'cut', $sqlCut, $row['cut'], $expVl)}
        {$formObj->getDDRowBySQL('Origin', 'origin', $sqlOrigin, $row['origin'], $expVl)}
        {$formObj->getDDRowBySQL('Type', 'type', $sqlType, $row['type'], $expVl)}
        {$formObj->getDDRowBySQL('Hardness', 'hardness', $sqlHardness, $row['hardness'], $expVl)}
        {$formObj->getDDRowBySQL('Luster', 'luster', $sqlLuster, $row['luster'], $expVl)}
        {$formObj->getDDRowBySQL('Treatment', 'treatment', $sqlTreatment, $row['treatment'], $expVl)}
        {$formObj->getDDRowBySQL('Lab', 'lab', $sqlLab, $row['lab'], $expVl)}
        ";

        $fieldset3 = "
        {$formObj->getTBRow('Price', 'price', $row['price'])}
        {$formObj->getTBRow('Cost A', 'cost_a', $row['cost_a'])}
        {$formObj->getTBRow('Cost B', 'cost_b', $row['cost_b'])}
        {$formObj->getTBRow('Margin B', 'margin_b', $row['margin_b'])}
        {$formObj->getTBRow('Asking Price', 'asking_price', $row['asking_price'], $expAskPrice)}
        {$formObj->getTBRow('Pieces / Qty', 'pieces_qty', $row['pieces_qty'])}
        {$formObj->getTBRow('Qty in stock', 'qty_in_stock', $row['qty_in_stock'])}
        {$formObj->getDDRowBySQL('Item Location', 'item_location', $sqlItemLocation, $row['item_location'], $expVl)}
        {$formObj->getTBRow('Supplier', 'supplier', $row['supplier'])}
        {$formObj->getTBRow('Supplier Code', 'supplier_code', $row['supplier_code'])}
        ";

        $fieldset4 = "
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getYesNoRRow('Show Price', 'show_price', $row['show_price'])}
        {$formObj->getYesNoRRow('Member Only', 'member_only', $row['member_only'])}
        {$formObj->getYesNoRRow('Latest', 'latest', $row['latest'])}
        ";

        $fieldset5 = "
        {$descriptionShort}
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Specification', $fieldset2)}
        {$formObj->getFieldSetWrapped('Price & Availability', $fieldset3)}
        {$formObj->getFieldSetWrapped('Access', $fieldset4)}
        {$formObj->getFieldSetWrapped('Description', $fieldset5)}
        {$metaData}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $links = '';
        $linkCertificate = '';

        if ($cpCfg['m.gdj.gemstone.hasCertificate'] == 1){
            $linkCertificate .= $media->getRightPanelMediaDisplay('Certificate', 'gdj_gemstone', 'certificate', $row);
        }

        if ($cpCfg['m.gdj.gemstone.hasProductRelatedProduct'] == 1){
            $links .= $displayLinkData->getLinkPortalMain('gdj_gemstone', 'gdj_gemstoneLink', 'Related Gemstones', $row);
        }

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'gdj_gemstone', 'picture', $row)}
        {$linkCertificate}
        {$media->getRightPanelMediaDisplay('Related Picture', 'gdj_gemstone', 'relatedPicture', $row)}
        {$links}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $company_id     = $fn->getReqParam('company_id');
        $shape          = $fn->getReqParam('shape');
        $supplier       = $fn->getReqParam('supplier');
        $status         = $fn->getReqParam('status');
        $special_search = $fn->getReqParam('special_search');

        $subCatOptions  = '';

        $SQLCat = "
        SELECT a.category_id
              ,a.title
        FROM category a
        LEFT JOIN (section b) ON (a.section_id  = b.section_id)
        WHERE b.section_type ='Gemstone'
        ORDER BY a.title ASC
        ";
        $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);

        if ($tv['category_id'] != "") {
            $sqlCombo = "
            SELECT a.sub_category_id
                  ,a.title
            FROM sub_category a
            WHERE a.category_id = {$tv['category_id']}
            ORDER BY a.title ASC
            ";
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $tv['sub_category_id']);
        }

        $SQLSupplier = "
        SELECT DISTINCT
               p.supplier
        FROM product p
        WHERE p.supplier != ''
        ORDER BY p.supplier ASC
        ";

        $SQLStatus = "
        SELECT DISTINCT
               p.status
        FROM product p
        WHERE p.status != ''
        ORDER BY p.status ASC
        ";

        $sqlShape = $fn->getValueListSQL('gemstoneShape', 'value ASC');

        $text = "
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$catOptions}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='sub_category_id'>
                <option value=''>Sub Category</option>
                {$subCatOptions}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='shape'>
                <option value=''>Shape</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlShape, $shape)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='supplier'>
                <option value=''>Supplier</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLSupplier, $supplier)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.gdj.gemstone.btnPosArr'], $special_search)}
            </select>
        </td>
        ";


        return $text;
    }
}