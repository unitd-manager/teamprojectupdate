<?
class CP_Common_Modules_EnterpriseIms_Student_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_student');
        $modules->registerModule($modObj, array(
            'title' => 'Trainee'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}