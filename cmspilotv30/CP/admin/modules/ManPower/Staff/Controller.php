<?
class CP_Admin_Modules_ManPower_Staff_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    /**
     * /admin/index.php?_spAction=updateMissingSaltPasswords&showHTML=0&module=core_staff
     */
    function getUpdateMissingSaltPasswords(){
        $this->model->getUpdateMissingSaltPasswords();
    }

    function getPrintStaffContract(){
        $this->model->getPrintStaffContract();
    }

    function getStaffDocumentSubmit(){
        $this->model->getStaffDocumentSubmit();
    }

    function getPrintStaffContractAbuDhabi(){
        $this->model->getPrintStaffContractAbuDhabi();
    }

    function getPrintDeclarationWord(){
        $this->model->getPrintDeclarationWord();
    }

    function getPrintNoDueWord(){
        $this->model->getPrintNoDueWord();
    }

    function getPrintCancelWord(){
        $this->model->getPrintCancelWord();
    }

    function getPrintResignationWord(){
        $this->model->getPrintResignationWord();
    }

}