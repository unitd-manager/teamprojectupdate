<?
class CP_Www_Themes_Directory_Hooks_ModuleWebBasicHome
{
    /*
     * 
     */
    function getList($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $subNav = Zend_Registry::get('subNav');
        $modelHelper = Zend_Registry::get('modelHelper');

        $wRecord = getCPWidgetObj('content_record');
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Business');

        $registerUrl = $cpUrl->getUrlBySecType('Register');
        $exp['expForSearchVar'] = array('specialFlag' => 'promoSlideshow');
        $exp['limit'] = 10;
        $promoData = $modelHelper->getDataArrayByModule('directory_promotion', $exp);
        $promoSlideshow = getCPModuleObj('directory_promotion')->view->getPromoSlideshow($promoData);
        
        $callToAction = '';
        if (!isLoggedInWww()){
            $callToAction = "
            <div class='callToAction'>
                {$wRecord->getWidget(array(
                     'contentType' => 'Homepage Call-to-Action'
                ))}
                <div class='btnSignupNow'>
                    <a href='{$registerUrl}'>{$ln->gd('m.membership.contact.lbl.signupNow')}</a>
                </div>
            </div>
            ";
        }
        
        $text = "
        {$callToAction}
        <div class='promotions'>
            {$promoSlideshow}
        </div>
        <div class='subcolumns homeBtm'>
            <div class='c25l'>
                <div class='subcl'>
                    {$subNav->getWidget(array(
                         'section_id' => $secRec['section_id']
                        ,'title' => $ln->gd('w.core.subNav.lbl.byCategory')
                        ,'showSubCat' => false
                    ))}

                    <div class='leftPanelBox'>
                        <div class='boxTop'>
                            <div class='boxBtm'>
                                <div class='title'>Reserved for Ideas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='c50l'>
                <div class='subc latestActivityBox'>
                    <div class='boxTop'>
                        <div class='boxBtm'>
                            <h2>Latest User Activity</h2>
                            {$this->getLatestUserActivity()}
                        </div>
                    </div>
                </div>
            </div>
            <div class='c25r'>
                <div class='subcr adRight'>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /*
     * 
     */
    function getLatestUserActivity() {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $media = Zend_Registry::get('media');
        
        $SQL = "
        (
        SELECT c.record_id AS business_id
              ,b.business_name AS title
              ,cat.title AS category_title
              ,sc.title AS sub_category_title
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
              ,c.creation_date AS content_date
              ,c.comments AS review
              ,'reviewed'  AS action
        FROM `comment` c
        JOIN (business b) ON (c.record_id = b.business_id)
        JOIN (contact cont) ON (c.contact_id = cont.contact_id)
        JOIN category cat ON b.category_id = cat.category_id
        JOIN sub_category sc ON b.sub_category_id  = sc.sub_category_id
        WHERE c.room_name = 'directory_business' 
        )
        
        UNION 
        (
            SELECT mb.business_id AS business_id
                  ,b.business_name AS title
                  ,cat.title AS category_title
                  ,sc.title AS sub_category_title
                  ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
                  ,mb.creation_date AS content_date
                  ,'' AS review
                  ,'liked'  AS action
            FROM `my_business` mb
            JOIN (business b) ON (mb.business_id = b.business_id)
            JOIN (contact cont) ON (mb.contact_id = cont.contact_id)
            JOIN category cat ON b.category_id = cat.category_id
            JOIN sub_category sc ON b.sub_category_id  = sc.sub_category_id
        )
        ORDER BY content_date DESC 
            LIMIT 0, 10
        ";
        
        $dataArray = $dbUtil->getSQLResultAsArray($SQL);
        $rows = '';
        
        foreach($dataArray AS $row){
            $timeStr = '';
            if ($row['content_date'] != ''){
                $dateStr = $fn->getCPDate($row['content_date'], 'Y-m-d H:i:s');
                $diff = time() - strtotime($dateStr);
                $timeStr = "{$dateUtil->getRelativeTime($dateStr)}";
            }
            $expPic = array('folder' => 'thumb');

            $pic = $media->getMediaPicture('directory_business', 'picture', $row['business_id'], $expPic);
            if ($pic == ''){
                $pic = "<img src='{$cpCfg['cp.themePathAlias']}{$cpCfg['cp.theme']}/images/no-image-small.gif' />";
            }

            $url = $cpUrl->getUrlByRecord($row, 'business_id', array('secType' => 'Business'));

            $rows .= "
            <div class='row'>
                <div class='pic'>
                    <a href='{$url}'>{$pic}</a>
                </div>
                <div class='txt'>
                    <div>
                        <a href='#'>{$row['contact_name']}</a> {$row['action']}
                        <a href='{$url}'>{$row['title']}</a> {$timeStr}
                    </div>
                    <div class='comment'>{$row['review']}</div>
                </div>
            </div>
            ";
        }
        
        $text = "
        <div class='latestActivity'>
            {$rows}
        </div>
        ";

        return $text;
    }
}