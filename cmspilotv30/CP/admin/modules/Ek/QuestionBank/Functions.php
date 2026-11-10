<?
class CP_Admin_Modules_Ek_QuestionBank_Functions
{

    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_questionBank');
        $modules->registerModule($modObj, array(
            'title'       => 'Question Bank'
           ,'tableName'   => 'question_bank'
           ,'keyField'    => 'question_bank_id'
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}