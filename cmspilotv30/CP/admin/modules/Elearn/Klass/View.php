<?
class CP_Admin_Modules_ELearn_Klass_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['klass_id'], 'center')}
            {$listObj->getListRowEnd($row['klass_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Class name', 'k.title')}
        {$listObj->getListHeaderCell('ID', 'k.klass_id' , 'headerCenter')}
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

        $fielset1 = "
        {$formObj->getTBRow('Class Name', 'title', $row['title'], 0)}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Class Details', $fielset1)}
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

        $text ="
        {$displayLinkData->getLinkPortalMain("elearn_klass", "elearn_bookLink", "Books / Colors Linked", $row)}
        {$displayLinkData->getLinkPortalMain("elearn_klass", "elearn_teacherLink", "Teachers Linked", $row)}
        ";
        return $text;
    }

    //==================================================================//
    //==================================================================//
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('klass', 'book');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'klass_book'
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('klass', 'teacher');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'klass_teacher'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));

    }


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

        $text = "
        ";
        
        
        return $text;
    }
}