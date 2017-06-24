<?php

$GLOBALS['TL_DCA']['tl_content']['palettes']['mvo_facebook_post_list'] =
    '{type_legend},type,headline;mvo_facebook_numberOfPosts;{image_legend},size,fullsize';

$GLOBALS['TL_DCA']['tl_content']['palettes']['mvo_facebook_event_list'] =
    '{type_legend},type,headline;{image_legend},size,fullsize';

$GLOBALS['TL_DCA']['tl_content']['fields']['mvo_facebook_numberOfPosts'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['mvo_facebook_numberOfPosts'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['rgxp' => 'natural', 'tl_class' => 'w50'],
    'sql'       => "smallint(5) unsigned NOT NULL default '0'"
];