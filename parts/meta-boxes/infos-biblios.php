<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Title:       Informations bibliographiques
 * Post Type:   dclrecord
 * Order:       50
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
    'field' => 'date',
    'label' => 'Date de publication',
    'description' => "<br />Date d'édition ou de diffusion du document.",
    'value' => '',
    'attributes' => array('class' => 'text', ),
    'position' => 'wrap',
));

piklist('field', array(
    'type' => 'select',
    'field' => 'language',
    'scope' => 'post_meta',
    'label' => 'Langue du document',
    'choices' => array(
        'FRE' => 'français',
        'ENG' => 'anglais',
        'SPA' => 'espagnol',
        'DEU' => 'allemand',
        'ITA' => 'italien',
    ),
    'position' => 'wrap',
    'list' => false,
));

piklist('field', array(
    'type' => 'text',
    'scope' => 'post_meta',
    'field' => 'pagination',
    'label' => 'Pagination',
    'description' => "<br />Nombre de pages (exemple : 10p.) ou numéros des pages de début et de fin (exemple : 15-20).",
    'value' => '',
    'attributes' => array('class' => 'text', ),
    'position' => 'wrap',
));

piklist('field', array(
    'type' => 'text',
    'scope' => 'post_meta',
    'field' => 'format',
    'label' => 'Format du document',
    'description' => "<br />Caractéristiques matérielles du document : étiquettes de collation (tabl, ann, fig...), références bibliographiques, etc.",
    'value' => '',
    'attributes' => array('class' => 'large-text', ),
    'position' => 'wrap',
));
