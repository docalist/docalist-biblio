<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Title:       Auteurs
 * Post Type:   dclrecord
 * Order:       30
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;

piklist('field', array(
    'type' => 'group',
    'field' => 'author',
    //     'scope' => 'post_meta',
    'add_more' => true,
    'label' => __('Personnes'),
    'fields' => array(
        array(
            'type' => 'text',
            'field' => 'name',
            'label' => 'Nom',
            'columns' => 4,
        ),
        array(
            'type' => 'text',
            'field' => 'firstname',
            'label' => 'Prénom',
            'columns' => 4
        ),
        array(
            'type' => 'select',
            'field' => 'role',
            'label' => 'Rôle',
            'choices' => array(
                '' => '',
                'pref' => 'Préface',
                'trad' => 'Traducteur',
                'ill' => 'Illustrateur',
                'dir' => 'Directeur',
                'coord' => 'Coordonnateur',
                'postf' => 'Postface',
                'intro' => 'Introduction',
                'interviewer' => 'interviewer'
            ),
            'columns' => 2
        )
    )
));

piklist('field', array(
    'type' => 'group',
    'field' => 'organisation',
    'scope' => 'post_meta',
    'add_more' => true,
    'label' => __('Organismes'),
    'fields' => array(
        array(
            'type' => 'text',
            'field' => 'name',
            'label' => 'Nom',
            'columns' => 4,
        ),
        array(
            'type' => 'text',
            'field' => 'city',
            'label' => 'Ville',
            'columns' => 2
        ),
        array(
            'type' => 'text',
            'field' => 'country',
            'label' => 'Pays',
            'columns' => 2
        ),
        array(
            'type' => 'select',
            'field' => 'role',
            'label' => 'Rôle',
            'choices' => array(
                '' => '',
                'com' => 'Commanditaire',
                'financ' => 'Financeur',
            ),
            'columns' => 2
        )
    )
));
