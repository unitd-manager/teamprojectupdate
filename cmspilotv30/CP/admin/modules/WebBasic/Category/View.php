<?
class CP_Admin_Modules_WebBasic_Category_View extends CP_Common_Modules_WebBasic_Category_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $published = "";
        $filter = "";

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
            {$listObj->getListSortOrderField($row, 'category_id')}
            {$listObj->getListDataCell($row['category_type'])}
            {$listObj->getListDataCell($row['section_title'])}
            {$listObj->getListDataCell($row['category_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['category_id'])}
            {$listObj->getListRowEnd($row['category_id'])}
            ";
            $rowCounter++ ;
        }

        $refTitle ='';
        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $listObj->getListHeaderCell('Ref. Title', 'c.title_ref');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.category.lbl.title', 'Title'), 'c.title')}
        {$refTitle}
        {$fnModCountry->getCountryLabelCellInList()}
        {$listObj->getListSortOrderImage()}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.category.lbl.categoryType', 'Category Type'), 'c.category_type')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.category.lbl.section', 'Section'), 's.title')}
        {$filter}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.category.lbl.id', 'ID'), 'c.category_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.category.lbl.published', 'Published'), 'c.published', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.webBasic.category.lbl.title', 'Title'), 'title')}
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
        $am = Zend_Registry::get('am');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $sectionText      = '';
        $externalLink     = '';
        $internalLink     = '';
        $textPublished    = '';
        $textCaption      = '';
        $memberOnly       = '';
        $nonMemberonly    = '';
        $shortDescription = '';
        $description      = '';
        $metaText         = '';
        $cssStyleName     = '';
        $showInNav        = '';
        $refTitle         = '';
        $showInWeb        = '';
        $showInMobile     = '';
        $productGroup     = '';
        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $formObj->getTBRow('Ref. Title', 'title_ref', $row['title_ref']);
        }

        $wMultiYear = getCPWidgetObj('common_multiYear', false);

        $expSection = array('detailValue' => $row['section_title']);

        $secObj = getCPModuleObj('webBasic_section');
        $sqlSection = $secObj->model->getSectionSQL();

        $sectionText .= $formObj->getDDRowBySQL($ln->gd('m.webBasic.category.lbl.section', 'Section'), 'section_id', $sqlSection, $row['section_id'], $expSection);

        if ($cpCfg['m.webBasic.section.showExtLinkField'] == 1){
            $externalLink .= $formObj->getTBRow($ln->gd('m.webBasic.category.lbl.externalLink', 'External Link'), 'external_link', $row['external_link']);
        }

        if ($cpCfg['m.webBasic.category.showIntLinkField'] == 1){
            $internalLink = $formObj->getTBRow($ln->gd('m.webBasic.category.lbl.internalLink', 'Internal Link'), 'internal_link', $row['internal_link'] );
        }

        $textPublished = $formObj->getYesNoRRow($ln->gd('m.webBasic.category.lbl.published', 'Published'), 'published', $row['published']);

        if ($cpCfg['m.webBasic.category.showMetaData'] == 1){
            $metaText = $formObj->getMetaData($row);
        }

        if ($cpCfg['m.webBasic.category.hasCaption'] == 1){
            $textCaption = $formObj->getTBRow('Caption', 'caption', $ln->gfv($row, 'caption', '0'));
        }

        if($cpCfg['m.webBasic.category.hasMemberOnly'] == 1){
            $memberOnly = $formObj->getYesNoRRow('Member Only', 'member_only', $row['member_only']);
        }

        if ($cpCfg['m.webBasic.category.hasNonMemberOnly'] == 1){
            $nonMemberonly = $formObj->getYesNoRRow('Non Member Only', 'non_member_only', $row['non_member_only']);
        }

        if ($cpCfg['m.webBasic.displayShowInNavFld']){
            $showInNav = $formObj->getYesNoRRow($ln->gd('m.webBasic.category.lbl.showInNavigation', 'Show in Navigation'), 'show_in_nav', $row['show_in_nav']);
        }

        $noFollow = '';        
        if($cpCfg['m.webBasic.category.showNoFollow']){
            $noFollow = $formObj->getYesNoRRow('Robots No Follow', 'no_follow', $row['no_follow']);
        }        
        
        //if ($cpCfg['m.webBasic.category.hasBanner'] == 1){
        if ($cpCfg['m.webBasic.category.showShortDescription'] == 1){
            if($cpCfg['m.webBasic.category.showFCKForShortDescription'] == 1){
                $shortDescription .= $formObj->getHTMLEditor(
                                        'Short Description',
                                        'description_short',
                                        $ln->gfv($row, 'description_short', '0')
                );
            } else {
                $shortDescription .= $formObj->getTARow(
                                        'Short Description',
                                        'description_short',
                                        $ln->gfv($row, 'description_short', '0')
                );
            }
        }

        if($cpCfg['m.webBasic.category.hasCSSStyleName'] == 1){
            $cssStyleName = $formObj->getTBRow($ln->gd('m.webBasic.category.lbl.cssStyleName', 'CSS style name'), 'css_style_name', $row['css_style_name']);
        }

        $fieldset2 = '';
        if ($cpCfg['m.webBasic.category.showDescription'] == 1){
            $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));
            $fieldset2 = $formObj->getFieldSetWrapped('Description', $fieldset2);
        }

        if ($cpCfg['m.webBasic.category.showMetaData'] == 1){
            $metaText = $formObj->getMetaData($row);
        }

        if ($cpCfg['m.webBasic.category.showOkForWeb']){
            $showInWeb = $formObj->getYesNoRRow('OK for Web', 'ok_for_web', $row['ok_for_web']);
        }

        if ($cpCfg['m.webBasic.category.showOkForMobile']){
            $showInMobile = $formObj->getYesNoRRow('OK for Mobile', 'ok_for_mobile', $row['ok_for_mobile']);
        }

				// This is code is used for trading mass product group
		        /*$sqlProductGroup = "
		        SELECT product_group_id
		              ,title 
		        FROM product_group
		        ";*/

        //used in tradingsg module
        /*if ($cpCfg['m.webBasic.category.displayTradingmassProductGroup'] == 1){
                        $expStaff   = array('detailValue' => $row['']);
						$productGroup = $formObj->getDDRowBySQL('Product Group', 'product_group_id', $sqlProductGroup, $row['product_group_id']);
        }*/        


        $fnMod = includeCPClass('ModuleFns', 'webBasic_category');
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
		//{$productGroup}

        $fielset1 = "
        {$formObj->getTBRow($ln->gd('m.webBasic.category.lbl.title', 'Title'), 'title', $ln->gfv($row, 'title', '0'))}
        {$refTitle}
        {$sectionText}
        {$formObj->getDDRowByArr($ln->gd('m.webBasic.category.lbl.categoryType', 'Category Type'), 'category_type', $cpCfg['m.webBasic.category.recordTypeArr'], $row['category_type'])}
        {$fnModCountry->getCountryDropDown($formObj->mode, $row)}
        {$externalLink}
        {$internalLink}
        {$cssStyleName}
        {$textPublished}
        {$textCaption}
        {$memberOnly}
        {$nonMemberonly}
        {$showInNav}
        {$showInWeb}
        {$showInMobile}
        {$noFollow}
        {$wMultiYear->view->getCpYearRow($row)}
        {$shortDescription}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.webBasic.category.lbl.categoryDetails', 'Category Details'), $fielset1)}
        {$fieldset2}
        {$metaText}
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

        $text  = '';

        if ($cpCfg['m.webBasic.category.hasBanner'] == 1) {
            $text .= $media->getRightPanelMediaDisplay($ln->gd('m.webBasic.category.link.banner', 'Banner'), "webBasic_category", "banner", $row);
        }

        if ($cpCfg['m.webBasic.category.hasPicture'] == 1) {
            $text .= $media->getRightPanelMediaDisplay($ln->gd('m.webBasic.category.link.picture', 'Picture'), "webBasic_category", "picture", $row);
        }

        if ($cpCfg['cp.hasMultiSites']) {
            $text .= $displayLinkData->getLinkPortalMain('webBasic_category', 'common_siteLink', 'Sites Linked', $row);
        }

        return $text;
    }

    /**
     *
     */
    function getCategoryBySectionJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $section_id = $fn->getReqParam('section_id');

        $json = array();

        if ($section_id == ''){
            return json_encode($json);
        }

        $SQL = "
        SELECT category_id
              ,title
        FROM category
        WHERE section_id = '{$section_id}'
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array('value' => $row['category_id'], 'caption' => $row['title']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $ddSection = "";
        $catFilter = "";
        $countryFilter = '';
        $site = '';

        $secObj = getCPModuleObj('webBasic_section');
        $sqlSection = $secObj->model->getSectionSQL();

        $section_id = $tv['section_id'];

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        if ($cpCfg['cp.hasMultiSites'] == 1) {
            $site_id = $fn->getReqParam('site_id');
            $sqlSites = $fn->getDDSQL('common_site');

            $site = "
            <td class='fieldValue'>
                <select name='site_id'>
                    <option value=''>{$ln->gd('m.webBasic.category.lbl.site', 'Site')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSites, $site_id)}
                </select>
            </td>
            ";
        }


        $text = "
        <td>
            <select name='section_id'>
                <option value=''>{$ln->gd('m.webBasic.category.lbl.section', 'Section')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSection, $section_id)}
            </select>
        </td>
        {$catFilter}
        {$site}
        {$fnModCountry->getCountryDropDown('search')}
        ";

        return $text;
    }
}