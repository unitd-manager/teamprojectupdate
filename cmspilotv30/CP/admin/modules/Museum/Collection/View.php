<?
class CP_Admin_Modules_Museum_Collection_View extends CP_Common_Modules_Museum_Collection_View
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['collection_id'], 'center')}
            {$listObj->getListDataCell($fn->getYesNo($row['latest']), 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['collection_id'])}
            {$listObj->getListRowEnd($row['collection_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'c.title')}
        {$listObj->getListHeaderCell('Category', 'ca.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('Latest', 'c.latest', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'c.collection_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
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
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $latest = '';
        if ($cpCfg['m.museum.collection.showLatest'] == 1) {
            $latest = $formObj->getYesNoRRow("Latest", "latest", $row['latest']);
        }

        $metaData = '';
        if ($cpCfg['m.museum.collection.showMetaData'] == 1) {
            $metaData .= $formObj->getMetaData($row);
        }

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Collection', 'Collection');
        $expCategory = array('detailValue' => $row['category_title']);

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $sponsorUrl = "/index.php?module=museum_collection&_spAction=sponsor&showHTML=0&lang={$tv['lang'
        ]}&record_id={$row['collection_id']}";
        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
        {$formObj->getTBRow("Flickr Ref", 'flickr_ref', $row['flickr_ref'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$latest}
        {$formObj->getTBRow('Sponsor Url', 'sponsor_url', $sponsorUrl, array('isEditable' => 0))}
        ";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $fieldset3 = "
        {$formObj->getHTMLEditor('Sponsor Description', 'sponsor_description', $ln->gfv($row, 'sponsor_description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Collection Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getFieldSetWrapped('Sponsor Description', $fieldset3)}
        {$metaData}
        ";
        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'museum_collection', 'picture', $row)}
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
        WHERE b.section_type ='Collection'
        ORDER BY a.title
        ";
        $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);

        if ($tv['category_id'] != "") {
            $sqlCombo = "
            SELECT a.sub_category_id
                  ,a.title
            FROM sub_category a
            WHERE a.category_id = {$tv['category_id']}
            ORDER BY a.title
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
                {$cpUtil->getDropDown1($cpCfg['m.ecommerce.product.btnPosArr'], $special_search)}
            </select>
        </td>
        ";


        return $text;
    }
}