<?
class CP_Admin_Modules_Ecommerce_Country_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $rows  = "";

        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}                            
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}           
            {$listObj->getListDataCell($row['country_code'])}                       
            {$listObj->getListSortOrderField($row, 'country_id')}
            {$listObj->getListDataCell($row['currency_code'])}                      
            {$listObj->getListPublishedImage($row['published'], $row['country_id'])}
            {$listObj->getListDataCell($row['creation_date'])}                         
            {$listObj->getListRowEnd($row['country_id'])}                              
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}                                                  
        {$listObj->getListHeaderCell($ln->gd('m.ecommerce.header.country.lbl.countryName', 'Country Name'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.ecommerce.header.country.lbl.countryCode', 'Country Code'), 'c.country_code')}
        {$listObj->getListSortOrderImage('c')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.currency', 'Currency'), 'c.currency_code')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.published', 'Published'), 'c.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.creation', 'Creation'), 'c.creation_date', 'headerCenter')}
        {$listObj->getListHeaderEnd()}                                                       
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.ecommerce.country.lbl.countryName', 'Country Name'), 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset1 = "
        {$formObj->getTextBoxRow($ln->gd('m.ecommerce.country.lbl.countryName', 'Country Name'), 'title', $ln->gfv($row, 'title', '0'))} 
        {$formObj->getTextBoxRow($ln->gd('m.ecommerce.country.lbl.countryCode', 'Country Code'), 'country_code', $row['country_code'])} 
        {$formObj->getTextBoxRow($ln->gd('m.ecommerce.country.lbl.currencyCode', 'Currency Code'), 'currency_code', $row['currency_code'])} 
        {$formObj->getTextBoxRow($ln->gd('m.ecommerce.country.lbl.adminEmail', 'Admin Email'), 'admin_email', $row['admin_email'])} 
        {$formObj->getYesNoRRow($ln->gd('cp.lbl.published', 'Published'), 'published', $row['published'] )} 
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.ecommerce.country.lbl.countryDetails', 'Country Details'), $fieldset1)}
        ";

        return $text;
    }
}