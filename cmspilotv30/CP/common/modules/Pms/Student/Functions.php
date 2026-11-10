<?
class CP_Common_Modules_Pms_Student_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_student');
        $modules->registerModule($modObj, array(
            'title' => 'Trainee'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pms_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}