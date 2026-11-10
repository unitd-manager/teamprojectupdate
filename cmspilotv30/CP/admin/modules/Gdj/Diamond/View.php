<?
class CP_Admin_Modules_Gdj_Diamond_View extends CP_Common_Modules_Gdj_Diamond_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['item_code'])}
            {$listObj->getListDataCell($row['shape'])}
            {$listObj->getListDataCell($row['color'])}
            {$listObj->getListDataCell($row['clarity'])}
            {$listObj->getListDataCell($row['lab'])}
            {$listObj->getListDataCell($row['fluorescence'])}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListPublishedImage($row['published'], $row['product_id'])}
            {$listObj->getListDataCell($row['product_id'], 'center')}
            {$listObj->getListRowEnd($row['diamond_id'])}

            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$listObj->getListHeaderCell('Item Code', 'p.item_code')}
        {$listObj->getListHeaderCell('Shape', 'p.shape')}
        {$listObj->getListHeaderCell('Color', 'p.color')}
        {$listObj->getListHeaderCell('Clarity', 'p.clarity')}
        {$listObj->getListHeaderCell('Lab', 'p.lab')}
        {$listObj->getListHeaderCell('Fluorescence', 'p.fluorescence')}
        {$listObj->getListHeaderCell('Price', 'p.price')}
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

        $sqlStatus       = $fn->getValueListSQL('diamondStatus');
        $sqlShape        = $fn->getValueListSQL('diamondShape');
        $sqlLab          = $fn->getValueListSQL('diamondLab');
        $sqlColor        = $fn->getValueListSQL('diamondColor');
        $sqlClarity      = $fn->getValueListSQL('diamondClarity');
        $sqlPolish       = $fn->getValueListSQL('diamondPolish');
        $sqlSymmetry     = $fn->getValueListSQL('diamondSymmetry');
        $sqlGirdle       = $fn->getValueListSQL('diamondGirdle');
        $sqlFluorescence = $fn->getValueListSQL('diamondFluorescence');
        $sqlCulet        = $fn->getValueListSQL('diamondCulet');
        $sqlCut          = $fn->getValueListSQL('diamondCut');

        $expVl = array('sqlType' => 'OneField');

        $metaData = '';
        if ($cpCfg['m.gdj.diamond.showMetaDataForDiamond'] == 1) {
            $metaData .= $formObj->getMetaData($row);
        }

        $descriptionShort = '';
        if ($cpCfg['m.gdj.diamond.showShortDescInDiamond'] == 1){
            $descriptionShort = $formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short'));
        }

        $expNoEdit = array('isEditable' => 0);

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getTBRow('Item Code', 'item_code', $row['item_code'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Shape', 'shape', $sqlShape, $row['shape'], $expVl)}
        {$formObj->getDDRowBySQL('Lab', 'lab', $sqlLab, $row['lab'], $expVl)}
        {$formObj->getDDRowBySQL('Color', 'color', $sqlColor, $row['color'], $expVl)}
        {$formObj->getTBRow('Carat', 'carat', $row['carat'])}
        {$formObj->getDDRowBySQL('Clarity', 'clarity', $sqlClarity, $row['clarity'], $expVl)}
        {$formObj->getDDRowBySQL('Polish', 'polish', $sqlPolish, $row['polish'], $expVl)}
        {$formObj->getDDRowBySQL('Symmetry', 'symmetry', $sqlSymmetry, $row['symmetry'], $expVl)}
        {$formObj->getDDRowBySQL('Girdle', 'girdle', $sqlGirdle, $row['girdle'], $expVl)}
        {$formObj->getDDRowBySQL('Fluorescence', 'fluorescence', $sqlFluorescence, $row['fluorescence'], $expVl)}
        {$formObj->getDDRowBySQL('Culet', 'culet', $sqlCulet, $row['culet'], $expVl)}
        {$formObj->getDDRowBySQL('Cut', 'cut', $sqlCut, $row['cut'], $expVl)}
        ";

        $fieldset3 = "
        {$formObj->getTBRow('Table', 'table', $row['table'])}
        {$formObj->getTBRow('Height', 'height', $row['height'])}
        {$formObj->getTBRow('Depth', 'depth', $row['depth'])}
        {$formObj->getTBRow('Measurement', 'measurement',  $row['measurement'])}
        ";

        $fieldset4 = "
        {$formObj->getTBRow('Price', 'price', $row['price'])}
        {$formObj->getTBRow('Rap Price', 'rap_price',  $row['rap_price'])}
        {$formObj->getTBRow('Less Price', 'less_price', $row['less_price'])}
        {$formObj->getTBRow('Discount', 'discount',  $row['discount'])}
        {$formObj->getTBRow('Total', 'total',  $row['total'], $expNoEdit)}
        {$formObj->getTBRow('Qty In Stock', 'qty_in_stock', $row['qty_in_stock'])}
        {$formObj->getTBRow('Supplier', 'supplier', $row['supplier'])}
        {$formObj->getTBRow('Supplier Code', 'supplier_code', $row['supplier_code'])}
        ";

        $fieldset5 = "
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getYesNoRRow('Show Price', 'show_price', $row['show_price'])}
        {$formObj->getYesNoRRow('Member Only', 'member_only', $row['member_only'])}
        {$formObj->getYesNoRRow('Latest', 'latest', $row['latest'])}
        ";

        $fieldset6 = "
        {$descriptionShort}
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Specification', $fieldset2)}
        {$formObj->getFieldSetWrapped('Measurement', $fieldset3)}
        {$formObj->getFieldSetWrapped('Price & Availability', $fieldset4)}
        {$formObj->getFieldSetWrapped('Access', $fieldset5)}
        {$formObj->getFieldSetWrapped('Description', $fieldset6)}
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

        $certificate = '';
        $links = '';

        if ($cpCfg['m.gdj.diamond.hasCertificate'] == 1){
            $certificate .= $media->getRightPanelMediaDisplay('Certificate', 'gdj_diamond', 'certificate', $row);
        }

        if ($cpCfg['m.gdj.diamond.hasProductRelatedProduct'] == 1){
            $links .= $displayLinkData->getLinkPortalMain('gdj_diamond', 'gdj_diamondLink', 'Related Diamonds', $row);
        }

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'gdj_diamond', 'picture', $row)}
        {$certificate}
        {$media->getRightPanelMediaDisplay('Related Picture', 'gdj_diamond', 'relatedPicture', $row)}
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
        $color          = $fn->getReqParam('color');
        $clarity        = $fn->getReqParam('clarity');
        $lab            = $fn->getReqParam('lab');
        $fluorescence   = $fn->getReqParam('fluorescence');
        $shape          = $fn->getReqParam('shape');
        $special_search = $fn->getReqParam('special_search');

        $sqlColor       = $fn->getValueListSQL('diamondColor', 'value ASC');
        $sqlClarity     = $fn->getValueListSQL('diamondClarity', 'value ASC');
        $sqlLab         = $fn->getValueListSQL('diamondLab', 'value ASC');
        $sqlFluorescence = $fn->getValueListSQL('diamondFluorescence', 'value ASC');
        $sqlShape       = $fn->getValueListSQL('diamondShape', 'value ASC');

        $text = "
        <td>
            <select name='color'>
                <option value=''>Color</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlColor, $color)}
            </select>
        </td>

        <td>
            <select name='clarity'>
                <option value=''>Clarity</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlClarity, $clarity)}
            </select>
        </td>

        <td>
            <select name='lab'>
                <option value=''>Lab</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlLab, $lab)}
            </select>
        </td>

        <td>
            <select name='fluorescence'>
                <option value=''>Fluorescence</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlFluorescence, $fluorescence)}
            </select>
        </td>

        <td>
            <select name='shape'>
                <option value=''>Shape</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlShape, $shape)}
            </select>
        </td>

        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.gdj.diamond.btnPosArr'], $special_search)}
            </select>
        </td>
        ";

        return $text;
    }
}