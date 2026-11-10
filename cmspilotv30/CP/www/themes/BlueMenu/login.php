<? $login = includeCPClass('Plugin', 'login', 'Login'); ?>
<div class="page_margins">
    <div class="page">
        <div id="header">
            <? print $panel->getBrandingPanel() ?>
        </div>
      
        <div id="main" class="hideboth loginTpl">
            <div id="col3">
                <div id="col3_content" class="clearfix">
                    <? echo $login->getLoginForm() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<? print $fn->getLangKeys(); ?>
