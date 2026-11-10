<?
class CP_Admin_Modules_Common_Broadcast_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $text = '';
        $rows = '';

        $rowCounter = 0;
        $fnMod = includeCPClass('ModuleFns', 'common_broadcast');

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['from_name'])}
            {$listObj->getListDataCell($row['from_email'])}
            {$listObj->getListDataCell($fnMod->getBroadcastCount($row['broadcast_id'], 'total'), 'center')}
            {$listObj->getListDataCell($fnMod->getBroadcastCount($row['broadcast_id'], 'success'), 'center')}
            {$listObj->getListDataCell($fnMod->getBroadcastCount($row['broadcast_id'], 'failed'), 'center')}
            {$listObj->getListDataCell($fnMod->getBroadcastCount($row['broadcast_id'], 'remaining'), 'center')}
            {$listObj->getListDataCell($dateUtil->formatDate($row['broadcast_date'], 'DD MMM YYYY'))}
            {$fn->getSiteFldForList($row)}
            {$listObj->getListDataCell($row['broadcast_id'], 'center')}
            {$listObj->getListRowEnd($row['broadcast_id'])}
            ";

            $rowCounter++;
        }

        $text .= "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.common.broadcast.lbl.title', 'Title'), 'a.title')}
        {$listObj->getListHeaderCell($ln->gd('m.common.broadcast.lbl.fromName', 'From Name'), 'a.from_name')}
        {$listObj->getListHeaderCell($ln->gd('m.common.broadcast.lbl.fromEmail', 'From Email'), 'a.from_email')}
        {$listObj->getListHeaderCell($ln->gd('m.common.broadcast.lbl.total', 'Total'),  '', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.common.broadcast.lbl.success', 'Success'),  '', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.common.broadcast.lbl.failed', 'Failed'),  '', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.common.broadcast.lbl.remaining', 'Remaining'),'', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.common.broadcast.lbl.date', 'Date'), 'a.broadcast_date')}
        {$fn->getSiteLabelForList()}
        {$listObj->getListHeaderCell($ln->gd('m.common.broadcast.lbl.id', 'ID'), 'a.broadcast_id', 'headerCenter')}
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
        $fn = Zend_Registry::get('fn');
        $dbz = Zend_Registry::get('dbz');
        $ln = Zend_Registry::get('ln');
        
        $template = '';
        if ($fn->isModuleExist('common_template')){
            $select = $dbz->select()
                         ->from(array('t' => 'template'),
                                array('template_id', 'title'));
            
            $template = "
            {$formObj->getDDRowBySQL('Template', 'template_id', $select)}
            ";
        }
        
        $fielset = "
        {$formObj->getTBRow($ln->gd('m.common.broadcast.lbl.subject', 'Subject'), 'title')}
        {$template}
        {$fn->getSiteDropDown('new')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];
        $recordType = "";
        $smsText = "";
        $emailText = "";
        
        $fnMod = includeCPClass('ModuleFns', 'common_broadcast');
        $sqlRecordType = $fnMod->getRecordTypeArray();

        if ($cpCfg['m.common.broadcast.showRecordType'] == 1){
            $smsText = "
            <div class='smsDetailWrap'>
                {$formObj->getTBRow($ln->gd('m.common.broadcast.lbl.fromNo', 'From No'), 'from_no', $row['from_no'])}
                {$formObj->getTARow($ln->gd('m.common.broadcast.lbl.message', 'Message'), 'message', $row['message'])}
            </div>    
            ";
            
            $emailText = "
            <div class='emailDetailWrap'>
                {$formObj->getTBRow($ln->gd('m.common.broadcast.lbl.fromEmail', 'From Email'), 'from_email', $row['from_email'])}
            </div>
            ";
        }

        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('m.common.broadcast.lbl.fromName', 'From Name'), 'from_name', $row['from_name'])}
        {$formObj->getTBRow($ln->gd('m.common.broadcast.lbl.fromEmail', 'From Email'), 'from_email', $row['from_email'])}
        {$smsText}
        {$emailText}
        {$formObj->getTBRow($ln->gd('m.common.broadcast.lbl.subject', 'Subject'), 'title', $row['title'])}
        {$fn->getSiteDropDown($formObj->mode, $row)}
        ";

        $expEditor = array('includeStylesheet' => false);
        $fieldset2 = "
        {$formObj->getHTMLEditor($ln->gd('m.common.broadcast.lbl.description', 'Description'), 'description', $ln->gfv($row, 'description', '0'), $expEditor)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.common.broadcast.lbl.broadcastDetails', 'Broadcast Details'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.common.broadcast.lbl.message', 'Message'), $fieldset2)}
        ";
        
        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');

        $text = '';

        $text .= "
        {$media->getRightPanelMediaDisplay($ln->gd('m.common.broadcast.link.attachment', 'Attachments'), 'common_broadcast', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('common_broadcast', 'common_testRecipientLink', $ln->gd('m.common.broadcast.link.testRecipients', 'Test Recipients') , $row)}
        {$displayLinkData->getLinkPortalMain('common_broadcast', 'common_contactLink', $ln->gd('m.common.broadcast.link.linkContacts', 'Link Contacts'), $row)}
        ";
        
        return $text;
    }

    /**
     *
     */
    function isRecordType($recordType){

        $arr1 = array(
            'sms'
        );

        $recordType = strtolower($recordType);
        if (in_array($recordType, $arr1)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        
        $text = "
        {$fn->getSiteDropDown('search')}
        ";
        
        return $text;
    }

}