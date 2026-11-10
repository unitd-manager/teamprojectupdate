<?
class CP_Admin_Modules_Edukite_Class_View extends CP_Common_Modules_Edukite_Class_View
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
            {$listObj->getListDataCell($row['class_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['class_id'])}
            {$listObj->getListRowEnd($row['class_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Class Name', 'c.title')}
        {$listObj->getListHeaderCell('ID', 'c.class_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];
        
        $sqlStaff = "
        SELECT  a.staff_id
               ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name 
        FROM staff a 
        ORDER BY staff_name
        ";
        
        $fielset1 = "
        {$formObj->getTBRow('Class Name', 'title', $row['title'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text ="
        {$displayLinkData->getLinkPortalMain('edukite_class', 'edukite_teacherLink', 'Staff Linked', $row)}
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