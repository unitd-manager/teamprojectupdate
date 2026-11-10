<?
class CP_Admin_Modules_ELearn_BookPage_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['english'])}
            {$listObj->getListDataCell($row['book_title'])}
            {$listObj->getListDataCell($row['color'])}
            {$listObj->getListDataCell($row['page_no'])}
            {$listObj->getListDataCell($row['book_page_id'], 'center')}
            {$listObj->getListRowEnd($row['book_page_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('English Text', 'a.english')}
        {$listObj->getListHeaderCell('Book/Series','b.title')}
        {$listObj->getListHeaderCell('Color','b.color')}
        {$listObj->getListHeaderCell('Page No','a.page_no')}
        {$listObj->getListHeaderCell('ID', 'a.book_page_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $sqlColor = "
        SELECT value
              ,value
        FROM valuelist
        WHERE key_text = 'color'
        ";
        $sqlBook = "
        SELECT book_id
              ,title
        FROM book 
        ORDER BY title
        ";

        $fieldset = "
        {$formObj->getTARow('English', 'english')}
        {$formObj->getDDRowBySQL('Color', 'color', $sqlColor)}
        {$formObj->getDDRowBySQL('Book', 'book_id', $sqlBook)}
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
        $linksArray = Zend_Registry::get('linksArray');

        $formObj->mode = $tv['action'];

        $sqlColor = $fn->getValueListSQL('color');
        $exp = array('sqlType' => 'OneField');

        $sqlBook = "
        SELECT book_id
              ,title
        FROM book 
        ORDER BY title
        ";
        $expBook = array('detailValue' => $row['book_title']);

        $fielset1 = "
        {$formObj->getDDRowBySQL('Color', 'color', $sqlColor, $row['color'], $exp)}
        {$formObj->getDDRowBySQL('Book', 'book_id', $sqlBook, $row['book_id'], $expBook)}
        {$formObj->getTBRow('Page No', 'page_no', $row['page_no'])}
        {$formObj->getTARow('English', 'english', $row['english'])}
        {$formObj->getTARow('Simplified Chinese', 'chinese', $row['chinese'])}
        {$formObj->getTARow('Pinyin', 'pinyin', $row['pinyin'])}
        {$formObj->getTARow('Traditional Chinese', 'chinese_traditional', $row['chinese_traditional'])}
        <!-- {$formObj->getTARow('Vocabulary', 'vocabulary', $row['vocabulary'])} -->
		";

        $text = "
        {$formObj->getFieldSetWrapped('Book Page Details', $fielset1)}
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
        {$media->getRightPanelMediaDisplay("Picture", "ek_bookPage", "picture", $row)}
        {$media->getRightPanelMediaDisplay("Audio", "ek_bookPage", "audio", $row)}
        {$displayLinkData->getLinkPortalMain('elearn_bookPage', 'elearn_pageQuestionLink', 'Questions Linked', $row)}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $book_id = $fn->getReqParam('book_id');
        $color = $fn->getReqParam('color');

        $sqlColor = $fn->getValueListSQL('color', 'value');
        
        $sqlBook = "
        SELECT book_id
              ,title
        FROM book 
        ORDER BY title
        ";

        $text = "
        <td class='fieldValue'>
            <select name='color'>
                <option value=''>Color</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlColor, $color)}
            </select>
        </td>

        <td>
            <select name='book_id'>
                <option value=''>Book</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBook, $book_id)}
            </select>
        </td>
        ";
        
        
        return $text;
    }
}