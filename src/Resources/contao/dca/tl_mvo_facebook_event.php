<?php

/**
 * Table tl_mvo_facebook_event
 */
$GLOBALS['TL_DCA']['tl_mvo_facebook_event'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'    => 'Table',
        'enableVersioning' => false,
        'notDeletable'     => true,
        'notEditable'      => true,
        'closed'           => true,
        'sql'              => array
        (
            'keys' => array
            (
                'id' => 'primary',
            )
        )
    ),

    // List
    'list'   => array
    (
        'sorting'    => array
        (
            'mode'   => 1,
            'fields' => array('startTime'),
            'flag'   => 7,
            //'panelLayout' => 'search,limit'
        ),
        'label'      => array
        (
            'fields' => array('name'),
            'format' => '%s',
        ),
        'operations' => array
        (
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_mvo_facebook_event']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ),
            'toggle' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_mvo_facebook_event']['toggle'],
                'attributes'           => 'onclick="Backend.getScrollOffset();"',
                'haste_ajax_operation' => [
                    'field'   => 'visible',
                    'options' => [
                        [
                            'value' => '',
                            'icon'  => 'invisible.svg'
                        ],
                        [
                            'value' => '1',
                            'icon'  => 'visible.svg'
                        ]
                    ]
                ]
            ),
        )
    ),

    // Fields
    'fields' => array
    (
        // contao
        'id'           => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp'       => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'visible'      => array
        (
            //            'label'     => &$GLOBALS['TL_LANG']['tl_mvo_facebook_event']['visible'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => array('isBoolean' => true),
            'sql'       => "char(1) NOT NULL default '1'"
        ),


        // facebook
        'eventId'      => array
        (
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'name'         => array
        (
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'description'  => array
        (
            'sql' => "mediumtext NULL"
        ),
        'startTime'    => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'locationName' => array
        (
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'image'        => array
        (
            //            'label' => &$GLOBALS['TL_LANG']['tl_mvo_facebook_event']['image'],
            //            'exclude'                 => true,
            //            'inputType'               => 'fileTree',
            //            'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
            'sql' => "binary(16) NULL"
        ),
        'ticketUri'    => array
        (
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'lastChanged'  => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
    )
);