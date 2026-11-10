<?
class CP_Admin_Modules_ELearn_Book_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $fieldsArray = array();

    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['color'])}
            {$listObj->getListDataCell($row['book_no'])}
            {$listObj->getListDataCell($row['code'])}
            {$listObj->getListDataCell($row['book_id'], 'center')}
            {$listObj->getListRowEnd($row['book_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'a.title')}
        {$listObj->getListHeaderCell('Color', 'a.color')}
        {$listObj->getListHeaderCell('Book No', 'a.book_no')}
        {$listObj->getListHeaderCell('Internal Code', 'a.code')}
        {$listObj->getListHeaderCell('ID', 'a.book_id' , 'headerCenter')}
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
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];
        $sqlColor = $fn->getValueListSQL('color');
        $exp = array('sqlType' => 'OneField');
        $sizeArr = array('Normal', 'Small', 'Large');
        
        $fielset1 = "
        {$formObj->getTBRow('Code', 'code', $row['code'], 0)}
        {$formObj->getTBRow('Book No', 'book_no', $row['book_no'], 0)}
        {$formObj->getTBRow('Title', 'title', $row['title'], 0)}
        {$formObj->getDDRowBySQL('Color', 'color', $sqlColor, $row['color'], $exp)}
        {$formObj->getDDRowByArr('Font Size', 'font_size', $sizeArr, $row['font_size'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Book Details', $fielset1)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay("Cover Picture", "ek_book", "picture", $row)}
        {$displayLinkData->getLinkPortalMain("elearn_book", "elearn_klassLink", "Classes Linked", $row)}
        ";
        return $text;
    }

    //==================================================================//
    //==================================================================//

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');

        $color = $fn->getReqParam('color');
        $sqlColor = $fn->getValueListSQL('color', 'value');

        $text = "
        <td class='fieldValue'>
            <select name='color'>
                <option value=''>Color</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlColor, $color)}
            </select>
        </td>
        ";
        
        
        return $text;
    }
}