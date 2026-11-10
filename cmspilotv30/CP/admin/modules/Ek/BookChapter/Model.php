<?
class CP_Admin_Modules_Ek_BookChapter_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT bc.*
              ,b.title AS book_title
        FROM book_chapter bc
        LEFT JOIN (book b) ON (bc.book_id = b.book_id)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'bc';

        $book_id  = $fn->getReqParam('book_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "bc.book_chapter_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'bc.book_chapter_id');

            if ($book_id != '') {
                $searchVar->sqlSearchVar[] = "bc.book_id = {$book_id}";
            }
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               bc.title LIKE '%{$tv['keyword']}%'
            OR bc.description LIKE '%{$tv['keyword']}%'
            )";
        }        
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'book_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'description');
        
        return $fa;
    }

    /**
     *
     */
    function getBookChapterSQL($book_id) {

        return $sql = "
        SELECT bc.book_chapter_id
              ,bc.title
        FROM book_chapter bc
        WHERE bc.book_id = '{$book_id}'
        ORDER BY bc.title
        ";

    }
}
