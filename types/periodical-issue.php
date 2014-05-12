<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Type;

/**
 * Décrit un numéro particulier d'un périodique.
 *
 * - A single instance of a periodically published journal, magazine, or
 *   newspaper.
 *   @see http://en.wikipedia.org/wiki/Issue
 *
 * Principales caractéristiques :
 * - a un parent de type Periodical
 */
// @formatter:off
return [
    'name' => 'periodical-issue',
    'label' => __('Numéro de périodique', 'docalist-biblio'),
    'description' => __('Une parution d\'un périodique.', 'docalist-biblio'),
    'fields' => [

        // Title, OtherTitle, Translation
        ['name' => 'group', 'label' => 'Numéro de périodique'],
        ['name' => 'volume'],
        ['name' => 'issue'],
        ['name' => 'title'],

        // Date / Language / Pagination / Format
        ['name' => 'group', 'label' => 'Informations bibliographiques'],
        ['name' => 'date'],
        ['name' => 'isbn'],// Un issue peut avoir un isbn = un fascicule
        ['name' => 'pagination'], // nb de p/ de ce n°
        ['name' => 'format'], // matériel d'accompagnement

        // Topic / Abstract / Note
        ['name' => 'group', 'label' => 'Indexation et résumé'],
        ['name' => 'topic', 'table' => 'table:topics'],
        ['name' => 'abstract', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],
        ['name' => 'note', 'table' => 'table:notes'],

        // Liens et relations
        ['name' => 'group', 'label' => 'Liens et relations'],
        ['name' => 'link', 'table' => 'table:links'],
        ['name' => 'relation', 'table' => 'table:relations'],

        // Ref / Owner / Creation / Lastupdate
        ['name' => 'group', 'label' => 'Informations de gestion'],
        ['name' => 'type'],
        ['name' => 'ref'],
        ['name' => 'owner'],
//         ['name' => 'creation'],
//         ['name' => 'lastupdate'],
    ]
];
// @formatter:on