<?php

// models
$GLOBALS['TL_MODELS']['tl_mvo_facebook_post']  = 'Mvo\\ContaoFacebook\\Model\\FacebookPostModel';
$GLOBALS['TL_MODELS']['tl_mvo_facebook_event'] = 'Mvo\\ContaoFacebook\\Model\\FacebookEventModel';

// BE
$GLOBALS['BE_MOD']['mvo_facebook_integration'] = [
    'mvo_facebook_posts'  => ['tables' => ['tl_mvo_facebook_post']],
    'mvo_facebook_events' => ['tables' => ['tl_mvo_facebook_event']]
];

// FE
$GLOBALS['TL_CTE']['mvo_facebook']['mvo_facebook_post_list']  = 'Mvo\\ContaoFacebook\\Element\\ContentPostList';
$GLOBALS['TL_CTE']['mvo_facebook']['mvo_facebook_event_list'] = 'Mvo\\ContaoFacebook\\Element\\ContentEventList';

// data import
$GLOBALS['TL_CRON']['minutely'][] = ['mvo_contao_facebook.listener.import_posts', 'onImport'];
$GLOBALS['TL_CRON']['minutely'][] = ['mvo_contao_facebook.listener.import_events', 'onImport'];
