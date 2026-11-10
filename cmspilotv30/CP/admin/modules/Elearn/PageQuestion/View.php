<?
class CP_Admin_Modules_ELearn_PageQuestion_View extends CP_Common_Lib_ModuleViewAbstract
{

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');
        
        $bookPageRec = $fn->getRecordRowByID('book_page', 'book_page_id', $tv['srcRoomId']);
        $book_id     = $bookPageRec['book_id'];
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Question', 'question')}
                {$formObj->getTBRow('Question (Chinese Traditional)')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
            <input type='hidden' name='book_id' value='{$book_id}' />
        </form>
        ";

        return $text;
    }

    //==================================================================//
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('page_question', 'page_question_id', $id);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Question', 'question', $row['question'])}
                {$formObj->getTBRow('Question (Chinese Traditional)', 'cht_question', $row['cht_question'])}
            </fieldset>
            <input type='hidden' name='page_question_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}