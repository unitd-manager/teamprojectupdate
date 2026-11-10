<?
$cpCfg = array();

//------------ ACCOUNT HEAD --------------//
$cpCfg['m.account.accHead.showContact'] = true;

//------------ JOURNAL MASTER --------------//
$cpCfg['m.account.journalMaster.voucherTypeArr'] = array (
     'Journal'
    ,'Payment'
    ,'Receipt'
    ,'Counter'
    ,'Cash'
);

$cpCfg['m.account.journalMaster.hasAvgStock'] = true;


//------------ ACC CATEGORY --------------//
$cpCfg['m.account.accCategory.categoryType'] = array (
     'Bank Account'
    ,'Cash Account'
    ,'Sundry Creditor / Debtor'
    ,'Counter'
);

//------------ COUNTER MASTER --------------//
$cpCfg['m.account.counterMaster.counterActionArr'] = array (
    'buy'
    ,'sell'
);

$cpCfg['cp.account.exchangeRateDecimals'] = 4;
$cpCfg['cp.showPagerPanelInFooter'] = true;
$cpCfg['cp.fixMainPanelHeightInList'] = true;
$cpCfg['m.account.counterMaster.pageCSSClass'] = 'hidecol1';

//------------ CONTACT --------------//
$cpCfg['m.account.contact.hasCompanyTable'] = false;
$cpCfg['m.account.contact.showInterest'] = true;

return $cpCfg;