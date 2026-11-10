<?
class CP_Admin_Modules_Logistics_Company_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        
        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $email     = $row['email'];
            $website   = $row['website'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['company_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['type'])}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListDataCell("<a href='{$website}'>{$website}</a>")}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListRowEnd($row['company_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Company Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Email', 'email')}
        {$listObj->getListHeaderCell('Type', 'type')}
        {$listObj->getListHeaderCell('Country Name', 'country_name')}
        {$listObj->getListHeaderCell('Website', 'website')}
        {$listObj->getListHeaderCell('Phone', 'phone')}
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

        $fielset1 = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $expVl = array('sqlType' => 'OneField');
        $sqlType       = $fn->getValueListSQL('companyType','valuelist_id');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_code']);

        $formObj->mode = $tv['action'];    

        $fielset1 = "
        {$formObj->getTBRow('Company Name', 'company_name', $row['company_name'])}
        {$formObj->getDDRowBySQL('Type', 'type', $sqlType, $row['type'], $expVl)}
        {$formObj->getTBRow('Industry', 'industry', $row['industry'])}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Hand Phone', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('E-mail', 'email', $row['email'])}
        {$formObj->getTBRow('Office Address', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
        {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL('Country', 'country_code', $sqlCountry, $row['country_code'], $expCountry)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */

    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlStatus   = $fn->getValueListSQL('companyStatus');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $linksArray = Zend_Registry::get('linksArray');
        $media = Zend_Registry::get('media');
        
        
        $links   = "";

        $record_id = $fn->getIssetParam($row, 'company_id');

        $links .= $media->getRightPanelMediaDisplay('Attachments', 'logistics_company', 'attachment', $row);


        $text = "
        {$links}
        {$displayLinkData->getLinkPortalMain('logistics_company', 'logistics_contactLink', 'Contacts Linked', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $type        = $fn->getReqParam('type');
        $sqlType     = $fn->getValueListSQL('companyType');

        $country_code  = $fn->getReqParam('country_code');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='type'>
                <option value=''>Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlType, $type)}
            </select>
        </td>    
        <td class='fieldValue'>
            <select name='country_code'>
                <option value=''>Country</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $fn->getGeoCountrySQL(), $country_code)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";
        
        return $text;
    }
}