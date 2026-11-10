<?
class CP_Common_Modules_AgileIms_Student_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_student');
        $modules->registerModule($modObj, array(
            'title' => 'Trainee'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('agileIms_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}