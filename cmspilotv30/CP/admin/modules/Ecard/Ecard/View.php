<?
class CP_Admin_Modules_Ecard_Ecard_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['sender_name'])}
            {$listObj->getListDataCell($row['sender_dept'])}
            {$listObj->getListDataCell($row['language'])}
            {$listObj->getListDataCell($row['ecard_id'], 'center')}
            {$listObj->getListRowEnd($row['ecard_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Sender Name', 'e.sender_name')}
        {$listObj->getListHeaderCell('Sender Dept', 'e.sender_dept')}
        {$listObj->getListHeaderCell('Language', 'e.language')}
        {$listObj->getListHeaderCell('ID', 'e.ecard_id', 'headerCenter')}
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
        {$formObj->getTBRow('Sender Name', 'sender_name')}
        {$formObj->getTBRow('Sender Dept', 'sender_dept')}
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
        $formObj = Zend_Registry::get('formObj');

        $sqlContact = "
        SELECT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
        FROM contact c
        ORDER BY contact_name
        ";
        $expContact     = array('detailValue' => $row['contact_name']);

        $formObj->mode = $tv['action'];

        $text = '';

        $fieldset1 = "
        {$formObj->getTBRow('Sender Name', 'sender_name', $row['sender_name'])}
        {$formObj->getTBRow('Sender Dept', 'sender_dept', $row['sender_dept'])}
        {$formObj->getTBRow('Language', 'language', $row['language'])}
        {$formObj->getDDRowBySQL('Contact Name', 'contact_id', $sqlContact, $row['contact_id'], $expContact)}
        ";
        
        $fieldset2 = $formObj->getHTMLEditor('Message', 'message', $row['message']);

        $text = "
        {$formObj->getFieldSetWrapped('Ecard Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        ";

        return $text;
    }

    //==================================================================//
    //========================================================//
    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
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
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');
        
        $text = '';

        
        return $text;
    }
}