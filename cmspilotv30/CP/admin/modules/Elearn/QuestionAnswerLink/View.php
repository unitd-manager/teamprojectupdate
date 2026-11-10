<?
class CP_Admin_Modules_ELearn_QuestionAnswer_ViewLink
{

    //==================================================================//
    function getNewPortal(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addPortal&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $qnRec = $fn->getRecordRowByID('page_question', 'page_question_id', $tv['srcRoomId']);
        $book_page_id = $qnRec['book_page_id'];
        $book_id      = $qnRec['book_id'];

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Answer', 'answer')}
                {$formObj->getTBRow('Answer (Chinese Traditional)', 'cht_answer')}
                {$formObj->getYesNoRRow('Right Answer', 'right_answer')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
            <input type='hidden' name='book_id' value='{$book_id}' />
            <input type='hidden' name='book_page_id' value='{$book_page_id}' />
        </form>
        ";

        return $text;
    }

    //==================================================================//
    function getEditPortal(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=savePortal&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('question_answer', 'question_answer_id', $id);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Answer', 'answer', $row['answer'])}
                {$formObj->getTBRow('Answer (Chinese Traditional)', 'cht_answer', $row['cht_answer'])}
                {$formObj->getYesNoRRow('Right Answer', 'right_answer', $row['right_answer'])}
            </fieldset>
            <input type='hidden' name='question_answer_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

}
