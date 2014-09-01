<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Entity;

use Docalist\Biblio\Reference;

/**
 * Chapitre de livre.
 *
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
class BookChapter extends Reference {
    static protected function loadSchema() {
        $fields = parent::loadSchema()['fields'];

        // @formatter:off
        return [
            'name' => 'book-chapter',
            'label' => __('Chapitre de livre', 'docalist-biblio'),
            'description' => __('Un chapitre extrait d\'un livre publié.', 'docalist-biblio'),
            'fields' => [

                // Title, OtherTitle, Translation
                'group1' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Titres'],
                $fields['title'],
                $fields['othertitle'],
                $fields['translation'],

                // Author, Organisation
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Auteurs'],
                $fields['author'],
                $fields['organisation'],

                // Date / Language / Pagination / Format
                'group3' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations bibliographiques'],
                $fields['date'],
                $fields['language'],
                $fields['number'],
                $fields['extent'],
                $fields['format'],

                // Topic / Abstract / Note
                'group4' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Indexation et résumé'],
                $fields['topic'],
                $fields['content'],

                // // Liens et relations
                'group5' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Liens et relations'],
                $fields['link'],
                $fields['relation'],

                // Ref / Owner / Creation / Lastupdate
                'group6' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations de gestion'],
                $fields['type'],
                $fields['ref'],
                $fields['owner'],
            ]
        ];
        // @formatter:on
    }
}