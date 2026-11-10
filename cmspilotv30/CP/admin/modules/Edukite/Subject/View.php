<?
class CP_Admin_Modules_Edukite_Subject_View extends CP_Common_Modules_Edukite_Subject_View
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
            {$listObj->getListDataCell($row['subject_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['subject_id'])}
            {$listObj->getListRowEnd($row['subject_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Subject', 's.title')}
        {$listObj->getListHeaderCell('ID', 's.subject_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 's.published', 'headerCenter')}
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
        {$formObj->getTBRow('Subject', 'title')}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

                
        $expVl   = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Subject', 'title', $row['title'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('General Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
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