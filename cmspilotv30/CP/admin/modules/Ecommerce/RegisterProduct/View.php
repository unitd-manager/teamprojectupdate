<?
class CP_Admin_Modules_Ecommerce_RegisterProduct_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $rowCounter = 0;
        $fnModCountry = includeCPClass('ModuleFns', 'common_geoCountry');

        $country     = '';
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['product_serial'])}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListDataCell($row['register_product_id'])}
            {$listObj->getListRowEnd($row['register_product_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'rp.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'rp.last_name')}
        {$listObj->getListHeaderCell('Email', 'rp.email')}
        {$listObj->getListHeaderCell('Product Name', 'product_name')}
        {$listObj->getListHeaderCell('Product Serial', 'rp.product_serial')}
        {$listObj->getListHeaderCell('Creation Date', 'rp.creation_date')}
        {$listObj->getListHeaderCell('ID', 'rp.register_product_id')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getPrint($result){
        return $this->getList($result);
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('First Name', 'first_name')}
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
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $expNoEdit = array('isEditable' => 0);
        $sqlStatus = $fn->getValueListSQL('enquiryStatus');
        $expVl     = array('sqlType' => 'OneField');

        $country = '';
        
                
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $fieldset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'], $expNoEdit)}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'], $expNoEdit)}
        {$formObj->getTBRow('Email', 'email', $row['email'], $expNoEdit)}
        {$fnModCountry->getCountryDropDown($formObj->mode, $row)}
        {$formObj->getTBRow('Product Name', 'product_name', $row['product_name'], $expNoEdit)}
        {$formObj->getTBRow('Product Serial', 'product_serial', $row['product_serial'], $expNoEdit)}
        {$formObj->getTARow('Comments', 'comments', $row['comments'], $expNoEdit)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Register Product Details', $fieldset1)}
        {$formObj->getCreationModificationText2($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        
        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $creation_date1 = $fn->getReqParam('creation_date1');
        $creation_date2 = $fn->getReqParam('creation_date2');
        
        
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
        $yearEnd = date('Y') + 10;
        $text = "
        <td class='dateRange'>
            Creation Date:
            <input type='text' allowEdit='1' name='creation_date1' class='fld_date' 
                   id='fld_creation_date1' value='{$creation_date1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='creation_date2' class='fld_date' 
                   id='fld_creation_date2' value='{$creation_date2}' yearEnd='{$yearEnd}' />
        </td>
        {$fnModCountry->getCountryDropDown('search')}
        ";

        
        return $text;
    }
}