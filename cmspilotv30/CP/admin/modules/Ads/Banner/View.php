<?
class CP_Admin_Modules_Ads_Banner_View extends CP_Common_Modules_Ads_Banner_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        
        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}                                  
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListPublishedImage($row['published'], $row['banner_id'])}
            {$listObj->getListDataCell($row['banner_id'], 'center')}
            {$listObj->getListRowEnd($row['banner_id'])}            
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'b.title')}
        {$listObj->getListHeaderCell('Company Name', 'b.company_name')}
        {$listObj->getListHeaderCell('Published', 'b.published', 'headerCenter')}
        {$listObj->getListHeaderCell('Banner ID', 'b.banner_id', 'headerCenter')}        
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
        
        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTBRow('Company', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('External Link', 'external_link', $row['external_link'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )} 
        ";      
                  
        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
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
        
        $text  = '';

        $text .= "
        {$media->getRightPanelMediaDisplay('Picture', 'ads_banner', 'picture', $row)}
        ";

        if ($cpCfg['cp.hasMultiSites']) {
            $text .= $displayLinkData->getLinkPortalMain('ads_banner', 'common_siteLink', 'Sites Linked', $row);
        }

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $special_search  = $fn->getReqParam('special_search');
        
        //==================================================================//
        $spArray = array(
              "Match Report"
             ,"To Play"
        );

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
        {$site}
        ";

        
        return $text;
    }
}