<?
class CP_Admin_Modules_Museum_Facility_View extends CP_Common_Modules_Museum_Facility_View
{
    /**
     *
     */
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";
        $tagsGroupHeader = '';
        $tagsGroupValue = '';

        foreach ($dataArray as $row){
        	
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
    		{$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['section_title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['date'])}
            {$listObj->getListDataCell($row['facility_id'], 'center')}
    		{$listObj->getListPublishedImage($row['published'] , $row['facility_id'])}
    		{$listObj->getListRowEnd($row['facility_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell('Facility', 'f.title')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.section', 'Section'), 'section_title')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.category', 'Category'), 'category_title')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.subCategory', 'Sub Category'), 'sub_category_title')}
    	{$listObj->getListHeaderCell('Date', 'f.date')}
    	{$listObj->getListHeaderCell('ID', 'f.facility_id', 'headerCenter')}
    	{$listObj->getListHeaderCell('Published', 'f.published', 'headerCenter')}
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
        {$formObj->getTBRow('Facility', 'title')}
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
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];               
        $expSection     = array('detailValue' => $row['section_title']);
        $expCategory    = array('detailValue' => $row['category_title']);
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $modSec = getCPModuleObj('webBasic_section');
        $modCat = getCPModuleObj('webBasic_category');
        $modSubCat = getCPModuleObj('webBasic_subCategory');

        $sqlSection = $modSec->model->getSectionSQL();
        $sqlCategory = '';
        if ($row['section_id'] != ''){
            $sqlCategory = $modCat->model->getCategorySQLBySection($row['section_id']);
        }

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }
        $fieldset1  = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL($ln->gd('m.webBasic.content.lbl.section', 'Section'), 'section_id', $sqlSection, $row['section_id'], $expSection)}
        {$formObj->getDDRowBySQL($ln->gd('m.webBasic.content.lbl.category', 'Category'), 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL($ln->gd('m.webBasic.content.lbl.subCategory', 'Sub Category'), 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
       
        {$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short', '0'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
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
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'museum_facility', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('museum_facility', 'museum_facilityAvailabilityLink', 'Booking Availability', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'museum_facility', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $text = "
        ";

        return $text;
    }
    
}