<?
class CP_Www_Widgets_Dms_DocumentUpload_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15', 'jqUploadify2.1.4');

    //========================================================//
    function getWidget() {

        $text = "
        {$this->getRowsHTML()}
        ";

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $c = &$this->controller;

        $formAction = $c->formAction;

        $module  = $c->module;
        $recType = $c->recordType;
        $id      = $c->record_id;

        $SQLCategory = "
        SELECT DISTINCT c.category_id
        	   ,c.title
        FROM category c
        JOIN section s ON (c.section_id = s.section_id)
        WHERE c.published= 1
          AND s.section_type = 'Document'
        ORDER BY c.title
        ";

        $expCategory = array(
             'useKey' => 1
            ,'firstOptionLabel' => $ln->gd('w.dms.documentUpload.form.lbl.chooseCategory')
        );

        $expUpld = array(
             'hideUploadBtn' => TRUE
            ,'browseButtonImg' => '/www/themes/Default/images/uploadbutton.gif'
            ,'btnWidth' => 100
            ,'formSuccessMsg' => $ln->gd('w.dms.documentUpload.message.success')
        );

        $text = "
        <form name='uploadForm' id='uploadForm' class='yform columnar cpUploadifyForm cpUploadifyNew' method='post' action='{$c->formAction}'>
            <fieldset>
                <h1>{$ln->gd('w.dms.documentUpload.form.heading')}</h1>
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.title.lbl'), 'title')}
                {$formObj->getDropDownRowBySQL($ln->gd('m.dms.document.lbl.category'), 'category_id', $SQLCategory, '', $expCategory)}
                <div class='type-text'>
                    <label for='fld_title'>{$ln->gd('w.dms.documentUpload.form.lbl.uploadFiles')}</label>
                    {$formObj->getUploadifyObj($module, $recType, '', $expUpld)}
                </div>
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />
            </fieldset>
        </form>
        ";

        return $text;
    }

}