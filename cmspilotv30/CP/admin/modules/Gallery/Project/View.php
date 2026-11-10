<?
class CP_Admin_Modules_Gallery_Project_View extends CP_Common_Modules_Gallery_Project_View
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
    		{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListSortOrderField($row, 'project_id')}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['location'])}
            {$listObj->getListDataCell($row['project_year'])}
            {$listObj->getListPublishedImage($row['published'], $row['project_id'])}
            {$listObj->getListDataCell($row['project_id'], 'center')}
            {$listObj->getListRowEnd($row['project_id'])}
			";

        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$listObj->getListSortOrderImage('p')}
        {$listObj->getListHeaderCell('Category', 'category_title')}
        {$listObj->getListHeaderCell('Sub Category', 'sub_category_title')}
        {$listObj->getListHeaderCell('Location', 'p.location')}
        {$listObj->getListHeaderCell('Year', 'p.project_year')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'p.project_id', 'headerCenter')}
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
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $modCat = getCPModuleObj('webBasic_category');

        $expCategory = array('detailValue' => $row['category_title']);
        $sqlCategory = getCPModelObj('webBasic_category')->getCategorySQLByType('Project');

        $expSubCategory = array('detailValue' => $row['sub_category_title']);
        $sqlSubCategory = getCPModelObj('webBasic_subCategory')->getSubCategorySQL($row['category_id']);

        $sqlYear = $fn->getValueListSQL('projectYear');
        $expYear = array('sqlType' => 'OneField');

        $fielset1  = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
		";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Year', 'project_year', $sqlYear, $row['project_year'], $expYear)}
        {$formObj->getTBRow('Location', 'location', $row['location'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getYesNoRRow('Latest', 'latest', $row['latest'])}
        ";

        $fieldset3 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('Description', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
	function getRightPanel($row) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');

        $relPics = '';
        if ($cpCfg['m.gallery.project.hasRelatedPics']){
            $relPics = $media->getRightPanelMediaDisplay('Related Picture', 'gallery_project', 'relatedPicture', $row);
        }

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'gallery_project', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'gallery_project', 'attachment', $row)}
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

        $category_id = $fn->getReqParam('category_id');
        $sub_category_id = $fn->getReqParam('sub_category_id');

        $modCategory = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCategory->model->getCategorySQLByType('Project');

        $subCatOptions = '';
        if ($category_id != '') {
            $modCat = getCPModuleObj('webBasic_subCategory');
            $SQLSubCat = $modCat->model->getSubCategorySQL($category_id);
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSubCat, $sub_category_id);
        }

        $text = "
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCategory, $category_id)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='sub_category_id'>
                <option value=''>Sub Category</option>
                {$subCatOptions}
            </select>
        </td>
        ";

        return $text;
    }
}