<?
class CP_Admin_Modules_Ek_BookChapter_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['book_title'])}
            {$listObj->getListPublishedImage($row['published'], $row['book_chapter_id'])}
            {$listObj->getListDataCell($row['book_chapter_id'], 'center')}
            {$listObj->getListRowEnd($row['book_chapter_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'bc.title')}
        {$listObj->getListHeaderCell('Book', 'b.title')}
        {$listObj->getListHeaderCell('Published', 'bc.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'bc.book_chapter_id' , 'headerCenter')}
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
       
        $modBook = getCPModuleObj('ek_book');
        $sqlBook = $modBook->model->getBookSQL();
        $expBook = array('detailValue' => $row['book_title']);

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Book', 'book_id', $sqlBook, $row['book_id'], $expBook)}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $fielset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $row['description'])}
        ";
		
        $text = "
        {$formObj->getFieldSetWrapped('General Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description', $fielset2)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'ek_bookChapter', 'picture', $row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $book_id  = $fn->getReqParam('book_id');

        $modBook = getCPModuleObj('ek_book');
        $sqlBook = $modBook->model->getBookSQL();

        $text = "
        <td class='fieldValue'>
            <select name='book_id'>
                <option value=''>Book</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBook, $book_id)}
            </select>
        </td>
        ";       
        
        return $text;
    }
}