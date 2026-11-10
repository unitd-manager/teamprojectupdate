<?
class CP_Admin_Modules_Dms_Document_View extends CP_Common_Modules_Dms_Document_View
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $dataArray = $this->model->dataArray;

        $rows  = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $country = '';

            if ($cpCfg['m.dms.document.hasCountryId']){
                $country = $listObj->getListDataCell($row['country_name']);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListSortOrderField($row, 'document_id')}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDateCell($row['content_date'])}
            {$country}
            {$listObj->getListDataCell($row['document_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['document_id'])}
            {$listObj->getListRowEnd($row['document_id'])}
            ";
            $rowCounter++;
        }

        $country = '';

        if ($cpCfg['m.dms.document.hasCountryId']){
            $country = $listObj->getListHeaderCell('Country', 'country_name');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'd.title')}
        {$listObj->getListSortOrderImage('d')}
        {$listObj->getListHeaderCell('Category', 'category_title')}
        {$listObj->getListHeaderCell('Document Date', 'd.content_date')}
        {$country}
        {$listObj->getListHeaderCell('ID', 'd.document_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'd.published', 'headerCenter')}
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

    //==================================================================//
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formObj->mode = $tv['action'];

        $expCategory    = array('detailValue' => $row['category_title']);

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCat = $modCat->model->getCategorySQLByType('Document');


        $modCountry = getCPModuleObj('common_country');

        if ($cpCfg['m.dms.document.hasCountryId']){
            $country = $modCountry->fns->getCountryDropDown($formObj->mode, $row, 1);
        }

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCat, $row['category_id'], $expCategory)}
        {$formObj->getDateRow('Document Date', 'content_date', $row['content_date'])}
        {$country}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped('Document Details', $fieldset1)}
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

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'dms_document', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $special_search  = $fn->getReqParam('special_search');

        $catOptions    = '';
        $subCatOptions = '';

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCat = $modCat->model->getCategorySQLByType('Document');
        $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $sqlCat, $tv['category_id']);


        $country = '';
        if ($cpCfg['m.dms.document.hasCountryId']){
            $modCountry = getCPModuleObj('common_country');
            $country = $modCountry->fns->getCountryDropDown('search', '', 1);
        }

        $text = "
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$catOptions}
            </select>
        </td>
        {$country}
        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['cp.specialSearchArr'], $special_search)}
            </select>
        </td>
        ";


        return $text;
    }
}