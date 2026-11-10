<?
class CP_Admin_Modules_Gdj_Jewellery_View extends CP_Common_Modules_Gdj_Jewellery_View
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
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListDataCell($row['weight'])}
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
        {$listObj->getListHeaderCell('Category', 'p.category_id')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('Price', 'p.price')}
        {$listObj->getListHeaderCell('Weight', 'p.weight')}
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
        
        $metaData = '';
        if ($cpCfg['m.gdj.jewellery.showMetaDataForJewellery'] == 1) {
            $metaData .= $formObj->getMetaData($row);
        }

        $descriptionShort = '';
        if ($cpCfg['m.gdj.jewellery.showShortDescInJewellery'] == 1){
            $descriptionShort = $formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short'));                
        }

        $sqlCategory = "
        SELECT DISTINCT c.category_id
        	  ,c.title
        	  ,s.title
        FROM category c
            ,section s
        WHERE c.section_id = s.section_id
          AND s.section_type = 'Jewellery'
        ORDER BY s.title
        ";
        $expCategory = array('detailValue' => $row['category_title']);
        
        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }        
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $sqlstatus      = $fn->getValueListSQL('jewelleryStatus');     
        $sqlMaterial    = $fn->getValueListSQL('jewelleryMaterial');
        $sqlMetal       = $fn->getValueListSQL('jewelleryMetal');
        $sqlColor       = $fn->getValueListSQL('jewelleryColor');

        $expVl = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
        {$formObj->getTBRow('Item Code', 'item_code', $row['item_code'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlstatus, $row['status'], $expVl)}
        {$formObj->getDDRowBySQL('Material', 'material', $sqlMaterial, $row['material'], $expVl)}
        {$formObj->getDDRowBySQL('Metal', 'metal', $sqlMetal, $row['metal'], $expVl)}
        {$formObj->getDDRowBySQL('Color', 'color', $sqlColor, $row['color'], $expVl)}
        {$formObj->getTBRow('Stone', 'stone', $row['stone'])}
        ";
        
        $fieldset2 = "
        {$formObj->getTBRow('Price', 'price', $row['price'])}
        {$formObj->getTBRow('Qty In Stock', 'qty_in_stock', $row['qty_in_stock'])}
        ";
        
        $fieldset3 = "
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getYesNoRRow('Show Price', 'show_price', $row['show_price'])}
        {$formObj->getYesNoRRow('Member Only', 'member_only', $row['member_only'])}
        {$formObj->getYesNoRRow('Latest', 'latest', $row['latest'])}
        ";
        
        $fieldset4 = "
        {$descriptionShort}
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Price & Availability', $fieldset2)}
        {$formObj->getFieldSetWrapped('Access', $fieldset3)}
        {$formObj->getFieldSetWrapped('Description', $fieldset4)}
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
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');

        $certificate = '';
        $links = '';
        
        if ($cpCfg['m.gdj.jewellery.hasCertificate'] == 1){
            $certificate .= $media->getRightPanelMediaDisplay('Certificate', 'gdj_jewellery', 'certificate', $row);
        }

        if ($cpCfg['m.gdj.jewellery.hasProductRelatedProduct'] == 1){
            $links .= $displayLinkData->getLinkPortalMain('gdj_jewellery', 'gdj_jewelleryLink', 'Related Jewellery', $row);
        }

        $record_id = $fn->getIssetParam($row, 'product_id');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'gdj_jewellery', 'picture', $row)}
        {$certificate}
        {$media->getRightPanelMediaDisplay('Related Picture', 'gdj_jewellery', 'relatedPicture', $row)}
        {$links}
        {$comment->getView(array(
             'roomName' => 'gdj_jewellery'
            ,'recordId' => $record_id
        ))}
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

        $special_search = $fn->getReqParam('special_search');

        $subCatOptions  = '';
        
        $SQLCat = "
        SELECT a.category_id
              ,a.title 
        FROM category a
        LEFT JOIN (section b) ON (a.section_id  = b.section_id)
        WHERE b.section_type ='Jewellery'
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
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.gdj.jewellery.btnPosArr'], $special_search)}
            </select>
        </td>
        ";

        
        return $text;
    }
}