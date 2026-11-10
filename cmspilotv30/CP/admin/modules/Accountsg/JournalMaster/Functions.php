<?
class CP_Admin_Modules_Accountsg_JournalMaster_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('accountsg_journalMaster');
        $modObj['tableName'] = 'journal_master';
        $modObj['keyField']  = 'journal_master_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title' => 'Journal'
           ,'actBtnsList' => array('new', 'export', 'import')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsNew' => array()
           ,'actBtnsEdit' => array()
        ));
    }

    function afterDeleteHandler($journal_master_id){
        $db = Zend_Registry::get('db');

        $SQL = "
        DELETE FROM journal
        WHERE journal_master_id = {$journal_master_id}
        ";
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('accountsg_journalMaster', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
