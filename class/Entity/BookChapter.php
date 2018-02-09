<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Entity;

use Docalist\Biblio\Entity\Reference;

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
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class BookChapter extends Reference
{
    public static function loadSchema()
    {
        return [
            'name' => 'book-chapter',
            'label' => __('Chapitre de livre', 'docalist-biblio'),
            'description' => __('Un chapitre extrait d\'un livre publié.', 'docalist-biblio'),
            'fields' => [
                'genre'         => ['unused' => true],
                'media'         => ['unused' => true],
                'journal'       => ['unused' => true],
                'editor'        => ['unused' => true],
                'collection'    => ['unused' => true],
                'edition'       => ['unused' => true],
                'context'       => ['unused' => true],
            ],
        ];
    }

    public static function getEditGrid()
    {
        return static::buildEditGrid([
            __('Titres', 'docalist-biblio')                         => 'title,othertitle,translation',
            __('Auteurs', 'docalist-biblio')                        => 'author,corporation',
            __('Informations bibliographiques', 'docalist-biblio')  => 'date,language,number,extent,format',
            __('Indexation et résumé', 'docalist-biblio')           => 'topic,content',
            __('Liens et relations', 'docalist-biblio')             => 'link,relation',
            __('Informations de gestion', 'docalist-biblio')        => '-,type,ref,owner',
        ]);
    }
}
