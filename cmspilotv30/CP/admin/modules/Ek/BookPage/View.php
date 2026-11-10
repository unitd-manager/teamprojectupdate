<?
class CP_Admin_Modules_Ek_BookPage_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['book_chapter_title'])}
            {$listObj->getListPublishedImage($row['published'], $row['book_page_id'])}
            {$listObj->getListDataCell($row['book_page_id'], 'center')}
            {$listObj->getListRowEnd($row['book_page_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'bp.title')}
        {$listObj->getListHeaderCell('Book', 'b.title')}
        {$listObj->getListHeaderCell('Book Chapter', 'bp.title')}
        {$listObj->getListHeaderCell('Published', 'bp.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'bp.book_page_id' , 'headerCenter')}
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
        
        $sqlBookChapter = '';
        if ($row['book_id'] != ''){
            $modBookChapter = getCPModuleObj('ek_bookChapter');
            $sqlBookChapter = $modBookChapter->model->getBookChapterSQL($row['book_id']);
        }
        $expBookChapter = array('detailValue' => $row['book_chapter_title']);

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Book', 'book_id', $sqlBook, $row['book_id'], $expBook)}
        {$formObj->getDDRowBySQL('Book Chapter', 'book_chapter_id', $sqlBookChapter, $row['book_chapter_id'], $expBookChapter)}
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
        {$media->getRightPanelMediaDisplay('Picture', 'ek_bookPage', 'picture', $row)}
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

        $book_id            = $fn->getReqParam('book_id');
        $book_chapter_id    = $fn->getReqParam('book_chapter_id');

        $modBook = getCPModuleObj('ek_book');
        $sqlBook = $modBook->model->getBookSQL();

        $bookChapter = '';
        if ($book_id != '') {
            $modBookChapter = getCPModuleObj('ek_bookChapter');
            $sqlBookChapter = $modBookChapter->model->getBookChapterSQL($book_id);
            $bookChapter = $dbUtil->getDropDownFromSQLCols2($db, $sqlBookChapter, $book_chapter_id);
        }

        $text = "
        <td class='fieldValue'>
            <select name='book_id'>
                <option value=''>Book</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBook, $book_id)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='book_chapter_id'>
                <option value=''>Book Chapter</option>
                {$bookChapter}
            </select>
        </td>
        ";       
        
        return $text;
    }
}