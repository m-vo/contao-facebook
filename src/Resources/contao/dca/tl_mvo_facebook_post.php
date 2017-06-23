<?php

/**
 * Table tl_mvo_facebook_post
 */

$GLOBALS['TL_DCA']['tl_mvo_facebook_post'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => false,
        'notEditable'       => true,
        'closed'            => true,
        'ondelete_callback' => array(
            function (DataContainer $dc) {
                // delete image with record
                $imageUuid = $dc->activeRecord->image;
                if (null != $imageUuid) {
                    if (Database::getInstance()
                            ->prepare("SELECT id FROM tl_mvo_facebook_post WHERE image = ? AND id <> ?")
                            ->execute($imageUuid, $dc->id)
                            ->numRows == 0
                    ) {
                        $objFile = FilesModel::findByUuid($imageUuid);
                        if ($objFile != null) {
                            Files::getInstance()->delete($objFile->path);
                            Dbafs::deleteResource($objFile->path);
                        }
                    }
                };
            }
        ),
        'sql'               => array
        (
            'keys' => array
            (
                'id' => 'primary',
            )
        ),
    ),

    // List
    'list'   => array
    (
        'sorting'           => array
        (
            'mode'   => 1,
            'fields' => array('postTime'),
            'flag'   => 7,
            //'panelLayout' => 'search,limit'
        ),
        'label'             => array
        (
            'fields' => array('message'),
            'format' => '%s',
        ),
        'global_operations' => array(
            'import' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_mvo_facebook_post']['import'],
                'href'  => 'key=import',
                'class' => 'header_icon',
                'icon'  => 'system/themes/' . \Backend::getTheme() . '/images/sync.gif',
            )
        ),
        'operations'        => array
        (
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_mvo_facebook_post']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ),
            'toggle' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_mvo_facebook_post']['toggle'],
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
            'delete' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_mvo_facebook_post']['delete'],
                'href'  => 'act=delete',
                'icon'  => 'delete.svg'
            )
        )
    ),

    // Fields
    'fields' => array
    (
        // contao
        'id'          => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp'      => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'visible'     => array
        (
            //            'label'     => &$GLOBALS['TL_LANG']['tl_mvo_facebook_post']['visible'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => array('isBoolean' => true),
            'sql'       => "char(1) NOT NULL default '1'"
        ),

        // facebook
        'postId'      => array
        (
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'postTime'    => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'message'     => array
        (
            'sql' => "mediumtext NULL"
        ),
        'image'       => array
        (
            //            'label'                   => &$GLOBALS['TL_LANG']['tl_mvo_facebook_post']['image'],
            //            'exclude'                 => true,
            //            'inputType'               => 'fileTree',
            //            'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
            'sql' => "binary(16) NULL"
        ),
        'lastChanged' => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
    )
);