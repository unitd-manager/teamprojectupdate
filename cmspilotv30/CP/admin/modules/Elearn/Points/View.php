<?
class CP_Admin_Modules_ELearn_Points_View extends CP_Common_Lib_ModuleViewAbstract
{
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
            {$listObj->getGoToDetailText($rowCounter, $row['student_name'])}
            {$listObj->getListDataCell($row['book'])}
            {$listObj->getListDataCell($row['record_type'])}
            {$listObj->getListDataCell($row['points'])}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListDataCell($row['points_id'], 'center')}
            {$listObj->getListRowEnd($row['points_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Student', 'st.student_name')}
        {$listObj->getListHeaderCell('Book', 'book')}
        {$listObj->getListHeaderCell('Activity Type', 'record_type')}
        {$listObj->getListHeaderCell('Points', 'points')}
        {$listObj->getListHeaderCell('Creation Date', 'p.creation_date')}
        {$listObj->getListHeaderCell('ID', 'p.points_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $sqlStudent = "
        SELECT student_id
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
        FROM student s
        ORDER BY student_name
        ";

        $fieldset = "
        {$formObj->getDDRowBySQL('Student', 'student_id', $sqlStudent)}
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
        
        $fielset1 = "
        {$formObj->getTBRow('Student Name', 'student_name', $row['student_name'])}
        {$formObj->getTBRow('Book Name', 'book_name', $row['book'])}
        {$formObj->getTBRow('Activity Type', 'record_type', $row['record_type'])}
        {$formObj->getTBRow('Points', 'points', $row['points'])}
        {$formObj->getTBRow('Creation Date', 'creation_date', $row['creation_date'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Details', $fielset1)}
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

    }

    //==================================================================//
    //==================================================================//

}