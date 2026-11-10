<?
class CP_Www_Modules_Edukite_Task_Controller extends CP_Common_Modules_Edukite_Task_Controller
{
    //==================================================================//
    function getDetail(){
        $text = parent::getDetailWithForm();

        return $text;
    }
}
