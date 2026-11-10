<?
class CP_Admin_Modules_LawNews_Jurisdiction_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
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
            {$listObj->getListDataCell($row['region_name'])}
            {$listObj->getListSortOrderField($row, 'jurisdiction_id')}
            {$listObj->getListDataCell($row['jurisdiction_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['jurisdiction_id'])}
            {$listObj->getListRowEnd($row['jurisdiction_id'])}
            ";
            $rowCounter++;
        }

        $refTitle ='';
        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $listObj->getListHeaderCell('Ref. Title', 'j.title_ref');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'j.title')}
        {$refTitle}
        {$listObj->getListHeaderCell('Region', 'region_name')}
        {$listObj->getListSortOrderImage('j')}
        {$listObj->getListHeaderCell('ID', 'j.jurisdiction_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'j.published', 'headerCenter')}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $modVl = getCPModuleObj('core_valuelist');
        $sqlRegion = $modVl->model->getValuelistSQL('jurisdictionRegion');
        $expReg    = array('detailValue' => $row['region_name']);

        $refTitle     = '';

        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $formObj->getTBRow('Ref. Title', 'title_ref', $row['title_ref']);
        }

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$refTitle}
        {$formObj->getDDRowBySQL('Region', 'region_id', $sqlRegion, $row['region_id'], $expReg)}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'lawNews_jurisdiction', 'picture', $row)}
        ";

        if ($cpCfg['cp.hasMultiSites']) {
            $text .= $displayLinkData->getLinkPortalMain('lawNews_jurisdiction', 'common_siteLink', 'Sites Linked', $row);
        }

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
        $region_id          = $fn->getReqParam('region_id');

        $modVl = getCPModuleObj('core_valuelist');
        $sqlRegion = $modVl->model->getValuelistSQL('jurisdictionRegion');

        $site = '';
        if ($cpCfg['cp.hasMultiSites'] == 1) {
            $site_id = $fn->getReqParam('site_id');

            $sqlSites = $fn->getDDSQL('common_site');

            $site = "
            <td class='fieldValue'>
                <select name='site_id'>
                    <option value=''>Site</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSites, $site_id)}
                </select>
            </td>
            ";
        }

        $text = "
        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.webBasic.content.specialSearchArr'], $special_search)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='region_id'>
                <option value=''>Region</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlRegion, $region_id)}
            </select>
        </td>
        {$site}
        ";

        return $text;
    }
}