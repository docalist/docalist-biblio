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
 * Décrit un chapitre particulier d'un livre.
 *
 * - Une division d’un livre ou d'une loi.
 *   @see http://fr.wikipedia.org/wiki/Chapitre
 *
 * - A chapter is one of the main divisions of a piece of writing of relative
 *   length, such as a book of prose, poetry, or law. In each case, chapters
 *   can be numbered or titled or both.
 *   @see http://en.wikipedia.org/wiki/Chapter_(books)
 *
 * Principales caractéristiques :
 * - a un parent de type Book
 * - a une pagination de type "page de début - page de fin"
 */
// @formatter:off
return [
    'name' => 'book-chapter',
    'label' => __('Chapitre de livre', 'docalist-biblio'),
    'description' => __('Un chapitre extrait d\'un livre publié.', 'docalist-biblio'),
    'fields' => [

        // Type, Genre, Media
        // ['name' => 'group', 'label' => 'Nature du document'],
        // ['name' => 'media', 'table' => 'medias'],

        // Title, OtherTitle, Translation
        ['name' => 'group', 'label' => 'Titres'],
        ['name' => 'title'],
        ['name' => 'othertitle', 'table' => 'titles'],
        ['name' => 'translation', 'table' => 'languages'],

        // Author, Organisation
        ['name' => 'group', 'label' => 'Auteurs'],
        ['name' => 'author', 'table' => 'roles-author', 'format' => 'fmt1'],
        ['name' => 'organisation', 'table' => 'countries', 'table2' => 'roles-organisation'],

        // Journal, Issn, Volume, Issue
        // ['name' => 'group', 'label' => 'Journal / Périodique'],
        // ['name' => 'journal'],
        // ['name' => 'issn'],
        // ['name' => 'volume'],
        // ['name' => 'issue'],

        // Date / Language / Pagination / Format
        ['name' => 'group', 'label' => 'Informations bibliographiques'],
        ['name' => 'date'],
        ['name' => 'language', 'table' => 'languages'],
        ['name' => 'pagination'],
        ['name' => 'format'],
        ['name' => 'doi'],

        // Editor / Collection / Edition / Isbn
        // ['name' => 'group', 'label' => 'Informations éditeur'],
        // ['name' => 'editor', 'table' => 'countries'],
        // ['name' => 'collection'],
        // ['name' => 'edition'],
        // ['name' => 'isbn'],

        // Event / Degree
        // ['name' => 'group', 'label' => 'Congrès et diplômes'],
        // ['name' => 'event'],
        // ['name' => 'degree'],

        // Topic / Abstract / Note
        ['name' => 'group', 'label' => 'Indexation et résumé'],
        ['name' => 'topic'],
        ['name' => 'abstract', 'table' => 'languages'],
        ['name' => 'note', 'table' => 'notes'],

        // Liens et relations
        ['name' => 'group', 'label' => 'Liens et relations'],
        ['name' => 'link', 'table' => 'links'],
        ['name' => 'relations', 'table' => 'relations'],

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