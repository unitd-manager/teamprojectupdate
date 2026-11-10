<?
class CP_Admin_Modules_Directory_Menu_View extends CP_Common_Modules_Directory_Menu_View
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
            {$listObj->getListDataCell($row['business_name'])}
            {$listObj->getListDataCell($row['menu_category_title'])}
            {$listObj->getListDataCell($row['menu_sub_category_title'])}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListDataCell($row['menu_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['menu_id'])}
            {$listObj->getListRowEnd($row['menu_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'm.title')}
        {$listObj->getListHeaderCell('Business', 'b.business_name')}
        {$listObj->getListHeaderCell('Category', 'mc.title')}
        {$listObj->getListHeaderCell('Sub Category', 'msc.title')}
        {$listObj->getListHeaderCell('Price', 'm.price')}
        {$listObj->getListHeaderCell('ID', 'm.product_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'm.published', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $expComp  = array(
             'autoSgstModule' => 'directory_business'
            ,'autoSgstSrchFld' => 'business_name'
            ,'autoSgstActualFld' => 'business_id'
            ,'autoSgstActualFldVal' => ''
            ,'autoSgstCallBack' => ''
        );

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        {$formObj->getTBRow('Business', 'business_name', '', $expComp)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $expNoEdit = array('isEditable' => 0);

        $sqlCategory = '';
        if ($row['business_id'] != ''){
            $sqlCategory = "
            SELECT menu_category_id
                  ,title
            FROM menu_category
            WHERE business_id = {$row['business_id']}
            ORDER BY title
            ";
        }
        $expCategory = array('detailValue' => $row['menu_category_title']);

        $sqlSubCategory = '';
        if ($row['menu_category_id'] != ''){
            $sqlSubCategory = "
            SELECT menu_sub_category_id
                  ,title
            FROM menu_sub_category
            WHERE menu_category_id = {$row['menu_category_id']}
            ORDER BY title
            ";
        }
        $expSubCategory = array('detailValue' => $row['menu_sub_category_title']);

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTBRow('Busienss', 'business_id', $row['business_name'], $expNoEdit)}
        {$formObj->getDDRowBySQL('Category', 'menu_category_id', $sqlCategory, $row['menu_category_id'], $expCategory)}
        {$formObj->getDDRowBySQL('Sub Category', 'menu_sub_category_id', $sqlSubCategory, $row['menu_sub_category_id'], $expSubCategory)}
        {$formObj->getTBRow('Price', 'price', $row['price'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'directory_menu', 'picture', $row)}
        ";
        return $text;
    }

    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');

        $special_search = $fn->getReqParam('special_search');
        $business_id = $fn->getReqParam('business_id');
        $business_name = $fn->getReqParam('business_name');

        $spSearchArr = array(
            'Flagged'
           ,'Not-Flagged'
           ,'Published'
           ,'Not-Published'
           ,'Abusive'
           ,'Not-Abusive'
           ,'Verified'
           ,'Not-Verified'
        );

        $expComp  = array(
             'autoSgstModule' => 'directory_business'
            ,'autoSgstSrchFld' => 'business_name'
            ,'autoSgstActualFld' => 'business_id'
            ,'autoSgstActualFldVal' => $business_id
            ,'autoSgstCallBack' => 'Actions.submitSearchTop'
        );

        $text = "
        {$formObj->getTBRow($ln->gd('m.directory.comment.lbl.business'), 'business_name', $business_name, $expComp)}
        <div>
            <select name='special_search'>
                <option value=''>{$ln->gd('m.directory.comment.lbl.specialSearch')}</option>
                {$cpUtil->getDropDown1($spSearchArr, $special_search)}
            </select>
        </div>
        ";

        return $text;
    }
}