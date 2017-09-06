<?php

// models
$GLOBALS['TL_MODELS']['tl_mvo_facebook']       = 'Mvo\\ContaoFacebook\\Model\\FacebookModel';
$GLOBALS['TL_MODELS']['tl_mvo_facebook_post']  = 'Mvo\\ContaoFacebook\\Model\\FacebookPostModel';
$GLOBALS['TL_MODELS']['tl_mvo_facebook_event'] = 'Mvo\\ContaoFacebook\\Model\\FacebookEventModel';

// BE
$GLOBALS['BE_MOD']['mvo_facebook_integration'] = [
    'mvo_facebook'  => [
        'tables' => ['tl_mvo_facebook'],
    ],
    'mvo_facebook_posts'  => [
        'tables' => ['tl_mvo_facebook_post'],
        'import' => ['mvo_contao_facebook.listener.import_posts', 'onForceImport'],
    ],
    'mvo_facebook_events' => [
        'tables' => ['tl_mvo_facebook_event'],
        'import' => ['mvo_contao_facebook.listener.import_events', 'onForceImport'],
    ]
];

$GLOBALS['TL_CSS'][] = 'bundles/mvocontaofacebook/css/backend_svg.css';

// FE
$GLOBALS['TL_CTE']['mvo_facebook']['mvo_facebook_post_list']  = 'Mvo\\ContaoFacebook\\Element\\ContentPostList';
$GLOBALS['TL_CTE']['mvo_facebook']['mvo_facebook_event_list'] = 'Mvo\\ContaoFacebook\\Element\\ContentEventList';

// data import
$GLOBALS['TL_CRON']['minutely'][] = ['mvo_contao_facebook.listener.import_posts', 'onImport'];
$GLOBALS['TL_CRON']['minutely'][] = ['mvo_contao_facebook.listener.import_events', 'onImport'];

// open graph tags
$GLOBALS['TL_HOOKS']['generatePage'][] = ['mvo_contao_facebook.listener.open_graph_tags', 'onInject'];