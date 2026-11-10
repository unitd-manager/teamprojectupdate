<?
class CP_Www_Modules_Common_Comment_View extends CP_Common_Modules_Common_Comment_View
{
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $formObj = Zend_Registry::get('formObj');
        $theme = getCPThemeObj($cpCfg['cp.theme']);

        $rows = "";

        foreach($dataArray AS $row) {
            $pic = '';

            if ($row['contact_module'] != ''){                
                $exp = array('folder' => 'thumb');
                $pic = $media->getMediaPicture($row['contact_module'], 'picture', $row['contact_id'], $exp);
            }

            if ($pic == ''){
                $pic = "<img src='{$cpCfg['cp.themePathAlias']}{$cpCfg['cp.theme']}/images/contact-icon.png' />";
            }
            
            $url = $cpUrl->getUrlByCatType('Public Profile Dashboard') . "?cpi={$row['contact_id']}";
            $comments = nl2br($row['comments']);
            $name = ($row['contact_name'] != '') ? $row['contact_name'] : 'Anonymous';
            
            $rows .= "
            <div class='row'>
                <div class='pic'>
                    <a href='{$url}'>{$pic}</a>
                </div>
                <div class='txt'>
                    <div class='name'>{$name}</div>
                    <div class='date'>{$fn->getCPDate($row['comment_date'], 'd M Y l h:i:s A')}</div>
                    <div class='rating'>
                        {$formObj->getStarRatingFld('rating' . $row['comment_id'], $row['rating'], true)}
                    </div>
                    <div class='comment'>{$comments}</div>
                </div>
            </div>
            ";
        }
        
        $text = "
        {$theme->view->getPagerPanel()}
        <div class='commentsList'>
            {$rows}
        </div>
        ";
        
        return $text;
    }
}