<?
class CP_Admin_Modules_ELearn_QuestionAnswerLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_questionAnswerLink');
        $modObj['tableName'] = 'question_answer';
        $modObj['keyField']  = 'question_answer_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title'         => 'Answer'
        ));
    }
}
