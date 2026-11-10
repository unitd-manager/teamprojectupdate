<?
class CP_Admin_Modules_Party_Message_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $text = '';
        $rows = '';

        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['from_name'])}
            {$listObj->getListDataCell($row['from_email'])}
            {$listObj->getListDataCell($row['party_title'])}
            {$listObj->getListDataCell($dateUtil->formatDate($row['message_date'], 'DD MMM YYYY'))}
            {$listObj->getListDataCell($row['message_id'], 'center')}
            {$listObj->getListRowEnd($row['message_id'])}
            ";

            $rowCounter++;
        }

        $text .= "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Subject', 'm.title')}
        {$listObj->getListHeaderCell('From Name', 'm.from_name')}
        {$listObj->getListHeaderCell('From Email', 'm.from_email')}
        {$listObj->getListHeaderCell('Party Title', 'party_title')}
        {$listObj->getListHeaderCell('Date', 'm.message_date')}
        {$listObj->getListHeaderCell('ID', 'm.message_id', 'headerCenter')}
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

        $modParty = getCPModuleObj('party_partySetup');
        $sqlParty = $modParty->model->getPartySetupSQL();

        $fielset = "
        {$formObj->getDDRowBySQL('Party Title', 'party_setup_id', $sqlParty)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $modParty = getCPModuleObj('party_partySetup');
        $sqlParty = $modParty->model->getPartySetupSQL();
        $expParty = array('detailValue' => $row['party_title']);

        $fieldset1 = "
        {$formObj->getTBRow('From Name', 'from_name', $row['from_name'])}
        {$formObj->getTBRow('From Email', 'from_email', $row['from_email'])}
        {$formObj->getTBRow('Subject', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Party Title', 'party_setup_id', $sqlParty, $row['party_setup_id'], $expParty)}
        ";

        $expEditor = array('includeStylesheet' => false);
        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'), $expEditor)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Broadcast Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Message', $fieldset2)}
        ";
        
        return $text;
    }

    function getRightPanel($row) {
        $media = Zend_Registry::get('media');

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'party_message', 'picture', $row)}
        ";

        return $text;
    }

    function getQuickSearch() {

        $text = "
        ";

        return $text;
    }
}