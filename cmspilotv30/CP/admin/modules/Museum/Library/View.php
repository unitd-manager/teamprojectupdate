<?
class CP_Admin_Modules_Museum_Library_View extends CP_Common_Modules_Museum_Library_View
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['accession_number'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getGoToDetailText($rowCounter, $row['author'])}
            {$listObj->getListDataCell($row['call_no'])}
            {$listObj->getListDataCell($row['publisher'])}
            {$listObj->getListDataCell($row['published_date'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListPublishedImage($row['published'], $row['library_id'])}
            {$listObj->getListDataCell($row['library_id'], 'center')}
            {$listObj->getListRowEnd($row['library_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Accession Number', 'l.accession_number')}
        {$listObj->getListHeaderCell('Title', 'l.title')}
        {$listObj->getListHeaderCell('Author', 'l.author')}
        {$listObj->getListHeaderCell('Call#', 'l.call_no')}
        {$listObj->getListHeaderCell('Publisher', 'l.publisher')}
        {$listObj->getListHeaderCell('Published Date', 'l.published_date')}
        {$listObj->getListHeaderCell('Status', 'l.status')}
        {$listObj->getListHeaderCell('Published', 'l.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'l.library_id', 'headerCenter')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];
/*
        $sqlItemType  = $fn->getValueListSQL('libraryItemType');
        $sqlLanguage  = $fn->getValueListSQL('libraryLanguage');
        $sqlStatus    = $fn->getValueListSQL('libraryStatus');
        $expVl = array('sqlType' => 'OneField');*/

        $modVl = getCPModuleObj('core_valuelist');
        $expVl = array('sqlType' => 'TwoFields');
        $sqlItemType = $modVl->model->getValuelistSQL('libraryItemType', array('useEngValueAsKey' => 1 ));
        $sqlLanguage = $modVl->model->getValuelistSQL('libraryLanguage', array('useEngValueAsKey' => 1 ));
        $sqlStatus = $modVl->model->getValuelistSQL('libraryStatus', array('useEngValueAsKey' => 1 ));

        $latest = '';
        if ($cpCfg['m.museum.library.showLatest'] == 1) {
            $latest = $formObj->getYesNoRRow("Latest", "latest", $row['latest']);
        }

        $metaData = '';
        if ($cpCfg['m.museum.library.showMetaData'] == 1) {
            $metaData .= $formObj->getMetaData($row);
        }

        $fielset1 = "
        {$formObj->getTBRow('OPAC Refrernce', 'opac_ref', $row['opac_ref'], array('isEditable' => false))}
        {$formObj->getTBRow('Accession Number', 'accession_number', $row['accession_number'])}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getTBRow('Other Title', 'added_title', $row['added_title'])}
        {$formObj->getTBRow('Added Entry: Corporate Name', 'added_name', $row['added_name'])}
        {$formObj->getTBRow('Author', 'author', $row['author'])}
        {$formObj->getTBRow('Added Entry: Author / Editor', 'added_author', $row['added_author'])}
        {$formObj->getTBRow('Local Call Number', 'call_no', $row['call_no'])}
        {$formObj->getTBRow('Additional Copies Available', 'additional_copies', $row['additional_copies'])}
        {$formObj->getTBRow('Edition', 'edition', $row['edition'])}
        {$formObj->getTBRow('ISBN', 'isbn', $row['isbn'])}
        {$formObj->getTBRow('ISSN', 'issn', $row['issn'])}
        {$formObj->getDDRowBySQL('Language', 'language', $sqlLanguage, $row['language'], $expVl)}
        {$formObj->getTARow('Notes', 'note', $row['note'])}
        {$formObj->getDDRowBySQL('Item Type', 'item_type', $sqlItemType, $row['item_type'], $expVl)}
        {$formObj->getTBRow('Subject : People', 'people', $row['people'])}
        {$formObj->getTARow('Physical Description', 'physical_description', $row['physical_description'])}
        {$formObj->getTBRow('Keyword / Index term', 'search_terms', $row['search_terms'])}
        {$formObj->getTBRow('Series Title', 'series', $row['series'])}
        {$formObj->getTBRow('Other Series Title', 'added_series_title', $row['added_series_title'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getTARow('Subjects: Tropical / Geographical', 'subjects', $row['subjects'])}
        {$formObj->getTARow('Summary', 'summary', $row['summary'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$latest}
        ";

        $fieldset2 = "
        {$formObj->getDateRow('Published Date', 'published_date', $row['published_date'])}
        {$formObj->getTBRow('Published Place', 'published_place', $row['published_place'])}
        {$formObj->getTBRow('Publisher', 'publisher', $row['publisher'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('OPAC Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Publication Details', $fieldset2)}
        {$metaData}
        {$formObj->getCreationModificationText($row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'museum_library', 'picture', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $special_search = $fn->getReqParam('special_search');
        $itemType = $fn->getReqParam('item_type');
        $language  = $fn->getReqParam('language');
        $status    = $fn->getReqParam('status');

        $sqlItemType    = $fn->getValueListSQL('libraryItemType');
        $sqlLanguage    = $fn->getValueListSQL('libraryLanguage');
        $sqlStatus      = $fn->getValueListSQL('libraryStatus');

        $text = "
        <td>
            <select name='item_type'>
                <option value=''>Item Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlItemType, $itemType)}
            </select>
        </td>
        <td>
            <select name='language'>
                <option value=''>Language</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlLanguage, $language)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.museum.library.btnPosArr'], $special_search)}
            </select>
        </td>
        ";

        return $text;
    }
}