<?
class CP_Admin_Modules_Ek_ExamResult_Functions
{

    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_examResult');
        $modules->registerModule($modObj, array(
            'tableName'   => 'exam_result'
           ,'title'       => 'Exam Result'
           ,'keyField'    => 'exam_result_id'
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
    }

    //==================================================================//
}