<?
class CP_Common_Modules_Edukloud_Student_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukloud_student');
        $modules->registerModule($modObj, array(
            'title' => 'Student'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukloud_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}