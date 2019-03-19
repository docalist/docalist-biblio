<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Entity;

use Docalist\Biblio\Entity\ReferenceEntity;
use Docalist\Data\GridBuilder\EditGridBuilder;

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
class BookChapterEntity extends ReferenceEntity
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
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

    /**
     * {@inheritDoc}
     */
    public static function getEditGrid()
    {
        $builder = new EditGridBuilder(self::class);

        $builder->setProperty('stylesheet', 'docalist-biblio-edit-reference');

        $builder->addGroup(
            __('Titres', 'docalist-biblio'),
            'title,othertitle,translation'
        );
        $builder->addGroup(
            __('Auteurs', 'docalist-biblio'),
            'author,corporation'
        );
        $builder->addGroup(
            __('Informations bibliographiques', 'docalist-biblio'),
            'date,language,number,extent,format'
        );
        $builder->addGroup(
            __('Indexation et résumé', 'docalist-biblio'),
            'topic,content'
        );
        $builder->addGroup(
            __('Liens et relations', 'docalist-biblio'),
            'link,relation'
        );
        $builder->addGroup(
            __('Informations de gestion', 'docalist-biblio'),
            'type,ref,source',
            'collapsed'
        );

        $builder->setDefaultValues([
            'othertitle' => [
                ['type' => 'common'],                   // Titre de l'ensemble
            ],
            'translation' => [
                ['type' => 'fre'],                      // Traduction en français
            ],
            'author' => [
                ['role' => 'aut'],                      // Rôle auteur
            ],
            'corporation' => [
                ['country' => 'FR', 'role' => 'aut'],   // Pays France, rôle auteur
            ],
            'number' => [
                ['type' => 'isbn'],                     // ISBN
                ['type' => 'ean'],                      // EAN
                ['type' => 'official-number'],          // Numéro officiel
                ['type' => 'part-no'],                  // Numéro de tome
            ],
            'date' => [
                ['type' => 'publication'],              // Date de publication
                ['type' => 'print'],                    // Date de la version imprimée
            ],
            'language' => [
                'fre',                                  // En français
            ],
            'extent' => [
                ['type' => 'page-range'],               // Pages début-fin
                ['type' => 'pages'],                    // Nombre de pages
            ],
            'content' => [
                ['type' => 'abstract'],                 // Résumé
            ],
            'link' => [
                ['type' => 'D04'],                      // Accès à la version en ligne
            ],
            'relation' => [
                ['type' => 'is-part-of'],               // Fait partie de
            ],
        ]);

        return $builder->getGrid();
    }
}
