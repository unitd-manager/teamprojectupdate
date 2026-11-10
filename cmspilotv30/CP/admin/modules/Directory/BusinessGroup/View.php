<?
class CP_Admin_Modules_Directory_BusinessGroup_View extends CP_Common_Modules_Directory_BusinessGroup_View
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['business_count'], 'center')}
            {$listObj->getListDataCell($row['country_title'])}
            {$listObj->getListPublishedImage($row['published'], $row['business_group_id'])}
            {$listObj->getListDataCell($row['has_logo'], 'center')}
            {$listObj->getListDataCell($row['description_list'])}
            {$listObj->getListDataCell($row['website'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['social_medias'])}
            {$listObj->getListDataCell($row['dgd_modified_by'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListDateCell($row['modification_date'])}
            {$listObj->getListDataCell($row['business_group_id'], 'center')}
            {$listObj->getListRowEnd($row['business_group_id'])}
            ";
            $rowCounter++;
        }

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.businessGroup'), 'bg.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.businessCount'), 'business_count', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.country'), 'co.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.published'), 'bg.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.logo'), 'has_logo', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.description'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.website'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.category'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.subCategory'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.socialMedia'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.orName'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.creation'), 'b.creation_date')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.modification'), 'b.modification_date')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessGroup.lbl.id'), 'bg.business_group_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.businessGroup.lbl.businessGroup'), 'title')}
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
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $expCategory = array('detailValue' => $row['category_title']);
        $sqlCategory = getCPModelObj('webBasic_category')->getCategorySQLByType('Directory');

        $expSubCategoryCond = array(
            'condn' => "category_id = '{$row['category_id']}'"
        );

        $expSubCategory = array('detailValue' => $row['sub_category_title']);
        $sqlSubCategory = '';
        if ($row['category_id'] != '') {
            $sqlSubCategory = $fn->getDDSql('webBasic_subCategory', $expSubCategoryCond);
        }

        $expWebsite = array(
            'urliseContent' => true
           ,'fldPrefix' => 'http://www.'
        );

        $expCountry = array('detailValue' => $row['country_title']);
        $sqlCountry = $fn->getDDSql('common_country');

        $urlGoogle = "https://www.google.com/search?q="
                   . urlencode($row['title']);
        $findInGoogle = "
        <a href='{$urlGoogle}' target='_blank' title='Find this in google'>
            <img src='images/google.png'>
        </a>
        " ;
        $expBusGroup = array('notesRight' => $findInGoogle);
        
        $business_type = $row['business_type'] == '' ? 'Retail' : $row['business_type'];
        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('m.directory.businessGroup.lbl.businessGroup'), 'title', $row['title'], $expBusGroup)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.businessGroup.lbl.published'), 'published', $row['published'] )}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.businessGroup.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}

        {$formObj->getDDRowBySQL($ln->gd('m.directory.businessGroup.lbl.category'), 'category_id', $sqlCategory,
                                 $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.businessGroup.lbl.subCategory'), 'sub_category_id', $sqlSubCategory,
                                 $row['sub_category_id'], $expSubCategory)}
        {$formObj->getDDRowByVL($ln->gd('m.directory.businessGroup.lbl.businessType'), 'business_type', 'businessType', $business_type)}
        {$formObj->getTBRow($ln->gd('m.directory.businessGroup.lbl.website'), 'website', $row['website'], $expWebsite)}
		";

        $urlWiki = "http://en.wikipedia.org/w/index.php?search="
                   . urlencode($row['title']);
        $findInWiki = "
        <a href='{$urlWiki}' target='_blank' title='Find this in Wiki'>
            <img src='images/wiki.png'>
        </a>
        " ;
        $expDesc = array('notesRight' => $findInWiki);
        
        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('m.directory.businessGroup.lbl.name'), 'contact_name', $row['contact_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessGroup.lbl.position'), 'contact_position', $row['contact_position'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessGroup.lbl.phone'), 'contact_phone', $row['contact_phone'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessGroup.lbl.email'), 'contact_email', $row['contact_email'])}
        ";
        
        $fieldset3 = "
        {$formObj->getTARow($ln->gd('m.directory.businessGroup.lbl.description'), 'description', $row['description'], $expDesc)}
        {$formObj->getTARow($ln->gd('m.directory.businessGroup.lbl.description - Chinese'), 'description_chi', $row['description_chi'])}
        {$formObj->getTARow($ln->gd('m.directory.businessGroup.lbl.tags'), 'tags', $ln->gfv($row, 'tags', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.businessGroup.lbl.mainDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.businessGroup.lbl.contactDetails'), $fieldset2)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.businessGroup.lbl.moreDetails'), $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

	function getRightPanel($row) {
        $dld = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');

        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.businessGroup.link.logo'), 'directory_businessGroup', 'logo', $row)}
        {$dld->getLinkPortalMain('directory_businessGroup', 'directory_businessLink',$ln->gd('m.directory.businessGroup.link.businesses'), $row)}
        {$dld->getLinkPortalMain('directory_businessGroup', 'directory_socialMediaLink',$ln->gd('m.directory.businessGroup.link.socialMedia'), $row)}
        {$dld->getLinkPortalMain('directory_businessGroup', 'directory_paymentLink',$ln->gd('m.directory.businessGroup.link.payments'), $row)}
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