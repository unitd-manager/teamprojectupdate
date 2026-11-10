<?
class CP_Admin_Modules_WebBasic_SubCategory_View extends CP_Common_Modules_WebBasic_SubCategory_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows           = "";
        $categoryFilter = "";
        $textFilter     = "";

        $rowCounter = 0;

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $refTitle ='';
            if ($cpCfg['m.webBasic.section.showRefTitle']){
                $refTitle = $listObj->getListDataCell($row['title_ref']);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$refTitle}
            {$fnModCountry->getCountryValueCellInList($row)}
            {$listObj->getListSortOrderField($row, "sub_category_id")}
            {$listObj->getListDataCell($row['sub_category_type'])}
            {$listObj->getListDataCell($row['section_title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_id'], "center")}
            {$listObj->getListPublishedImage($row['published'], $row['sub_category_id'])}
            {$listObj->getListRowEnd($row['sub_category_id'])}
            ";
            $rowCounter++ ;
        }

        $refTitle ='';
        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $listObj->getListHeaderCell('Ref. Title', 'sc.title_ref');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.subCategory.lbl.title', 'Title'), 'scc.title')}
        {$refTitle}
        {$fnModCountry->getCountryLabelCellInList()}
        {$listObj->getListSortOrderImage('sc')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.subCategory.lbl.subCategoryType', 'Sub Cat Child Type'), 'scc.sub_category_type')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.subCategory.lbl.section', 'Section'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.subCategory.lbl.category', 'Category'), 'ca.title')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.subCategory.lbl.id', 'ID'), 'sc.sub_category_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.subCategory.lbl.published', 'Published'), 'sc.published', 'headerCenter')}
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
        $ln = Zend_Registry::get('ln');
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.webBasic.subCategory.lbl.title', 'Title'), 'title')}
        {$fnModCountry->getCountryDropDown('new')}
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

        $textExternalLink = "";
        $textInternalLink = "";
        $textPublished = "";
        $textMeta = "";
        $shortDescription = "";
        $showInNav = '';
        $refTitle = '';
        $category2Text = '';
        $category3Text = '';
        $showInWeb     = '';
        $showInMobile  = '';
        $memberOnly = '';
        $nonMemberonly = '';

        $expCategory = array('detailValue' => $row['category_title'], 'sqlType' => 'hasSeperator');
        $fnMod = includeCPClass('ModuleFns', 'webBasic_subCategory');
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $formObj->getTBRow('Ref. Title', 'title_ref', $row['title_ref']);
        }

        $wMultiYear = getCPWidgetObj('common_multiYear', false);

        $catObj = getCPModuleObj('webBasic_category');
        $sqlCategory = $catObj->model->getCategoryGroupedBySectionSQL();

        if($cpCfg['m.webBasic.subCategory.showExtLinkField'] == 1){
            $textExternalLink .= $formObj->getTBRow($ln->gd('m.webBasic.subCategory.lbl.externalLink', 'External Link'), 'external_link', $row['external_link']);
        }

        if ($cpCfg['m.webBasic.subCategory.showIntLinkField'] == 1){
            $textInternalLink = $formObj->getTBRow($ln->gd('m.webBasic.subCategory.lbl.internalLink', 'Internal Link'), 'internal_link', $row['internal_link'] );
        }

        if ($cpCfg['m.webBasic.displayShowInNavFld']){
            $showInNav = $formObj->getYesNoRRow($ln->gd('m.webBasic.subCategory.lbl.showInNavigation', 'Show in Navigation'), 'show_in_nav', $row['show_in_nav']);
        }

        if($cpCfg['m.webBasic.subCategory.showMetaData'] == 1){
            $textMeta = $formObj->getMetaData($row);
        }

        if ($cpCfg['m.webBasic.subCategory.showShortDescription'] == 1){
            if($cpCfg['m.webBasic.subCategory.showFCKShortDescription'] == 1){
                $shortDescription .= $formObj->getHTMLEditor('Short Description', 'description_short', $ln->gfv($row, 'description_short', '0'));
            } else {
                $shortDescription .= $formObj->getTARow('Short Description' , 'description_short', $ln->gfv($row, 'description_short', '0'));
            }
        }
        if ($cpCfg['m.webBasic.subCategory.hasCategory2']){
            $expCategory2 = array('detailValue' => $row['category2_title'], 'sqlType' => 'hasSeperator');
            $category2Text = "
            {$formObj->getDDRowBySQL($ln->gd('m.webBasic.subCategory.lbl.category2', 'Category 2'), 'category2_id', $sqlCategory,
                       $row['category2_id'], $expCategory2)}
            ";
        }
        if ($cpCfg['m.webBasic.subCategory.hasCategory3']){
            $expCategory3 = array('detailValue' => $row['category3_title'], 'sqlType' => 'hasSeperator');
            $category3Text = "
            {$formObj->getDDRowBySQL($ln->gd('m.webBasic.subCategory.lbl.category3', 'Category 3'), 'category3_id', $sqlCategory,
                       $row['category3_id'], $expCategory3)}
            ";
        }

        if ($cpCfg['m.webBasic.subCategory.showOkForWeb']){
            $showInWeb = $formObj->getYesNoRRow('OK for Web', 'ok_for_web', $row['ok_for_web']);
        }

        if ($cpCfg['m.webBasic.subCategory.showOkForMobile']){
            $showInMobile = $formObj->getYesNoRRow('OK for Mobile', 'ok_for_mobile', $row['ok_for_mobile']);
        }

        if($cpCfg['m.webBasic.subCategory.hasMemberOnly'] == 1){
            $memberOnly = $formObj->getYesNoRRow('Member Only', 'member_only', $row['member_only']);
        }

        if ($cpCfg['m.webBasic.subCategory.hasNonMemberOnly'] == 1){
            $nonMemberonly = $formObj->getYesNoRRow('Non Member Only', 'non_member_only', $row['non_member_only']);
        }

        $noFollow = '';
        if($cpCfg['m.webBasic.subCategory.showNoFollow']){
            $noFollow = $formObj->getYesNoRRow('Robots No Follow', 'no_follow', $row['no_follow']);
        }

        $fieldset2 = '';
        if ($cpCfg['m.webBasic.subCategory.showDescription']){
            $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));
            $fieldset2 = $formObj->getFieldSetWrapped('Description', $fieldset2);
        }

        $fielset1 = "
        {$formObj->getTBRow($ln->gd('m.webBasic.subCategory.lbl.title', 'Title'), 'title', $ln->gfv($row, 'title', '0'))}
        {$refTitle}
        {$formObj->getDDRowBySQL($ln->gd('m.webBasic.subCategory.lbl.category', 'Category'), 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowByArr($ln->gd('m.webBasic.subCategory.lbl.subCategoryType', 'Sub Category Type'), 'sub_category_type', $cpCfg['m.webBasic.subCategory.recordTypeArr'], $row['sub_category_type'])}
        {$category2Text}
        {$category3Text}
        {$fnModCountry->getCountryDropDown($formObj->mode, $row)}
        {$shortDescription}
        {$textExternalLink}
        {$textInternalLink}
        {$formObj->getYesNoRRow($ln->gd('m.webBasic.subCategory.lbl.published', 'Published'), 'published', $row['published'])}
        {$memberOnly}
        {$nonMemberonly}
        {$showInNav}
        {$showInWeb}
        {$showInMobile}
        {$noFollow}
        {$wMultiYear->view->getCpYearRow($row)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.webBasic.subCategory.lbl.subCategoryDetails', 'Sub Category Details'), $fielset1)}
        {$fieldset2}
        {$textMeta}
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
        $ln = Zend_Registry::get('ln');

        $text = '';
        $subCatPic    = '';
        $subCatBanner = '';
        $links = '';

        if ($cpCfg['m.webBasic.subCategory.hasBanner'] == 1) {
            $subCatBanner = $media->getRightPanelMediaDisplay($ln->gd('m.webBasic.subCategory.link.banner', 'Banner'), 'webBasic_subCategory', 'banner', $row);
        }

        if ($cpCfg['m.webBasic.subCategory.hasPicture'] == 1) {
            $subCatPic = $media->getRightPanelMediaDisplay($ln->gd('m.webBasic.subCategory.link.picture', 'Picture'), 'webBasic_subCategory', 'picture', $row);
        }


        if ($cpCfg['cp.hasMultiSites']) {
            $links = $displayLinkData->getLinkPortalMain('webBasic_subCategory', 'common_siteLink', 'Sites Linked', $row);
        }

        $text = "
        {$subCatBanner}
        {$subCatPic}
        {$links}
        ";

        return $text;
    }

    /**
     *
     */
    function getSubCategoryByCategoryJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $rows = "";

        $category_id = $fn->getReqParam('category_id');

        $json = array();

        if ($category_id == ''){
            return json_encode($json);
        }

        $SQL = "
        SELECT sub_category_id
              ,title
        FROM sub_category
        WHERE category_id = '{$category_id}'
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array('value' => $row['sub_category_id'], 'caption' => $row['title']);
        }

        return json_encode($json);
    }


    /**
     *
     */
    function getQuickSearch() {
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $dropdown = "";
        $site = '';

        if ($tv['section_id'] != "") {
            $SQLCat = getCPModuleObj('webBasic_category')->model->getCategorySQL($tv['section_id']);
            $dropdown = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $secObj = getCPModuleObj('webBasic_section');
        $sqlSection = $secObj->model->getSectionSQL();

        $section_id = $tv['section_id'];
        if (!isset($_REQUEST['section_id'])
                && $cpCfg['m.webBasic.subCategory.defaultSectionType'] != '') {
            $sectionRec = getCPModelObj('webBasic_section')
                          ->getRecordBySectionType($cpCfg['m.webBasic.subCategory.defaultSectionType']);
            $section_id = $sectionRec['section_id'];
        }

        if ($cpCfg['cp.hasMultiSites'] == 1) {
            $site_id = $fn->getReqParam('site_id');

            $sqlSites = $fn->getDDSQL('common_site');

            $site = "
            <td class='fieldValue'>
                <select name='site_id'>
                    <option value=''>{$ln->gd('m.webBasic.subCategory.lbl.site', 'Site')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSites, $site_id)}
                </select>
            </td>
            ";
        }

        $text = "
        <td class='fieldValue'>
           <select name='section_id'>
               <option value=''>{$ln->gd('m.webBasic.subCategory.lbl.section', 'Section')}</option>
               {$dbUtil->getDropDownFromSQLCols2($db, $sqlSection, $section_id)}
           </select>
        </td>

        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>{$ln->gd('m.webBasic.subCategory.lbl.category', 'Category')}</option>
                {$dropdown}
            </select>
        </td>
        {$site}
        {$fnModCountry->getCountryDropDown('search')}
        ";

        return $text;
    }
}