<?
class CP_Admin_Modules_Pos_SubCategory_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $rows           = "";
        $categoryFilter = "";
		$textFilter     = "";
		
        $rowCounter = 0;

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

        $expCategory = array('displayText' => $row['category_title']);
        $categoryText = $fn->getRecordDetailLink('pos_category', 'record_id',
                            $row['category_id'], $expCategory);
            
            if ($cpCfg['category'] == 0) {
                $category = $listObj->getListDataCell($row['category_title']);
            } else {
                $category = $listObj->getListDataCell($categoryText);
            }                            

            $catLink = "
            <a href = {'$categoryText'}>Title</a>
            ";

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$category}
            {$listObj->getListDataCell($row['description'])}
            {$listObj->getListDataCell($row['sub_category_id'], "center")}
            {$listObj->getListRowEnd($row['sub_category_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'sc.code')}
        {$listObj->getListHeaderCell('Title', 'sc.title')}
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Description', 'sc.description')}
        {$listObj->getListHeaderCell('ID', 'sc.sub_category_id', 'headerCenter')}
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
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
       
        $sqlCategory = $fn->getDDSQL('pos_category');
        $expCategory = array('displayText' => $row['category_title']);

        $categoryText = $fn->getRecordDetailLink('pos_category', 'record_id',
                            $row['category_id'], $expCategory);
        $expCatDisp = array('detailValue' => $categoryText, 'hideFirstOption' => 1);

        if ($cpCfg['category'] == 0) {
            $category = $formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory,
                                 $row['category_title']);
        } else {
            $category = $formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory,
                                 $row['category_id'], $expCatDisp);
        }                            
        
        
        $fieldset1 = "
        {$formObj->getTBRow('Code', 'code', $ln->gfv($row, 'code', '0'))}
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$category}                                                                                                                               
        {$formObj->getTARow('Description', 'description', $ln->gfv($row, 'description', '0'))}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Sub Category Details', $fieldset1)}
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

        $text = "
        {$media->getRightPanelMediaDisplay('Sub Category Picture', 'pos_subCategory', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('pos_subCategory', 'pos_sizeLink', 'Size Linked', $row)}
        {$displayLinkData->getLinkPortalMain('pos_subCategory', 'pos_seasonLink', 'Season Linked', $row)}
        {$displayLinkData->getLinkPortalMain('pos_subCategory', 'pos_styleLink', 'Style Linked', $row)}
        {$displayLinkData->getLinkPortalMain('pos_subCategory', 'pos_colorLink', 'Color Linked', $row)}
        {$displayLinkData->getLinkPortalMain('pos_subCategory', 'pos_elementLink', 'Element Linked', $row)}
        {$displayLinkData->getLinkPortalMain('pos_subCategory', 'pos_brandLink', 'Brand Linked', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getSubCategoryByCategoryJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $category_id = $fn->getReqParam('category_id');

        $json = array();
        
        if ($category_id == ''){
            return json_encode($json);
        }

        $SQL = "
        SELECT sub_category_id
              ,title
        FROM sub_category 
        WHERE category_id = '{$category_id}'
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array('value' => $row['sub_category_id'], 'caption' => $row['title']);
        }
        
        return json_encode($json);
    }


    /**
     *
     */
    function getQuickSearch() {
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $special_search  = $fn->getReqParam('special_search');

        $SQLCat = $fn->getDDSQL('pos_category');

        $text = "
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id'])}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1(array('Flagged', 'Not-Flagged'), $special_search)}
            </select>
        </td>
        ";
        
        return $text;
    }


    function getBulkMoveToCategoryForm(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expCheck = array('isLabelOnLeft' => true);
        
        $count = $fn->getFlaggedRecordCount('pos_subCategory');
        $sqlCat = $fn->getDdSQL('pos_category');
        
        $formAction = "index.php?module=pos_subCategory&_spAction=bulkMoveToCategorySubmit&showHTML=0";
        $message = "
        <p>Please choose the category & click <b>Move Sub Categories</b> button.
        This will move all the tagged <b>{$count}</b> sub-categories from the previous categories to the new Category.</p>
        ";
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$message}
            <fieldset>
                {$formObj->getDDRowBySQL('Category', 'to_category_id', $sqlCat)}
            </fieldset>
        </form>
        ";
        return $text;
    }    

}