<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Title:       Editeur
 * Post Type:   dclrecord
 * Order:       60
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;

piklist('field', array(
    'type' => 'group',
    'field' => 'editor',
    'scope' => 'post_meta',
    'add_more' => true,
    'label' => __('Editeur'),
    'fields' => array(
        array(
            'type' => 'text',
            'field' => 'name',
            'columns' => 4,
        ),
        array(
            'type' => 'text',
            'field' => 'city',
            'columns' => 4
        ),
        array(
            'type' => 'select',
            'field' => 'country',
            'choices' => array(
                '' => '',
                'FRA' => 'France',
                'USA' => 'Etats-Unis',
                'ESP' => 'Espagne',
                'ITA' => 'Italie',
                'GER' => 'Allemagne',
            ),
            'columns' => 2
        )
    )
));

piklist('field', array(
    'type' => 'group',
    'field' => 'collection',
    'scope' => 'post_meta',
    'add_more' => true,
    'label' => __('Collection'),
    'fields' => array(
        array(
            'type' => 'text',
            'field' => 'name',
            'columns' => 8,
        ),
        array(
            'type' => 'text',
            'field' => 'number',
            'columns' => 2
        ),
    )
));

piklist('field', array(
    'type' => 'group',
    'field' => 'edition',
    'scope' => 'post_meta',
    'add_more' => true,
    'label' => __('Edition'),
    'fields' => array(
        array(
            'type' => 'select',
            'field' => 'type',
            'choices' => array(
                '' => '',
                'new' => 'Nouvelle édition',
                'upd' => 'Edition revue et corrigée',
                'HS' => 'Hors série',
                'rap' => 'Numéro de rapport',
                'sup' => 'Numéro de supplément',
            ),
            'columns' => 8,
        ),
        array(
            'type' => 'text',
            'field' => 'value',
            'columns' => 2
        ),
    )
));

piklist('field', array(
    'type' => 'text',
    'scope' => 'post_meta',
    'field' => 'isbn',
    'label' => 'ISBN',
    'description' => "<br />International Standard Book Number : numéro international permettant d'identifier un livre publié.",
    'value' => '',
    'attributes' => array('class' => 'text', ),
    'position' => 'wrap',
));
