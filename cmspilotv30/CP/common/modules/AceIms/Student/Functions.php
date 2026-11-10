<?
class CP_Common_Modules_AceIms_Student_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_student');
        $modules->registerModule($modObj, array(
            'title' => 'Trainee'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('aceIms_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}