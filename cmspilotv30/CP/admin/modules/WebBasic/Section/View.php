<?
class CP_Admin_Modules_WebBasic_Section_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
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
            {$listObj->getListSortOrderField($row, 'section_id')}
            {$listObj->getListDataCell($row['section_type'])}
            {$listObj->getListDataCell($row['button_position'])}
            {$fn->getSiteFldForList($row)}
            {$listObj->getListDataCell($row['section_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['section_id'])}
            {$listObj->getListRowEnd($row['section_id'])}
            ";
            $rowCounter++ ;
        }

        $refTitle ='';
        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $listObj->getListHeaderCell('Ref. Title', 's.title_ref');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.section.lbl.title', 'Title'), 's.title')}
        {$refTitle}
        {$fnModCountry->getCountryLabelCellInList()}
        {$listObj->getListSortOrderImage('s')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.section.lbl.sectionType', 'Section Type'), 's.section_type')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.section.lbl.btnPosition', 'Button Position'), 's.button_position')}
        {$fn->getSiteLabelForList()}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.section.lbl.id', 'ID'), 's.section_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.section.lbl.published', 'Published'), 's.published', 'headerCenter')}
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
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.webBasic.section.lbl.title', 'Title'), 'title')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $externalLink = '';
        $internalLink = '';
        $memberOnly   = '';
        $nonMember    = '';
        $metaText     = '';
        $description  = '';
        $cssStyleName = '';
        $showInNav    = '';
        $caption      = '';
        $refTitle     = '';
        $showInWeb    = '';
        $showInMobile = '';
        $adSpaces = '';

        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $formObj->getTBRow('Ref. Title', 'title_ref', $row['title_ref']);
        }

        $wMultiYear = getCPWidgetObj('common_multiYear', false);
        $wMultiUniqueSite = getCPWidgetObj('common_multiUniqueSite', false);

        if($cpCfg['m.webBasic.section.showExtLinkField'] == 1){
            $externalLink = $formObj->getTBRow($ln->gd('m.webBasic.section.lbl.externalLink', 'External Link'), 'external_link', $row['external_link']);
        }

        if ($cpCfg['m.webBasic.section.showIntLinkField'] == 1){
            $internalLink = $formObj->getTBRow($ln->gd('m.webBasic.section.lbl.internalLink', 'Internal Link'), 'internal_link', $row['internal_link'] );
        }

        if($cpCfg['m.webBasic.section.showDescription'] == 1){
            $description = $formObj->getTBRow('Description', 'description', $row['description']);
        }

        if($cpCfg['m.webBasic.section.hasMemberOnly'] == 1){
            $memberOnly = $formObj->getYesNoRRow('Member Only', 'member_only', $row['member_only']);
        }

        if($cpCfg['m.webBasic.section.hasNonMember'] == 1){
            $nonMember = $formObj->getYesNoRRow('Non Member Only', 'non_member_only', $row['non_member_only']);
        }

        if($cpCfg['m.webBasic.section.showMetaData'] == 1){
            $metaText = $formObj->getMetaData($row);
        }

        if($cpCfg['m.webBasic.section.hasCSSStyleName'] == 1){
            $cssStyleName = $formObj->getTBRow('CSS Style Name', 'css_style_name', $row['css_style_name']);
        }

        if ($cpCfg['m.webBasic.displayShowInNavFld']){
            $showInNav = $formObj->getYesNoRRow($ln->gd('m.webBasic.section.lbl.showInNavigation', 'Show in Navigation'),
                            'show_in_nav', $row['show_in_nav']);
        }

        if($cpCfg['m.webBasic.section.hasCaption'] == 1){
            $caption = $formObj->getTBRow('Caption', 'caption', $row['caption']);
        }

        if ($cpCfg['m.webBasic.section.showOkForWeb']){
            $showInWeb = $formObj->getYesNoRRow('OK for Web', 'ok_for_web', $row['ok_for_web']);
        }

        if ($cpCfg['m.webBasic.section.showOkForMobile']){
            $showInMobile = $formObj->getYesNoRRow('OK for Mobile', 'ok_for_mobile', $row['ok_for_mobile']);
        }

        $noFollow = '';
        if($cpCfg['m.webBasic.section.showNoFollow']){
            $noFollow = $formObj->getYesNoRRow('Robots No Follow', 'no_follow', $row['no_follow']);
        }

        if ($cpCfg['m.webBasic.section.hasAdSpaces']){
            $adSpaces = "
            {$formObj->getTBRow('Ad Space1', 'ad_space1', $row['ad_space1'])}
            {$formObj->getTARow('Ad Space2', 'ad_space2', $row['ad_space2'])}
            {$formObj->getTARow('Ad Space3', 'ad_space3', $row['ad_space3'])}
            ";

            $adSpaces = $formObj->getFieldSetWrapped('Ad Embed Codes', $adSpaces);
        }

        $aliasToSection = '';
        if($cpCfg['m.webBasic.section.showAliasToSection']){
            if($cpCfg['cp.hasMultiUniqueSites'] && $cpCfg['m.webBasic.section.showSectionsFromAllSitesInAliasToSecFld']){
                $sqlAliasSections = $this->model->getSectionsGroupedBySiteSQL();

                $aliasToSecTitle = '';
                if ($row['alias_to_section_id'] != ''){
                    $aliasSecRec = $fn->getRecordRowByID('section', 'section_id', $row['alias_to_section_id'], array('globalForAllSites' => true));

                    if (is_array($aliasSecRec)){
                        $siteRec = $fn->getRecordRowByID('site', 'site_id', $aliasSecRec['site_id'], array('globalForAllSites' => true));
                        $siteTitle = is_array($siteRec) ? $siteRec['title'] : '';
                        $aliasToSecTitle = $siteTitle . ' / ' . $aliasSecRec['title'];
                    }
                }
                $expAliasSec = array('detailValue' => $aliasToSecTitle, 'sqlType' => 'hasSeperator');
                $aliasToSection = $formObj->getDDRowBySQL('Alias to', 'alias_to_section_id', $sqlAliasSections, $row['alias_to_section_id'], $expAliasSec);
            } else {
                // fill this when required
            }
        }

        $fnMod = includeCPClass('ModuleFns', 'webBasic_section');
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $fielset1 = "
        {$formObj->getTBRow($ln->gd('m.webBasic.section.lbl.title', 'Title'), 'title', $ln->gfv($row, 'title', '0'))}
        {$refTitle}
        {$formObj->getDDRowByArr($ln->gd('m.webBasic.section.lbl.sectionType', 'Section Type'), 'section_type', $cpCfg['m.webBasic.section.recordTypeArr'], $row['section_type'])}
        {$fnModCountry->getCountryDropDown($formObj->mode, $row)}
        {$wMultiUniqueSite->view->getSiteRow($row)}
        {$formObj->getDDRowByArr($ln->gd('m.webBasic.section.lbl.btnPosition', 'Button Position'), 'button_position', $cpCfg['m.webBasic.section.btnPosArr'], $row['button_position'])}
        {$externalLink}
        {$internalLink}
        {$cssStyleName}
        {$caption}
        {$description}
        {$formObj->getYesNoRRow($ln->gd('m.webBasic.section.lbl.published', 'Published'), 'published', $row['published'])}
        {$memberOnly}
        {$nonMember}
        {$showInNav}
        {$showInWeb}
        {$showInMobile}
        {$noFollow}
        {$wMultiYear->view->getCpYearRow($row)}
        {$aliasToSection}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.webBasic.section.lbl.sectionDetails', 'Section Details'), $fielset1)}
        {$adSpaces}
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

        if ($cpCfg['m.webBasic.section.hasBanner'] == 1) {
            $text .= $media->getRightPanelMediaDisplay($ln->gd('m.webBasic.section.link.banner', 'Banner'), "webBasic_section", "banner", $row);
        }

        if ($cpCfg['m.webBasic.section.hasPicture'] == 1) {
            $text .= $media->getRightPanelMediaDisplay($ln->gd('m.webBasic.section.link.picture', 'Picture'), "webBasic_section", "picture", $row);
        }

        if ($cpCfg['cp.hasMultiSites']) {
            $text .= $displayLinkData->getLinkPortalMain('webBasic_section', 'common_siteLink', $ln->gd('m.webBasic.section.link.sitesLinked', 'Sites Linked'), $row);
        }

        if ($cpCfg['m.webBasic.section.showAdsBannerLink']) {
            $text .= $displayLinkData->getLinkPortalMain('webBasic_section', 'ads_bannerLink', $ln->gd('m.webBasic.section.link.bannersLinked', 'Banners Linked'), $row);
        }

        return $text;
    }


    /**
     *
     */
    function getUpdateSectionSEOTitle() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "";

        //======================================================//
        $SQL    = "SELECT * FROM section";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $seo_title = strtolower($fn->_prepare_url_text($row['title']));
            $SQL2      = "UPDATE section SET seo_title = '{$seo_title}' WHERE section_id = {$row['section_id']}";
            $db->sql_query($SQL2);

            print "{$row['title']} => {$seo_title}<br>";
        }
        //======================================================//

        $text .= "UPDATE COMPLETE";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $special_search  = $fn->getReqParam('special_search');

        $button_position = $fn->getReqParam('button_position');
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $site = '';

        if ($cpCfg['cp.hasMultiSites'] == 1) {
            $site_id = $fn->getReqParam('site_id');

            $sqlSites = $fn->getDDSQL('common_site');

            $site = "
            <td class='fieldValue'>
                <select name='site_id'>
                    <option value=''>{$ln->gd('m.webBasic.section.lbl.site', 'Site')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSites, $site_id)}
                </select>
            </td>
            ";
        }

        $specialSearch = '';
        if ($cpCfg['m.webBasic.section.showSpecialSearch'] == 1) {
            $specialSearch = "
            <td>
                <select name='special_search'>
                    <option value=''>{$ln->gd('m.webBasic.content.lbl.specialSearch', 'Special Search')}</option>
                    {$cpUtil->getDropDown1($cpCfg['m.webBasic.section.specialSearchArr'], $special_search)}
                </select>
            </td>
            ";
        }

        $text = "
        <td class='fieldValue'>
        <select name='button_position'>
            <option value=''>{$ln->gd('m.webBasic.section.lbl.position', 'Position')}</option>
            {$cpUtil->getDropDown1($cpCfg['m.webBasic.section.btnPosArr'], $button_position)}
        </select>
        </td>
        {$site}
        {$specialSearch}
        {$fnModCountry->getCountryDropDown('search')}
        {$fn->getSiteDropDown('search')}
        ";

        return $text;
    }
}