<?
class CP_Admin_Modules_Common_Country_View extends CP_Common_Modules_Common_Country_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $iddCode = '';
            if ($cpCfg['m.common.country.hasIddCode']) {
                $iddCode = "
                {$listObj->getListDataCell($row['idd_code'])}                      
                ";
            }            
            
            $currency = '';
            if ($cpCfg['m.common.country.hasCurrency']) {
                $currency = "
                {$listObj->getListDataCell($row['currency'])}                      
                ";
            }            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}                            
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}           
            {$listObj->getListDataCell($row['default_language'])}                      
            {$listObj->getListDataCell($row['admin_email'])}                      
            {$listObj->getListDataCell($row['country_code'])}                      
            {$iddCode}
            {$currency}
            {$listObj->getListPublishedImage($row['published'], $row['country_id'])}
            {$listObj->getListDataCell($row['country_id'], 'center')}
            {$listObj->getListRowEnd($row['country_id'])}                              
            ";
            $rowCounter++ ;
        }

        $iddCode = '';
        if ($cpCfg['m.common.country.hasIddCode']) {
            $iddCode = "
            {$listObj->getListHeaderCell('Idd Code', 'c.idd_code')}
            ";
        }        
        $currency = '';
        if ($cpCfg['m.common.country.hasCurrency']) {
            $currency = "
            {$listObj->getListHeaderCell('Currency', 'c.currency')}
            ";
        }        
        $text = "
        {$listObj->getListHeader()}                                                  
        {$listObj->getListHeaderCell($ln->gd('m.common.country.lbl.title', 'Title'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.common.country.lbl.defaultLanguage', 'Default Language'), 'c.default_language')}
        {$listObj->getListHeaderCell($ln->gd('m.common.country.lbl.email', 'Email'), 'c.admin_email')}
        {$listObj->getListHeaderCell($ln->gd('m.common.country.lbl.countryCode', 'Country Code'), 'c.country_code')}
        {$iddCode}
        {$currency}
        {$listObj->getListHeaderCell($ln->gd('m.common.country.lbl.published', 'Published'), 'c.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.common.country.lbl.countryID', 'Country ID'), 'c.country_id', 'headerCenter')}
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

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.common.country.lbl.countryName', 'Country Name'), 'title')}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $text = '';
        $exp = array('useKey' => 1);

        $iddCode = '';
        if ($cpCfg['m.common.country.hasIddCode']) {
            $iddCode = "
            {$formObj->getTextBoxRow($ln->gd('m.common.country.lbl.iddCode', 'Idd Code'), 'idd_code', $row['idd_code'])} 
            ";
        }

        $currency = '';
        if ($cpCfg['m.common.country.hasCurrency']) {
            $currency = "
            {$formObj->getTextBoxRow($ln->gd('m.common.country.lbl.currency', 'Currency'), 'currency', $row['currency'])} 
            ";
        }

        $topLevelDomain = '';
        if ($cpCfg['m.common.country.hasTopLevelDomain']) {
            $sqlTopLevelDomain = $fn->getValueListSQL('topLevelDomain');
            
            $topLevelDomain = "
            {$formObj->getDDRowBySQL($ln->gd('m.common.country.lbl.topLevelDomain', 'Top Level Domain'), 'top_level_domain', 
                       $sqlTopLevelDomain, $row['top_level_domain'], $formObj->expOneFld)}
            ";
        }
        
        $fieldset1 = "
        {$formObj->getTextBoxRow($ln->gd('m.common.country.lbl.countryName', 'Country Name'), 'title', $row['title'])} 
        {$formObj->getTextBoxRow($ln->gd('m.common.country.lbl.email', 'Email'), 'admin_email', $row['admin_email'])} 
        {$formObj->getTextBoxRow($ln->gd('m.common.country.lbl.countryCode', 'Country Code'), 'country_code', $row['country_code'])} 
        {$iddCode}
        {$currency}
        {$topLevelDomain}
        {$formObj->getYesNoRRow($ln->gd('m.common.country.lbl.isthisDefaultCountry', 'Is this Default Country'), 'default_country', $row['default_country'] )} 
        {$formObj->getDDRowByArr($ln->gd('m.common.country.lbl.defaultLanguage', 'Default Language'), 'default_language', $cpCfg['cp.availableLanguages'], $row['default_language'], $exp)}
        {$formObj->getYesNoRRow($ln->gd('m.common.country.lbl.published', 'Published'), 'published', $row['published'] )} 
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.common.country.lbl.countryDetails', 'Country Details'), $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');

        $text ="
        {$displayLinkData->getLinkPortalMain("common_country", "common_languageLink", $ln->gd('m.common.country.link.languageLinked', 'Language Linked'), $row)}
        ";
        
        return $text;
    }
}