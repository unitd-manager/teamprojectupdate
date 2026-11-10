<?
class CP_Admin_Modules_LawNews_Correspondent_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $rowCounter = 0;

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
            {$listObj->getListDataCell($row['jurisdiction_title'])}
            {$listObj->getListDataCell($fn->getYesNo($row['active']))}
            {$listObj->getListSortOrderField($row, 'correspondent_id')}
            {$listObj->getListDataCell($row['correspondent_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['correspondent_id'])}
            {$listObj->getListRowEnd($row['correspondent_id'])}
            ";
            $rowCounter++;
        }

        $refTitle ='';
        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $listObj->getListHeaderCell('Ref. Title', 'c.title_ref');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'c.title')}
        {$refTitle}
        {$listObj->getListHeaderCell('Jurisdiction', 'j.jurisdiction_id')}
        {$listObj->getListHeaderCell('Active', 'c.active')}
        {$listObj->getListSortOrderImage('c')}
        {$listObj->getListHeaderCell('ID', 'c.correspondent_id', 'headerCenter')}
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
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $titleFld = 'title';

        if ($cpCfg['m.lawNews.jurisdiction.showRefTitle']){
            $titleFld = "IF(title_ref IS NOT NULL, CONCAT_WS('', title, ': ',title_ref), title)";
        }

        $sqljurisdiction = $fn->getDDSql('lawNews_jurisdiction', array('titleFld' => $titleFld));
        $expJuris = array('detailValue' => $row['jurisdiction_title']);

        $refTitle     = '';

        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $formObj->getTBRow('Ref. Title', 'title_ref', $row['title_ref']);
        }

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$refTitle}
        {$formObj->getDDRowBySQL('Jurisdiction', 'jurisdiction_id', $sqljurisdiction, $row['jurisdiction_id'], $expJuris)}
        {$this->fns->getYearsRow('Years Linked', 'years_linked', $row['years_linked'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )}
        {$formObj->getYesNoRRow('Active', 'active', $row['active'] )}
        ";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}

        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $cpCfg = Zend_Registry::get('cpCfg');

        $links = "
        {$displayLinkData->getLinkPortalMain('lawNews_correspondent', 'lawNews_reporterLink', 'Reporter Linked', $row)}
        {$displayLinkData->getLinkPortalMain('lawNews_correspondent', 'common_siteLink', 'Sites Linked', $row)}
        ";

        if ($cpCfg['m.law.correspondent.showAdsBannerLink']) {
            $links .= $displayLinkData->getLinkPortalMain('lawNews_correspondent', 'ads_bannerLink', 'Banner Linked', $row);
        }

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'lawNews_correspondent', 'picture', $row)}
        {$links}
        ";

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

        $special_search     = $fn->getReqParam('special_search');
        $jurisdiction_id    = $fn->getReqParam('jurisdiction_id');

        $titleFld = 'title';
        if ($cpCfg['m.lawNews.jurisdiction.showRefTitle']){
            $titleFld = "IF(title_ref IS NOT NULL, CONCAT_WS('', title, ': ',title_ref), title)";
        }

        $sqlJurisdiction = $fn->getDDSql('lawNews_jurisdiction', array('titleFld' => $titleFld));

        $sites = '';
        if ($cpCfg['cp.hasMultiSites'] == 1) {
            $site_id = $fn->getReqParam('site_id');

            $sqlSites = $fn->getDDSQL('common_site');

            $site = "
            <td>
                <select name='site_id'>
                    <option value=''>Site</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSites, $site_id)}
                </select>
            </td>
            ";
        }

        $text = "
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.lawNews.correspondent.specialSearchArr'], $special_search)}
            </select>
        </td>
        <td>
            <select name='jurisdiction_id'>
                <option value=''>Jurisdiction</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlJurisdiction, $jurisdiction_id)}
            </select>
        </td>
        {$site}
        ";

        return $text;
    }
}