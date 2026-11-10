<?
class CP_Www_Lib_ViewHelper extends CP_Common_Lib_ViewHelper
{
    /**
     *
     */
    function getDetailViewWrapper($html, $exp = array()){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $pager = Zend_Registry::get('pager');
        $modulesArr = Zend_Registry::get('modulesArr');
        $clsInst = $fn->getIssetParam($exp, 'clsInst');

        if (!is_object($clsInst)){
            $clsInst = Zend_Registry::get('currentModule');
        }
        $model = $clsInst->model;
        $view = $clsInst->view;
        $module = $clsInst->name;

        $row = $model->dataArray[0];
        $keyField = $modulesArr[$module]["keyField"];
        $showActBtnsBelowForm = $modulesArr[$module]["showActBtnsBelowForm"];

        $btns = '';
        if ($showActBtnsBelowForm){
		    $action = Zend_Registry::get('action');
            $actBtns = $action->getActionButtons('button');

            if ($actBtns != ''){
                $btns = "
                <div class='floatbox'>
                    <div class='type-button float_right'>
                        {$actBtns}
                    </div>
                </div>
                ";
            }
        }

        $formText = "
        <div class='ym-form ym-columnar linearize-form yform columnar detail'>
            {$html}
            {$btns}
        </div>
        ";

        if (method_exists($view, 'getRightPanel') || CP_SCOPE == 'admin'){
            $right = method_exists($view, 'getRightPanel') ? $view->getRightPanel($row) : '';

            $text = "
            <div class='subcolumns col3Body'>
                <div class='c50l'>
                    <div class='subcl'>
                        {$formText}
                    </div>
                </div>
                <div class='c50r'>
                    <div class='rightPanel'>
                        {$right}
                    </div>
                </div>
            </div>
            ";
        } else {
            $text = "
            <div class='col3Body'>
                {$formText}
            </div>
            ";
        }

        /** the output is surrounded by form just to inherit the styles of yform - to change later **/
        $text = "
        {$text}
        <input id='record_id' type='hidden' name='{$keyField}' value='{$row[$keyField]}'>
        <input id='room_name' type='hidden' value='{$module}'>
        <input id='returnToListUrl' type='hidden' value='{$pager->getReturnToListLinkAfterDelete()}'>
        ";

        return $text;
    }

    /**
     *
     */
    function getDetailViewWrapperWww($html){
        $text = "
        <div class='col3Body'>
            {$html}
        </div>
        ";

        return $text;
    }

    /**
    
     *
     */
    function getEditViewWrapperWWW($html, $exp = array()){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $pager = Zend_Registry::get('pager');
        $modulesArr = Zend_Registry::get('modulesArr');
        $clsInst = $fn->getIssetParam($exp, 'clsInst');

        if (!is_object($clsInst)){
            $clsInst = Zend_Registry::get('currentModule');
        }

        $model = $clsInst->model;
        $view = $clsInst->view;
        $module = $clsInst->name;
        $row = $model->dataArray[0];
        $keyField = $modulesArr[$module]["keyField"];
        $showActBtnsBelowForm = $modulesArr[$module]["showActBtnsBelowForm"];

        $returnUrl = $fn->getIssetParam($exp, 'returnUrl');

        $formAction = '/index.php?_spAction=save';
        $returnUrl = ($returnUrl !='') ? $returnUrl : $cpUrl->getUriWithNoQstr();
        $returnUrl = preg_replace('/\/(detail|edit)\/[0-9]+\//', '/', $returnUrl);

        $formAction .= '&showHTML=0';

        $strPos = $cpUtil->stringPosition($formAction, "module");
        if ($strPos == -1) {
            $formAction  = $formAction . "&module=" . $module;
        }

        $btns = '';
        if ($showActBtnsBelowForm){
            $action  = Zend_Registry::get('action');
            $actBtns = $action->getActionButtons('button');

            if ($actBtns != ''){
                $btns = "
                <div class='type-button actBtnsWrapper'>
                    {$actBtns}
                </div>
                ";
            }
        }

        $hideKeyField = $fn->getIssetParam($exp, 'hideKeyField', false);
        $hiddenKeyFieldRow = '';
        if(!$hideKeyField){
            $hiddenKeyFieldRow  = "<input id='record_id' type='hidden' name='{$keyField}' value='{$row[$keyField]}'>";
        }

        $formText = "
        <form name='edit' class='yform columnar edit cpJqForm' id='frmEdit' action='{$formAction}' method='post'>
            {$html}
            {$btns}
            {$hiddenKeyFieldRow}
            <input type='hidden' name='apply' value='0'>
            <input type='hidden' name='returnUrl' value='{$returnUrl}'>
        </form>
        ";


        if (method_exists($view, 'getRightPanel')){
            $right = method_exists($view, 'getRightPanel') ? $view->getRightPanel($row) : '';

            $text = "
            <div class='subcolumns col3Body'>
                <div class='c50l'>
                    <div class='subcl'>
                        {$formText}
                    </div>
                </div>
                <div class='c50r'>
                    <div class='rightPanel'>
                        {$right}
                    </div>
                </div>
            </div>
            ";
        } else {
            $text = "
            <div class='col3Body'>
                {$formText}
            </div>
            ";
        }

        $text = "
        {$text}
        <input id='room_name' type='hidden' value='{$tv['module']}'>
        <input id='returnToListUrl' type='hidden' value='{$pager->getReturnToListLinkAfterDelete()}'>
        ";

        return $text;
    }
}
