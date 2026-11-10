<?
class CP_Admin_Modules_Accountsg_JournalMaster_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSaveJournal() {
        $json = $this->model->getSaveJournal();
        return $json;
    }

    function getLedgerAuthorize() {
        $json = $this->model->getLedgerAuthorize();
        return $json;
}

    function getLedgerPending() {
        $json = $this->model->getLedgerPending();
        return $json;
    }

    function getUpdateAccountHeadOther() {
        $this->model->getUpdateAccountHeadOther();
    }
}