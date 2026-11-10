<?
class CP_Admin_Modules_Ek_Book_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListPublishedImage($row['published'], $row['book_id'])}
            {$listObj->getListDataCell($row['book_id'], 'center')}
            {$listObj->getListRowEnd($row['book_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'b.title')}
        {$listObj->getListHeaderCell('Published', 'b.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'b.book_id' , 'headerCenter')}
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
       
        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
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
        {$media->getRightPanelMediaDisplay('Picture', 'ek_book', 'picture', $row)}
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