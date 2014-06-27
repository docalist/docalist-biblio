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
 * Décrit un rapport.
 *
 * - Un rapport est un document analysant et évaluant un fonctionnement ou une
 *   activité, dans un ou plusieurs domaines, pour une période donnée.
 *   @see http://fr.wikipedia.org/wiki/Reporting
 *
 * - A report or account is any informational work made with the specific
 *   intention of relaying information or recounting certain events in a
 *   widely presentable form. Written reports are documents which present
 *   focused, salient content to a specific audience. Reports are often used
 *   to display the result of an experiment, investigation, or inquiry.
 *   The audience may be public or private, an individual or the public in
 *   general. Reports are used in government, business, education, science,
 *   and other fields.
 *   @see http://en.wikipedia.org/wiki/Report
 *
 * Principales caractéristiques :
 * - pas d'éditeur
 * - pas d'isbn
 */

// TODO : ne pas appeller ça "rapport". Ce que ça désigne, c'est une monographie non éditée (pas d'isbn)
// @formatter:off
return [
    'name' => 'report',
    'label' => __('Rapport', 'docalist-biblio'),
    'description' => __('Un rapport d\'activité ou une étude non publiée', 'docalist-biblio'),
    'fields' => [

        // Type, Genre, Media
        ['name' => 'group', 'label' => 'Nature du document'],
        ['name' => 'genre', 'table' => 'table:genres-report'],
        ['name' => 'media', 'table' => 'thesaurus:medias'],

        // Title, OtherTitle, Translation
        ['name' => 'group', 'label' => 'Titres'],
        ['name' => 'title'],
        ['name' => 'translation', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],

        // Author, Organisation
        ['name' => 'group', 'label' => 'Auteurs'],
        ['name' => 'organisation', 'table' => 'table:ISO-3166-1_alpha2_fr', 'table2' => 'thesaurus:marc21-relators_fr'],
        ['name' => 'author', 'table' => 'thesaurus:marc21-relators_fr', 'format' => 'fmt1'],

        // Date / Language / Pagination / Format
        ['name' => 'group', 'label' => 'Informations bibliographiques'],
        ['name' => 'date', 'table' => 'table:dates'],
        ['name' => 'language', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],
        ['name' => 'extent', 'table' => 'table:extent'],
        ['name' => 'format', 'table' => 'thesaurus:format'],
        ['name' => 'number', 'table' => 'table:numbers'],

        // Topic / Abstract / Note
        ['name' => 'group', 'label' => 'Indexation et résumé'],
        ['name' => 'topic', 'table' => 'table:topics'],
        ['name' => 'content', 'table' => 'table:content'],

        // Liens et relations
        ['name' => 'group', 'label' => 'Liens et relations'],
        ['name' => 'link', 'table' => 'table:links'],
        ['name' => 'relation', 'table' => 'table:relations'],

        // Ref / Owner / Creation / Lastupdate
        ['name' => 'group', 'label' => 'Informations de gestion'],
        ['name' => 'type'],
        ['name' => 'ref'],
        ['name' => 'owner'],
    ]
];
// @formatter:on