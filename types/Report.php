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
        ['name' => 'genre', 'table' => ['genres-report']], // rapport officiel, rapport moral, étude, rapport financier, fin de contrat...
        ['name' => 'media', 'table' => ['medias']], // papier, web

        // Title, OtherTitle, Translation
        ['name' => 'group', 'label' => 'Titres'],
        ['name' => 'title'],
        // ['name' => 'othertitle', 'table' => ['titles']], // pas de othertitle pour un rapport
        ['name' => 'translation', 'table' => ['languages']],

        // Author, Organisation
        ['name' => 'group', 'label' => 'Auteurs'],
        ['name' => 'organisation', 'table' => ['countries', 'roles-organisation']], // /com
        ['name' => 'author', 'table' => ['roles-author'], 'format' => 'fmt1'],

        // Date / Language / Pagination / Format
        ['name' => 'group', 'label' => 'Informations bibliographiques'],
        ['name' => 'date'],
        ['name' => 'language', 'table' => ['languages']],
        ['name' => 'pagination'],
        ['name' => 'format'],
        ['name' => 'doi'],

        // Topic / Abstract / Note
        ['name' => 'group', 'label' => 'Indexation et résumé'],
        ['name' => 'topic', 'table' => ['prisme', 'names', 'geo', 'free']],
        ['name' => 'abstract', 'table' => ['languages']],
        ['name' => 'note', 'table' => ['notes']],

        // Liens et relations
        ['name' => 'group', 'label' => 'Liens et relations'],
        ['name' => 'link', 'table' => ['links']],
        ['name' => 'relations', 'table' => ['relations']],

        // Ref / Owner / Creation / Lastupdate
        ['name' => 'group', 'label' => 'Informations de gestion'],
        ['name' => 'type'],
        ['name' => 'ref'],
        ['name' => 'owner'],
        ['name' => 'creation'],
        ['name' => 'lastupdate'],
    ]
];
// @formatter:on