<?
class CP_Admin_Modules_Ek_BookPage_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT bp.*
              ,b.title AS book_title
              ,bc.title AS book_chapter_title
        FROM book_page bp
        LEFT JOIN (book b) ON (bp.book_id = b.book_id)
        LEFT JOIN (book_chapter bc) ON (bp.book_chapter_id = bc.book_chapter_id)
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
        $searchVar->mainTableAlias = 'bp';

        $book_id            = $fn->getReqParam('book_id');
        $book_chapter_id    = $fn->getReqParam('book_chapter_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "bp.book_page_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'bp.book_page_id');

            if ($book_id != '') {
                $searchVar->sqlSearchVar[] = "bp.book_id = {$book_id}";
            }

            if ($book_chapter_id != '') {
                $searchVar->sqlSearchVar[] = "bp.book_chapter_id = {$book_chapter_id}";
            }
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               bp.title LIKE '%{$tv['keyword']}%'
            OR bp.description LIKE '%{$tv['keyword']}%'
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
        $fa = $fn->addToFieldsArray($fa, 'book_chapter_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'description');
        
        return $fa;
    }
}
