<?
class CP_Www_Themes_Edukloud_Functions
{
    /*
     *
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        foreach ($dataArray as $row){
        }

        /** create an instance of the widget **/
        $wSlideshow = getCPWidgetObj('media_anythingSlider');
        $slideshow = $wSlideshow->getWidget(array(
        ));

        $wRecord = getCPWidgetObj('content_record');

        $text = "
        <div class='subcolumns mt20'>
            <div class='c75l'>
                <div class='subcl'>
                    {$slideshow}
                </div>
            </div>
            <div class='c25r'>
                <div class='subcr'>
                    this is test
                </div>
            </div>    
        </div>
        <div class='homeContent'>
            <div class='c33l'>
                <div class='subcl'>
                    <div class='contentNews'>
                        <h2>{$ln->gd('newsForStudents')}</h2>
                        {$wRecord->getWidget(array(
                             'contentType' => 'News for Students'
                        ))}
                    </div>
                </div>
            </div>
            <div class='c33l'>
                <div class='subc'>
                    <div class='contentNews'>
                        <h2>{$ln->gd('newsForStaffs')}</h2>
                        {$wRecord->getWidget(array(
                             'contentType' => 'News for Staffs'
                        ))}
                    </div>
                </div>
            </div>
            <div class='c33r'>
                <div class='subcr'>
                    <div class='contentNews'>
                        <h2>{$ln->gd('newsForParents')}</h2>
                        {$wRecord->getWidget(array(
                             'contentType' => 'News for Parents'
                        ))}
                    </div>
                </div>
            </div>
        </div>        
        ";

        return $text;
    }

    /**
     *
     * @param <type> $redirectURL
     */
    function checkLoggedIn($loginSecType) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');
    
        $userType = $fn->getSessionParam('cpLoginTypeWWW');
        $loginUrl = $cpUrl->getUrlBySecType('Login');
        
        if ($loginSecType == 'edukloud_student' && $userType != 'edukloud_student'){
            $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
            $cpUtil->redirect($loginUrl);
        }

        if ($loginSecType == 'edukloud_parent' && $userType != 'edukloud_parent'){
            $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
            $cpUtil->redirect($loginUrl);
        }

        if ($loginSecType == 'edukloud_teacher' && $userType != 'edukloud_teacher'){
            $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
            $cpUtil->redirect($loginUrl);
        }
    }

    /**
     *
     */
    function isLoggedInStudent() {
        $fn = Zend_Registry::get('fn');
    
        $userType = $fn->getSessionParam('cpLoginTypeWWW');
        
        if ($userType == 'edukloud_student'){
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function isLoggedInTeacher() {
        $fn = Zend_Registry::get('fn');
    
        $userType = $fn->getSessionParam('cpLoginTypeWWW');

        if ($userType == 'edukloud_staff'){
            return true;
        } else {
            return false;
        }        
    }

    /**
     *
     */
    function isLoggedInParent() {
        $fn = Zend_Registry::get('fn');
    
        $userType = $fn->getSessionParam('cpLoginTypeWWW');

        if ($userType == 'edukloud_parent'){
            return true;
        } else {
            return false;
        }        
    }
}