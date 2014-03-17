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
 * Décrit un site web.
 *
 * - Un site web est un ensemble de pages web reliées entre elles et
 *   accessible à une adresse web.
 *   @see http://fr.wikipedia.org/wiki/Site_web
 *
 * - A website is a set of related web pages served from a single web domain.
 *   A website is hosted on at least one web server, accessible via a network
 *   such as the Internet or a private local area network through an URL.
 *   @see http://en.wikipedia.org/wiki/Web_site
 *
 * Principales caractéristiques :
 * - a une URL
 * - a un organisme ou une personne auteur
 */
// @formatter:off
return [
    'name' => 'website',
    'label' => __('Site web', 'docalist-biblio'),
    'description' => __('Un site web.', 'docalist-biblio'),
    'fields' => [

        // Type, Genre, Media
        ['name' => 'group', 'label' => 'Nature du document'],
        ['name' => 'genre', 'table' => 'table:genres-website'],
        // ['name' => 'media', 'table' => 'table:medias'],

        // Title, OtherTitle, Translation
        ['name' => 'group', 'label' => 'Titres'],
        ['name' => 'title'],
        ['name' => 'othertitle', 'table' => 'table:titles'],
        ['name' => 'translation', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],

        // Author, Organisation
        ['name' => 'group', 'label' => 'Auteurs'],
        ['name' => 'author', 'table' => 'thesaurus:marc21-relators_fr', 'format' => 'fmt1'],
        ['name' => 'organisation', 'table' => 'table:ISO-3166-1_alpha2_fr', 'table2' => 'thesaurus:marc21-relators_fr'],

        // Journal, Issn, Volume, Issue
        ['name' => 'group', 'label' => 'Journal / Périodique'],
        ['name' => 'journal'],
        ['name' => 'issn'],
        ['name' => 'volume'],
        ['name' => 'issue'],

        // Date / Language / Pagination / Format
        ['name' => 'group', 'label' => 'Informations bibliographiques'],
        ['name' => 'date'],
        ['name' => 'language', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],
        ['name' => 'pagination'],
        ['name' => 'format'],
        ['name' => 'doi'],

        // Editor / Collection / Edition / Isbn
        ['name' => 'group', 'label' => 'Informations éditeur'],
        ['name' => 'editor', 'table' => 'table:ISO-3166-1_alpha2_fr'],
        ['name' => 'collection'],
        ['name' => 'edition'],
        ['name' => 'isbn'],

        // Event / Degree
        ['name' => 'group', 'label' => 'Congrès et diplômes'],
        ['name' => 'event'],
        ['name' => 'degree'],

        // Topic / Abstract / Note
        ['name' => 'group', 'label' => 'Indexation et résumé'],
        ['name' => 'topic', 'table' => 'table:topics'],
        ['name' => 'abstract', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],
        ['name' => 'note', 'table' => 'table:notes'],

        // Liens et relations
        ['name' => 'group', 'label' => 'Liens et relations'],
        ['name' => 'link', 'table' => 'table:links'],
        ['name' => 'relations', 'table' => 'table:relations'],

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