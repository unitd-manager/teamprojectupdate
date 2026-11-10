<?
class CP_Admin_Modules_Party_Testimonial_View extends CP_Common_Modules_Party_Testimonial_View
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
            {$listObj->getListDataCell($row['name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDateCell($row['testimonial_date'])}
            {$listObj->getListPublishedImage($row['published'], $row['testimonial_id'])}
            {$listObj->getListRowEnd($row['testimonial_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 't.title')}
        {$listObj->getListHeaderCell('Testimonial By', 't.name')}
        {$listObj->getListHeaderCell('Email', 't.email')}
        {$listObj->getListHeaderCell('Date', 't.testimonial_date')}
        {$listObj->getListHeaderCell('Published', 't.published', 'headerCenter')}
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

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'] )}
        {$formObj->getTBRow('Testimonial By', 'name', $row['name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getDateRow('Date', 'testimonial_date', $row['testimonial_date'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getTARow('Description' , 'description', $row['description'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Testimonial Details', $fieldset1)}
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