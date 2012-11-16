<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Title:       Journal
 * Post Type:   dclrecord
 * Order:       40
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;

$conditions = array( array(
        'field' => 'type',
        // 'scope' => 'post_meta',
        'value' => 'article'
    ));

piklist('field', array(
    'type' => 'text',
    'scope' => 'post_meta',
    'field' => 'journal',
    'label' => 'Titre de périodique',
    'description' => "Nom du journal (revue, magazine, périodique, etc.) dans lequel a été publié le document.",
    'value' => '',
    'attributes' => array('class' => 'large-text', ),
    'position' => 'wrap',
    //'conditions' => $conditions,
));

piklist('field', array(
    'type' => 'text',
    'scope' => 'post_meta',
    'field' => 'issn',
    'label' => 'ISSN',
    'description' => "<br />International Standard Serial Number : numéro international identifiant le périodique dont le nom figure dans le champ Journal.",
    'value' => '',
    'attributes' => array('class' => 'text', ),
    'position' => 'wrap',
    //'conditions' => $conditions,
));

piklist('field', array(
    'type' => 'text',
    'scope' => 'post_meta',
    'field' => 'volume',
    'label' => 'Numéro de volume',
    'description' => "<br />Pour les publications en série, ce champ content le numéro de volume du fascicule. Pour les monographies, ce champ contient le numéro de tome du document référencé dans la notice",
    'value' => '',
    'attributes' => array('class' => 'text', ),
    'position' => 'wrap',
    //'conditions' => $conditions,
));

piklist('field', array(
    'type' => 'text',
    'scope' => 'post_meta',
    'field' => 'issue',
    'label' => 'Numéro de fascicule',
    'description' => "<br />Indique le numéro du fascicule de la revue dans lequel l'article a été publié.",
    'value' => '',
    'attributes' => array('class' => 'text', ),
    'position' => 'wrap',
    //'conditions' => $conditions,
));
