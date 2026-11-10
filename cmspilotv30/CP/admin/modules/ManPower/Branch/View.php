<?
class CP_Admin_Modules_ManPower_Branch_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
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
            {$listObj->getListDataCell($row['currency'])}            
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListRowEnd($row['branch_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'i.title')}
        {$listObj->getListHeaderCell('Currency', 's.first_name')}        
        {$listObj->getListHeaderCell('Creation Date', 'i.creation_date')}
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
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $text = '';

        $sqlCurrency = $fn->getValueListSQL('currency');
        $exp = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], $exp)}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Branch Details', $fieldset1)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        
        $record_id = $fn->getIssetParam($row, 'branch_id');

        $text = "
        ";

        return $text;
    }

    //==================================================================//
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');

        $text = "
        ";

        
        return $text;
    }
}