<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Title:       Autres titres et traductions
 * Post Type:   dclrecord
 * Order:       20
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
use Docalist\Core\Utils;

piklist('field', array(
    'type' => 'group',
    'field' => 'othertitle',
    //     'scope' => 'post_meta',
    'add_more' => true,
    'label' => __('Autres titres'),
    'fields' => array(
        array(
            'type' => 'select',
            'field' => 'type',
            'label' => 'Type de titre',
            'label_position' => 'before',
            'columns' => 3,
            'choices' => Utils::choices('record_title', true),
        ),
        array(
            'type' => 'text',
            'field' => 'title',
            'label' => 'Titre',
            'label_position' => 'before',
            'columns' => 7,
        ),
    )
));

piklist('field', array(
    'type' => 'group',
    'field' => 'translation',
    //     'scope' => 'post_meta',
    'add_more' => true,
    'label' => __('Traduction du titre'),
    'fields' => array(
        array(
            'type' => 'select',
            'field' => 'language',
            'label' => 'Langue',
            'label_position' => 'before',
            'columns' => 3,
            'choices' => Utils::choices('language', true),
        ),
        array(
            'type' => 'text',
            'field' => 'title',
            'label' => 'Titre traduit',
            'label_position' => 'before',
            'columns' => 7,
        ),
    )
));
