<?
class CP_Admin_Modules_Common_Site_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $sortOrder = '';
            if($cpCfg['m.common.site.hasSortOrder']){
                $sortOrder = $listObj->getListSortOrderField($row, 'site_id');
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$sortOrder}
            {$listObj->getListDataCell($row['site_url'])}
            {$listObj->getListDataCell($row['default_language'])}
            {$listObj->getListDataCell($row['admin_email'])}
            {$listObj->getListDataCell($row['theme'])}
            {$listObj->getListDataCell($row['skin'])}
            {$listObj->getListPublishedImage($row['published'], $row['site_id'])}
            {$listObj->getListDataCell($row['site_id'], 'center')}
            {$listObj->getListRowEnd($row['site_id'])}
            ";
            $rowCounter++ ;
        }

        $sortOrder = '';
        if($cpCfg['m.common.site.hasSortOrder']){
            $sortOrder = $listObj->getListSortOrderImage('s');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 's.title')}
        {$sortOrder}
        {$listObj->getListHeaderCell('Site Url', 's.site_url')}
        {$listObj->getListHeaderCell('Default Language', 's.default_language')}
        {$listObj->getListHeaderCell('Email', 's.admin_email')}
        {$listObj->getListHeaderCell('Theme', 's.theme')}
        {$listObj->getListHeaderCell('Skin', 's.skin')}
        {$listObj->getListHeaderCell('Published', 's.published', 'headerCenter')}
        {$listObj->getListHeaderCell('Site ID', 's.site_id', 'headerCenter')}
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
        {$formObj->getTBRow('Site Name', 'title')}
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
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $exp = array('useKey' => 1);

        $nonUniqueSitesFlds = '';
        if(!$cpCfg['cp.hasMultiUniqueSites']){
            $nonUniqueSitesFlds = "
            {$formObj->getTBRow('Google Analytics ID', 'google_analytics_id', $row['google_analytics_id'])}
            ";
        }

        $caption = '';
        if($cpCfg['m.common.site.hasCaption']){
            $caption = "
            {$formObj->getTARow('Caption', 'caption', $ln->gfv($row, 'caption', '0') )}
            ";
        }

        $siteKey = '';
        if($cpCfg['m.common.site.hasSiteKey']){
            $siteKey = "
            {$formObj->getTBRow('Key', 'site_key', $row['site_key'])}
            ";
        }

        $urlAlias = '';
        if($dbUtil->getColumnExists('site', 'site_url_alias')){
            $urlAlias = "{$formObj->getTBRow('Alias Url', 'site_url_alias', $row['site_url_alias'])}";
        }
            
        $fieldset1 = "
        {$formObj->getTBRow('Site Name', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTBRow('Email', 'admin_email', $row['admin_email'])}
        {$formObj->getTBRow('Site Url', 'site_url', $row['site_url'])}
        {$urlAlias}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )}
        {$caption}
        {$siteKey}
        ";

        $adOpsSitenameFlds = '';
        if($cpCfg['m.common.site.hasAdOpsSiteName']){
            $adOpsSitenameFlds = "
            {$formObj->getTBRow('AdOps Site Name', 'ad_ops_site_name', $row['ad_ops_site_name'])}
            ";
        }
        $additionalMetaTags = '';
        if($cpCfg['m.common.site.hasAdditionalMetaTags']){
            $additional_meta_tags = ($formObj->mode == 'detail') ?
                                    htmlspecialchars($row['additional_meta_tags']) : $row['additional_meta_tags'];
            $additionalMetaTags = "
            {$formObj->getTARow('Additional Meta Tags', 'additional_meta_tags', $additional_meta_tags)}
            ";
        }

        $metaData         = '';
        if ($cpCfg['m.common.site.showMetaData'] == 1) {
            $metaData = $formObj->getMetaData($row);
        }

        $additonalAnalyticsScript = '';
        if($cpCfg['m.common.site.hasAdditonalAnalyticsScript']){
            $additional_analytics_script = ($formObj->mode == 'detail') ?
                                    htmlspecialchars($row['additional_analytics_script']) : $row['additional_analytics_script'];
            $additonalAnalyticsScript = "
            {$formObj->getTARow('Analytics Tracking Code', 'additional_analytics_script', $additional_analytics_script)}
            ";
        }
        
        $tagLine = '';
        if ($cpCfg['m.common.site.hasTagline']){
            $tagLine = "
            {$formObj->getTBRow('Tag Line 1', 'tag_line', $row['tag_line'])}
            {$formObj->getTBRow('Tag Line 2', 'tag_line2', $row['tag_line2'])}
            ";
        }
        
        $fieldset2 = "
        {$formObj->getDDRowByArr('Default Language', 'default_language', $cpCfg['cp.availableLanguages'], $row['default_language'], $exp)}
        {$formObj->getDDRowByArr('Theme', 'theme', $cpCfg['cp.availableThemes'], $row['theme'])}
        {$formObj->getDDRowByArr('Skin', 'skin', $cpCfg['cp.availableSkins'], $row['skin'])}
        {$nonUniqueSitesFlds}
        {$adOpsSitenameFlds}
        {$additionalMetaTags}
        {$additonalAnalyticsScript}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Site Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Site Specific Values', $fieldset2)}
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

        $secondLogo = '';
        $links = '';
        
        if($cpCfg['m.common.site.hasSecondLogo']){
            $secondLogo = "
            {$media->getRightPanelMediaDisplay('Second Logo', 'common_site', 'secondLogo', $row)}
            ";
        }

        if ($cpCfg['m.common.site.hasLanguage']){
            $links .= $displayLinkData->getLinkPortalMain('common_site', 'common_languageLink', 'Language', $row);
        }

        $text = "
        {$media->getRightPanelMediaDisplay('Main Logo', 'common_site', 'logo', $row)}
        {$secondLogo}
        {$links}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}