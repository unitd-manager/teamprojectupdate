<?
class CP_Www_Modules_LawNews_Correspondent_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getList($dataArray) {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = '';
        foreach ($dataArray as $row){

            $expJur = array(
                 'secType' => 'Jurisdiction'
                ,'record_title' => $row['jurisdiction_title']
            );
            $urlJur = $cpUrl->getUrlByRecord($row, 'jurisdiction_id', $expJur);
            $urlReadMore = $urlJur . "?sp=archive";
            $urlCor = $urlJur;
            $addSearchCond = "
            AND c.content_id IN (
                SELECT DISTINCT jc.content_id
                FROM jurisdiction_content jc
                LEFT JOIN (jurisdiction j) ON (j.jurisdiction_id = jc.jurisdiction_id )
                WHERE j.published = 1
                  AND jc.jurisdiction_id = {$row['jurisdiction_id']}
            )
            ";

            $wRecord = getCPWidgetObj('content_record');
            $wCountryUpdate = $wRecord->getWidget(array(
                 'helperFn'       => 'getWidgetByCategoryType'
                ,'sectionType'    => 'News Archive'
                ,'categoryType'   => 'Country Update'
                ,'showHeading'    => FALSE
                ,'showDesc'       => FALSE
                ,'showShortDesc'  => FALSE
                ,'showPic'        => FALSE
                ,'addSearchCond'  => $addSearchCond
                ,'displayLimit'   => 2
            ));

            $wRecord = getCPWidgetObj('content_record');
            $wQAndA = $wRecord->getWidget(array(
                 'helperFn'       => 'getWidgetByCategoryType'
                ,'sectionType'    => 'News Archive'
                ,'categoryType'   => 'Q & A'
                ,'showHeading'    => FALSE
                ,'showDesc'       => FALSE
                ,'showPicInDesc'  => FALSE
                ,'showShortDesc'  => FALSE
                ,'addSearchCond'  => $addSearchCond
                ,'displayLimit'   => 1
            ));

            $rows .= "
            <li class='correspondent'>
                <h3 class='ruled'><a href='{$urlJur}'>{$row['jurisdiction_title']}</a></h3>
                <h4><a href='{$urlCor}'>{$row['title']}</a></h4>
                {$wCountryUpdate}
                {$wQAndA}
                <div class='readMore'>
                    <a href='{$urlReadMore}'>
                        {$ln->gd('cp.lbl.readMore')}
                    </a>
                </div>
            </li>
            ";
        }

        $text = "
        <div class='correspondentList'>
            <h2 class='ruled'>{$tv['room_name']}</h2>
            <ul class='noDefault'>
                {$rows}
            </ul>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $title =  "<h1 class='ruled'>{$ln->gfv($row, 'title', '0')}</h1>" ;
        $exp = array('style' => 'mb5');
        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['correspondent_id'], $exp);

        if ($pic != ''){
            $pic = "<div class='float_right'>{$pic}</div>";
        }

        $text = "
        {$pic}
        {$title}
        {$ln->gfv($row, 'description', '0')}
        ";

        return $text;
    }

}
