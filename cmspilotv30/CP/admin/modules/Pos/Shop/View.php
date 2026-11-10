<?
class CP_Admin_Modules_Pos_Shop_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['telephone'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['shop_id'], 'center')}
            {$listObj->getListRowEnd($row['shop_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 's.code')}
        {$listObj->getListHeaderCell('Name', 's.title')}
        {$listObj->getListHeaderCell('Telephone', 's.telephone')}
        {$listObj->getListHeaderCell('Status', 's.status')}
        {$listObj->getListHeaderCell('ID', 's.shop_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Shop Name', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();
        $sqlCompanyCode = getCPModuleObj('pos_companyProfile')->model->getCompanyCodeSQL();

        $fielset1 = "
        {$formObj->getTBRow('Code', 'code', $row['code'])}
        {$formObj->getTBRow('Name', 'title', $row['title'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        {$formObj->getTARow('Address', 'address', $row['address'])}
        {$formObj->getTBRow('Telephone', 'telephone', $row['telephone'])}
        {$formObj->getTARow('Remarks & Notes', 'notes', $row['notes'])}
        {$formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], array('sqlType' => 'OneField'))}
        {$formObj->getTBRow('Currency Sign', 'currency_sign', $row['currency_sign'])}
        {$formObj->getDDRowBySQL('Company Code', 'company_code', $sqlCompanyCode, $row['company_code'], array('sqlType' => 'OneField'))}
        {$formObj->getDDRowByVL('Status', 'status', 'shopStatus', $row['status'])}
        {$formObj->getYesNoRRow('Print Company Logo', 'print_company_logo', $row['print_company_logo'])}
        {$formObj->getYesNoRRow('Print Shop Logo', 'print_shop_logo', $row['print_shop_logo'])}
        {$formObj->getYesNoRRow('Print Shop Address and Telephone by shop', 'print_shop_add_tele', $row['print_shop_add_tele'])}
        {$formObj->getYesNoRRow('Print Invoice Remark', 'print_invoice_remark', $row['print_invoice_remark'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Product Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
                
        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'pos_shop', 'picture', $row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');
                
        $text = "
        ";

        
        return $text;
    }
}