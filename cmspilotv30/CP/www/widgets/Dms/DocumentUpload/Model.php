<?
class CP_Www_Widgets_Dms_DocumentUpload_Model extends CP_Common_Lib_WidgetModelAbstract
{

    /**
     *
     * @return <type>
     */
    function getUploadSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
 
        //-------------------------------------------------------------------------------------//
        $valArr = $this->getUploadSubmitValidate();
        $hasError = $valArr[0];
        $xmlText = $valArr[1];

        if ($hasError) {
            return $xmlText;
        }

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'published', 1);
        $fa = $fn->addSessionToFieldsArray($fa, 'country_id');
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'document');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'document');
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();
        
        $exp = array(
            'record_id' => $id
        );
        
        return $validate->getSuccessMessageXML('', '', $exp);
    }

    /**
     *
     * @return <type>
     */
    function getUploadSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('category_id', 'Please choose the category');

        if (count($validate->errorArray) == 0) {
            return array(0, $validate->getSuccessMessageXML());
        } else {
            return array(1, $validate->getErrorMessageXML());
        }

        return $text;
    }
}