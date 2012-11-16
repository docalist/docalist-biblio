<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Title:       Informations de gestion
 * Post Type:   dclrecord
 * Order:       100
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;

piklist('field', array(
    'type' => 'text',
    'scope' => 'post_meta',
    'field' => 'ref',
    'label' => __('N° de référence'),
    'position' => 'wrap',
));

piklist('field', array(
    'type' => 'text',
    'scope' => 'post_meta',
    'field' => 'owner',
    'add_more' => true,
    'label' => __('Propriétaire'),
    'position' => 'wrap',
));

piklist('field', array(
    'type' => 'group',
    'field' => 'creation',
    'scope' => 'post_meta',
    'label' => __('Date de création'),
    'position' => 'wrap',
    'fields' => array(
        array(
            'type' => 'text',
            'field' => 'date',
            'columns' => 3,
            'label' => 'le',
        ),
        array(
            'type' => 'text',
            'field' => 'by',
            'columns' => 3,
            'label' => 'par',
        ),
    )
));

piklist('field', array(
    'type' => 'group',
    'field' => 'lastupdate',
    'scope' => 'post_meta',
    'label' => __('Dernière modification'),
    'position' => 'wrap',
    'fields' => array(
        array(
            'type' => 'text',
            'field' => 'date',
            'columns' => 3,
            'label' => 'le',
        ),
        array(
            'type' => 'text',
            'field' => 'by',
            'columns' => 3,
            'label' => 'par',
        ),
    )
));
