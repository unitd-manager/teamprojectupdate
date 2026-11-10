<?
class CP_Admin_Modules_Party_PartyReminder_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['event_title'])}
            {$listObj->getListDataCell($row['event_date'])}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListYesNo($row['reminder_sent'])}
            {$listObj->getListDataCell($row['party_reminder_id'], 'center')}
            {$listObj->getListRowEnd($row['party_reminder_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'pr.name')}
        {$listObj->getListHeaderCell('Email', 'pr.email')}
        {$listObj->getListHeaderCell('Event Title', 'pr.event_title')}
        {$listObj->getListHeaderCell('Event Date', 'pr.event_date')}
        {$listObj->getListHeaderCell('Creation', 'pr.creation_date')}
        {$listObj->getListHeaderCell('Reminder Sent', 'pr.reminder_sent')}
        {$listObj->getListHeaderCell('Id', 'pr.party_reminder_id', 'center')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";
        return $text;
    }

    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');

        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');

        $text = "
        <td class='dateRange'>
            Event Date - From:
            <input type='text' allowEdit='1' name='start_date' class='fld_date' id='fld_start_date' value='{$start_date}' />
            To:
            <input type='text' allowEdit='1' name='end_date' class='fld_date' id='fld_end_date' value='{$end_date}' />
        </td>
        ";

        return $text;
    }
}