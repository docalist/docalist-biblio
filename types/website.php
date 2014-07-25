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
        ['name' => 'group1', 'label' => 'Nature du document'],
        ['name' => 'genre', 'table' => 'thesaurus:genres'],

        // Title, OtherTitle, Translation
        ['name' => 'group2', 'label' => 'Titres'],
        ['name' => 'title'],
        ['name' => 'othertitle', 'table' => 'table:titles'],
        ['name' => 'translation', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],

        // Author, Organisation
        ['name' => 'group3', 'label' => 'Auteurs'],
        ['name' => 'author', 'table' => 'thesaurus:marc21-relators_fr', 'format' => 'fmt1'],
        ['name' => 'organisation', 'table' => 'table:ISO-3166-1_alpha2_fr', 'table2' => 'thesaurus:marc21-relators_fr'],

        // Journal, Number
        ['name' => 'group4', 'label' => 'Journal / Périodique'],
        ['name' => 'journal'],
        ['name' => 'number', 'table' => 'table:numbers'],

        // Date / Language / Pagination / Format
        ['name' => 'group5', 'label' => 'Informations bibliographiques'],
        ['name' => 'date', 'table' => 'table:dates'],
        ['name' => 'language', 'table' => 'table:ISO-639-2_alpha3_EU_fr'],
        ['name' => 'extent', 'table' => 'table:extent'],
        ['name' => 'format', 'table' => 'thesaurus:format'],

        // Editor / Collection / Edition
        ['name' => 'group6', 'label' => 'Informations éditeur'],
        ['name' => 'editor', 'table' => 'table:ISO-3166-1_alpha2_fr', 'table2' => 'thesaurus:marc21-relators_fr'],
        ['name' => 'collection'],
        ['name' => 'edition'],

        // Event
        ['name' => 'group7', 'label' => 'Congrès et diplômes'],
        ['name' => 'event'],

        // Topic / Abstract / Note
        ['name' => 'group8', 'label' => 'Indexation et résumé'],
        ['name' => 'topic', 'table' => 'table:topics'],
        ['name' => 'content', 'table' => 'table:content'],

        // Liens et relations
        ['name' => 'group9', 'label' => 'Liens et relations'],
        ['name' => 'link', 'table' => 'table:links'],
        ['name' => 'relation', 'table' => 'table:relations'],

        // Ref / Owner / Creation / Lastupdate
        ['name' => 'group10', 'label' => 'Informations de gestion'],
        ['name' => 'type'],
        ['name' => 'ref'],
        ['name' => 'owner'],
    ]
];
// @formatter:on