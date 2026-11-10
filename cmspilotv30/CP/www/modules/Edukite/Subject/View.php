<?
class CP_Www_Modules_Edukite_Subject_View extends CP_Common_Modules_Edukite_Subject_View
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'], '', '', $row)}
            {$listObj->getListRowEnd($row['subject_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        <div class='subjectList'>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Subject', 's.title')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
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
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Subject Details', $fielset1)}
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