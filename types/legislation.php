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
 * Décrit un texte législatif ou réglementaire : loi, projet de loi, proposition
 * de loi, ordonnance, décret, arrêté, circulaire, convention, décision, code
 * législatif, code réglementaire, note de service, etc.
 *
 * @see http://www.snphar.com/data/A_la_une/phar27/legislation27.pdf
 *
 * Principales caractéristiques :
 * - l'auteur est un député, un sénateur, ou le premier ministre
 * - a une date de dépôt
 * - est publié ou nom au bo, au jo, etc.
 */
// @formatter:off
return [
    'name' => 'legislation',
    'label' => __('Législation', 'docalist-biblio'),
    'description' => __('Un texte législatif ou réglementaire.', 'docalist-biblio'),
    'fields' => [

        // Type, Genre, Media
        ['name' => 'group', 'label' => 'Nature du document'],
        ['name' => 'genre', 'table' => 'table:genres-legislation'],
        ['name' => 'media', 'table' => 'thesaurus:medias'],

        // Title, OtherTitle, Translation
        ['name' => 'group', 'label' => 'Titres'],
        ['name' => 'title'],

        // Author, Organisation
        ['name' => 'group', 'label' => 'Auteurs'],
        ['name' => 'author', 'table' => 'thesaurus:marc21-relators_fr', 'format' => 'fmt1'],
        ['name' => 'organisation', 'table' => 'table:ISO-3166-1_alpha2_fr', 'table2' => 'thesaurus:marc21-relators_fr'],

        // Journal, Issn, Volume, Issue
        ['name' => 'group', 'label' => 'Journal / Périodique'],
        ['name' => 'journal'],
        ['name' => 'number', 'table' => 'table:numbers'],
        ['name' => 'issn'],
        ['name' => 'volume'],
        ['name' => 'issue'],
        ['name' => 'edition'],

        // Date / Language / Pagination / Format
        ['name' => 'group', 'label' => 'Informations bibliographiques'],
        ['name' => 'date', 'table' => 'table:dates'],
        ['name' => 'language', 'table' => 'table:ISO-639-2_alpha3_EU_fr'], // fre par défaut
        ['name' => 'extent', 'table' => 'table:extent'],
        ['name' => 'format', 'table' => 'thesaurus:format'],

        // numéro de texte officiel

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