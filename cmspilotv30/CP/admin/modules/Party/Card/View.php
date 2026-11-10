<?

class CP_Admin_Modules_Party_Card_View extends CP_Common_Lib_ModuleViewAbstract {

    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row) {
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['card_type'])}
            {$listObj->getListDataCell($row['card_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['card_id'])}
            {$listObj->getListRowEnd($row['card_id'])}
            ";

            $rowCounter++;
        }

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'c.title')}
        {$listObj->getListHeaderCell('Category', 'ca.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('Card Type', 'c.card_type')}
        {$listObj->getListHeaderCell('ID', 'c.card_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
        {$listObj->getListHeaderCell('Image', '', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew() {
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Party', 'Select Card');
        $expCategory = array('detailValue' => $row['category_title']);

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
        {$formObj->getDDRowByArr('Card Type', 'card_type', $cpCfg['m.party.card.cardTypes'], $row['card_type'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $media = Zend_Registry::get('media');

        $text = "
        {$media->getRightPanelMediaDisplay('Thumb View Image', 'party_card', 'thumbImage', $row)}
        {$media->getRightPanelMediaDisplay('Hover Image', 'party_card', 'hoverImage', $row)}
        {$media->getRightPanelMediaDisplay('RSVP Background Image', 'party_card', 'rsvpBgImage', $row)}
        {$media->getRightPanelMediaDisplay('Preview Background Image', 'party_card', 'previewBgImage', $row)}
        {$media->getRightPanelMediaDisplay('Thankyou Card Image', 'party_card', 'thankyouCardImage', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $card_type = $fn->getReqParam('card_type');

        $arr = '';
        $text = "
        <td class='fieldValue'>
            <select name='card_type'>
                <option value=''>Card Type</option>
                {$cpUtil->getDropDown1($cpCfg['m.party.card.cardTypes'], $card_type)}
            </select>
        </td>
        ";

        return $text;
    }

}