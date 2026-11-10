<?
class CP_Www_Themes_Quest_Controller extends CP_Www_Lib_ThemeControllerAbstract
{
    function getTraineeRow(){
        return $this->fns->getTraineeRow();
    }

    function getTotalTraineeToAdd(){
        return $this->fns->getTotalTraineeToAdd();
    }

    function getCourseTypeById(){
        return $this->fns->getCourseTypeById();
    }

    function getTrainingHistory(){
        return $this->fns->getTrainingHistory();
    }

    function getExistingContactInfo(){
        return $this->fns->getExistingContactInfo();
    }
    
}