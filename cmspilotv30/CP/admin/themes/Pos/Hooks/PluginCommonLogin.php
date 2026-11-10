<?
class CP_Admin_Themes_Pos_Hooks_PluginCommonLogin
{
    /**
     *
     */
    function getLoginForm(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');


        if (!isset($_SESSION['returnUrlAfterLogin'])){
            $_SESSION['returnUrlAfterLogin'] =  @$_SERVER['HTTP_REFERER'];
        }

        $formAction = 'index.php?plugin=common_login&_spAction=loginSubmit&showHTML=0';
        $expPass['password'] = 1;
        $expPass['disableAutoComplete'] = $cpCfg['p.common.login.disableAutoCompleteTextFld'];

        $expEmail = array('disableAutoComplete' => $cpCfg['p.common.login.disableAutoCompleteTextFld']);
            
        $text = "
        <div id='loginOuter'>
            <div class='subcolumns'>
                <div class='c38l'>
                    <div class='subcl pr0'>
                        {$ln->gd('loginIntro')}
                    </div>
                </div>
                <div class='c62r'>
                    <div class='subcr'>
                        <form name='loginForm' id='loginForm' class='yform login cpJqForm' method='post' action='{$formAction}'>
                            <fieldset>
                                <h1>Login</h1>
                                <div id='errorDisplayBox'></div>
                                {$formObj->getTextBoxRow('Email', 'email', '', $expEmail)}
                                {$formObj->getTextBoxRow('Password', 'pass_word', '', $expPass)}
                                <div class='floatbox'>
                                    <div class='float_left'>
                                        <input type='submit' name='submit' class='button' value='Login' />
                                    </div>
                                    <div class='float_left'>
                                        <button class='button' id='btnSmartCard'>Smart Card</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getChooseUsergroupForm($obj, $staffRow){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $formObj->mode = 'edit';

        $formAction = 'index.php?plugin=common_login&_spAction=chooseUsergroupSubmit&showHTML=0';
        $saveLogin = $fn->getPostParam('saveLogin', '', true);

        $sqlGroup = "
        SELECT DISTINCT a.shop_id
              ,b.title
        FROM mod_acc_shop_user_group a
        LEFT JOIN shop b ON (a.shop_id = b.shop_id)
        WHERE a.staff_id = '{$staffRow['staff_id']}'
        ORDER BY b.title
        ";

        $text = "
        <form name='chooseUsergroupForm' id='chooseUsergroupForm' class='yform login' method='post' action='{$formAction}'>
            <fieldset>
                <h1>Choose Shop</h1>
                {$formObj->getDDRowBySQL('Shop', 'shop_id', $sqlGroup)}
                {$formObj->getDDRowBySQL('Terminal', 'terminal_id')}
                <input type='submit' name='submit' class='button' value='Submit' />
                <input type='hidden' name='staff_id' value='{$staffRow['staff_id']}' />
                <input type='hidden' name='saveLogin' value='{$saveLogin}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getChooseUsergroupSubmit($obj) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $shop_id = $fn->getPostParam('shop_id', '', true);
        $staffId       = $fn->getPostParam('staff_id', '', true);
        $terminal_id = $fn->getPostParam('terminal_id', '', true);
        $saveLogin     = $fn->getPostParam('saveLogin', '', true);

        $rec = $fn->getRecordByCondition('mod_acc_shop_user_group', "staff_id = '{$staffId}' AND shop_id = '{$shop_id}'");
        
        if (is_array($rec)){
            $row = $fn->getRecordRowByID($cpCfg['cp.modAccessStaffTable'], 'staff_id', $staffId, array('condn' => 'AND published = 1'));
            $row['user_group_id'] = $rec['user_group_id'];

            $_SESSION['shopId'] = $shop_id;
            $_SESSION['terminalId'] = $terminal_id;
            
            $retUrl = $obj->setSessionValuesAfterLogin($row, $saveLogin);
            /** if there is a hook for homepage in the theme level then use that **/
            $theme = getCPThemeObj($cpCfg['cp.theme']);
            if (method_exists($theme->fns, 'setSessionValuesAfterLogin')){
                $theme->fns->setSessionValuesAfterLogin($row);
            }
            return $validate->getSuccessMessageXML($retUrl);

        } else {
            return $validate->getSuccessMessageXML("index.php");
        }
    }

}