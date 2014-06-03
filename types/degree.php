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
 * Décrit un document élaboré en vue de l'obtention d'un diplôme : thèse,
 * mémoire, dissertation, etc.
 *
 * - Dans le milieu universitaire, une thèse est un mémoire résumant un
 *   travail de recherche universitaire, soutenu devant un jury par un
 *   étudiant afin d'obtenir un diplôme ou un grade universitaire.
 *   @see http://fr.wikipedia.org/wiki/Th%C3%A8se
 *
 * - A thesis or dissertation is a document submitted in support of
 *   candidature for an academic degree or professional qualification
 *   presenting the author's research and findings
 *   @see http://en.wikipedia.org/wiki/Thesis
 *
 * Principales caractéristiques :
 * - écrit en vue d'obtenir un diplôme
 * - relié à une école, une fac, une université, etc.
 * - a un seul auteur physique
 * - peut avoir un ou plusieurs maitre de stage, directeur de thèse, etc.
 */
// @formatter:off
return [
    'name' => 'degree',
    'label' => __('Mémoire ou thèse', 'docalist-biblio'),
    'description' => __('Un document élaboré en vue de l\'obtention d\'un diplôme.', 'docalist-biblio'),
    'fields' => [

        // Type, Genre, Media
        ['name' => 'group', 'label' => 'Nature du document'],
        ['name' => 'genre', 'table' => 'table:genres-degree'], // mémoire, thèse, écrit de certification
        ['name' => 'media', 'table' => 'thesaurus:medias'], // papier, internet, cd, dvd

        // Title, OtherTitle, Translation
        ['name' => 'group', 'label' => 'Titres'],
        ['name' => 'title'],
        ['name' => 'degree'],
        ['name' => 'othertitle', 'table' => 'table:titles'],
        // ['name' => 'translation', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],

        // Author, Organisation
        ['name' => 'group', 'label' => 'Auteurs'],
        ['name' => 'author', 'table' => 'thesaurus:marc21-relators_fr', 'format' => 'fmt1'], // / dir
        ['name' => 'organisation', 'table' => 'table:ISO-3166-1_alpha2_fr', 'table2' => 'thesaurus:marc21-relators_fr'], // libellé : organisme de soutenance

        // Date / Language / Pagination / Format
        ['name' => 'group', 'label' => 'Informations bibliographiques'],
        ['name' => 'date', 'table' => 'table:dates'],
        ['name' => 'language', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],
        ['name' => 'volume'], // n° de tome
        ['name' => 'pagination'],
        ['name' => 'format'],
        ['name' => 'doi'],

        // Event / Degree
        ['name' => 'group', 'label' => 'Congrès et diplômes'],
        // ['name' => 'event'], // date de soutenance

        // numéro de thèse
        // numéro de promotion
        // nom de promotion

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
//         ['name' => 'creation'],
//         ['name' => 'lastupdate'],
    ]
];
// @formatter:on