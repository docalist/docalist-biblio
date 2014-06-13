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
 * Décrit un livre.
 *
 * - Un livre est un document écrit formant une unité et conçu comme tel
 *   composé de pages en papier ou en carton reliées les unes aux autres.
 *   @see http://fr.wikipedia.org/wiki/Livre_(document)
 *
 * - A book is a set of written, printed, illustrated, or blank sheets, made
 *   of ink, paper, parchment, or other materials, usually fastened together
 *   to hinge at one side
 *   @see http://en.wikipedia.org/wiki/Book
 *
 * Principales caractéristiques :
 * - a un éditeur (un diffuseur, etc.)
 * - a un isbn
 * - a un ou plusieurs auteurs physiques
 * - a un ou plusieurs auteurs moraux
 * - pagination de type "nombre de pages"
 */
// @formatter:off
return [
    'name' => 'book',
    'label' => __('Livre', 'docalist-biblio'),
    'description' => __('Un livre publié par un éditeur.', 'docalist-biblio'),
    'fields' => [

        // Type, Genre, Media
        ['name' => 'group', 'label' => 'Nature du document'],
        ['name' => 'genre', 'table' => 'table:genres-book'],
        ['name' => 'media', 'table' => 'thesaurus:medias'],

        // Title, OtherTitle, Translation
        ['name' => 'group', 'label' => 'Titres'],
        ['name' => 'title'],
        ['name' => 'othertitle', 'table' => 'table:titles'],
        ['name' => 'translation', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],

        // Author, Organisation
        ['name' => 'group', 'label' => 'Auteurs'],
        ['name' => 'author', 'table' => 'thesaurus:marc21-relators_fr', 'format' => 'fmt1'],
        ['name' => 'organisation', 'table' => 'table:ISO-3166-1_alpha2_fr', 'table2' => 'thesaurus:marc21-relators_fr'],

        // Date / Language / Pagination / Format
        ['name' => 'group', 'label' => 'Informations bibliographiques'],
        ['name' => 'date', 'table' => 'table:dates'],
        ['name' => 'language', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],
        ['name' => 'volume'], // n° de tome
        ['name' => 'extent', 'table' => 'table:extent'],
        ['name' => 'format', 'table' => 'thesaurus:format'],
        ['name' => 'doi'],

        // Editor / Collection / Edition / Isbn
        ['name' => 'group', 'label' => 'Informations éditeur'],
        ['name' => 'editor', 'table' => 'table:ISO-3166-1_alpha2_fr', 'table2' => 'thesaurus:marc21-relators_fr'],
        ['name' => 'collection'],
        ['name' => 'edition'],
        ['name' => 'isbn'],

        // Event / Degree
        ['name' => 'group', 'label' => 'Congrès et diplômes'],
        ['name' => 'event'], // acte de colloque
        ['name' => 'degree'],

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