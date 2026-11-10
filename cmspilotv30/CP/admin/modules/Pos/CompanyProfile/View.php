<?
class CP_Admin_Modules_Pos_CompanyProfile_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['company_code'])}
            {$listObj->getListDataCell($row['telephone'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['company_profile_id'], 'center')}
            {$listObj->getListRowEnd($row['company_profile_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'cp.title')}
        {$listObj->getListHeaderCell('Company Code', 'cp.company_code')}
        {$listObj->getListHeaderCell('Telephone', 'cp.telephone')}
        {$listObj->getListHeaderCell('Email', 'cp.email')}
        {$listObj->getListHeaderCell('ID', 'cp.company_profile_id', 'headerCenter')}
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
        {$formObj->getTBRow('Company Name', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        
        $fielset1 = "
        {$formObj->getTBRow('Name', 'title', $row['title'])}
        {$formObj->getTARow('Address', 'address', $row['address'])}
        {$formObj->getTBRow('Telephone', 'telephone', $row['telephone'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Company Code', 'company_code', $row['company_code'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Company Profile Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
                
        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'pos_companyProfile', 'picture', $row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
                
        $text = "
        ";

        
        return $text;
    }
}