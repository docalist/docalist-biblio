<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Title:       Congrès, diplômes
 * Post Type:   dclrecord
 * Order:       70
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;

piklist('field', array(
    'type' => 'group',
    'field' => 'event',
    'scope' => 'post_meta',
    'label' => __('Informations sur l\'évènement'),
    'position' => 'wrap',
    //    'add_more' => true,
    'fields' => array(
        array(
            'type' => 'text',
            'field' => 'title',
            'columns' => 5,
            'label' => 'Titre',
        ),
        array(
            'type' => 'text',
            'field' => 'date',
            'columns' => 2,
            'label' => 'Date',
        ),
        array(
            'type' => 'text',
            'field' => 'place',
            'columns' => 3,
            'label' => 'Lieu',
        ),
        array(
            'type' => 'text',
            'field' => 'number',
            'columns' => 2,
            'label' => 'Numéro',
        ),
    )
));

piklist('field', array(
    'type' => 'group',
    'field' => 'degree',
    'scope' => 'post_meta',
    'label' => __('Diplôme'),
    'position' => 'wrap',
    //    'add_more' => true,
    'fields' => array(
        array(
            'type' => 'select',
            'field' => 'level',
            'columns' => 2,
            'label' => 'Niveau',
            'choices' => array(
                'licence' => 'Licence',
                'maitrise' => 'Maîtrise',
                'doctorat' => 'Doctorat',
            ),
        ),
        array(
            'type' => 'text',
            'field' => 'title',
            'columns' => 10,
            'label' => 'Intitulé',
        ),
    )
));
