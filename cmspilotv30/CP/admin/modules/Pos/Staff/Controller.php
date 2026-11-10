<?
class CP_Admin_Modules_Pos_Staff_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    function getDuplicateStaff() {
        return $this->model->getDuplicateStaff();
    }
    
    /**
     * /admin/index.php?_spAction=updateMissingSaltPasswords&showHTML=0&module=core_staff
     */

    /*function getUpdateMissingSaltPasswords(){
        $this->model->getUpdateMissingSaltPasswords();
    }*/
}