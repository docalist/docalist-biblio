<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Title:       Indexation et résumé
 * Post Type:   dclrecord
 * Order:       80
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;

piklist('field', array(
    'type' => 'group',
    'field' => 'topic',
    'scope' => 'post_meta',
    'label' => __('Mots-clés'),
    'position' => 'wrap',
    'add_more' => true,
    'fields' => array(
        array(
            'type' => 'select',
            'field' => 'type',
            'columns' => 3,
            'label' => 'thesaurus',
            'choices' => array(
                'un' => 'theso un',
                'deux' => 'therso deux',
            ),
        ),
        array(
            'type' => 'textarea',
            'field' => 'terms',
            'columns' => 9,
            'label' => 'Termes',
        ),
    )
));

piklist('field', array(
    'type' => 'group',
    'field' => 'abstract',
    'scope' => 'post_meta',
    'label' => __('Résumé'),
    'position' => 'wrap',
    'add_more' => true,
    'fields' => array(
        array(
            'type' => 'select',
            'field' => 'language',
            'columns' => 3,
            'label' => 'Langue du résumé',
            'choices' => array(
                'FRE' => 'français',
                'ENG' => 'anglais',
                'SPA' => 'espagnol',
                'DEU' => 'allemand',
                'ITA' => 'italien',
            ),
        ),
        array(
            'type' => 'textarea',
            'field' => 'content',
            'columns' => 9,
            'label' => 'Résumé',
        ),
    )
));

piklist('field', array(
    'type' => 'group',
    'field' => 'note',
    'scope' => 'post_meta',
    'label' => __('Notes'),
    'position' => 'wrap',
    'add_more' => true,
    'fields' => array(
        array(
            'type' => 'select',
            'field' => 'type',
            'columns' => 3,
            'label' => 'Type',
            'choices' => array(
                'internal' => 'note interne',
                'warning' => 'avertissement',
                'pedago' => 'objectifs pédagogiques',
                'public' => 'public concerné',
                'pre-requis' => 'pré-requis',
                'access' => 'modalités d\'accès',
                'copyright' => 'copyright',
            ),
        ),
        array(
            'type' => 'textarea',
            'field' => 'content',
            'columns' => 9,
            'label' => 'Note',
        ),
    )
));
