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
 * Livre.
 *
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
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class BookEntity extends ReferenceEntity
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema()
    {
        return [
            'name' => 'book',
            'label' => __('Livre', 'docalist-biblio'),
            'description' => __('Un livre publié par un éditeur.', 'docalist-biblio'),
            'fields' => [
                'journal' => [
                    'unused' => true
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getEditGrid()
    {
        $builder = new EditGridBuilder(self::class);

        $builder->addGroup(
            __('Nature du document', 'docalist-biblio'),
            'genre,media'
        );
        $builder->addGroup(
            __('Titres', 'docalist-biblio'),
            'title,othertitle,translation,context'
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
            __('Informations éditeur', 'docalist-biblio'),
            'editor,collection,edition'
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
            'media' => [
                'PA',                                   // Imprimés divers
                'EB',                                   // Document à télécharger ou en ligne
            ],
            'othertitle' => [
                ['type' => 'complement'],               // Complément du titre
            ],
            'translation' => [
                ['type' => 'fre'],                      // Traduction en français
            ],
            'author' => [
                ['role' => 'aut'],                      // Rôle auteur
            ],
            'corporation' => [
                ['country' => 'FR', 'role' => 'aut'],   // Pays France, rôle auteur
                ['country' => 'FR', 'role' => 'spn'],   // Pays France, rôle commanditaire
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
                ['type' => 'pages'],                    // Nombre de pages
            ],
            'editor' => [
                ['country' => 'FR', 'role' => 'pbl'],   // Pays France, rôle éditeur
            ],
            'content' => [
                ['type' => 'publisher-abstract'],       // Présentation de l'éditeur
            ],
            'link' => [
                ['type' => 'D04'],                      // Accès à la version en ligne
                ['type' => 'D06'],                      // Site de l'éditeur sur le document
            ],
            'relation' => [
                ['type' => 'new-edition-of'],           // Nouvelle édition de
            ],
        ]);

        return $builder->getGrid();
    }

/*
    static public function contentGrid() {
        return new Schema([
            'name' => 'content',
            'label' => __('Affichage long', 'docalist-biblio'),
            'description' => __("Grille utilisée pour l'affichage détaillé d'une notice complète de type livre.", 'docalist-biblio'),
            'fields' => [

                // Champs affichés
                'group1' => [ 'type' => 'Docalist\Data\Type\Group', 'label' => __('Champs affichés', 'docalist-biblio'), 'format' => '<tr><th style="width: 200px; text-align: right; vertical-align: top">%label : </th><td>%content</td></tr>', 'before' => '<table>', 'after' => '</table>' ],
                'ref',
                'parent',
                'status',
                'creation',
                'lastupdate',
                'password',
                'posttype',
                'type',
                'genre' => [
                    'sep' => ', ',
                ],
                'media' => [
                    'sep' => ', ',
                ],
                'author' => [
                    'explode' => true,
                    'format' => 'f n',
                    'sep' => ', ',
                ],
                'corporation' => [
                    'format' => 'n (a), t, c, r',
                    'sep' => ' ; ',
                    'explode' => true,
                ],
                'othertitle' => [
                    'explode' => true,
                    'format' => 'v',
                    'sep' => ', ',
                ],
                'translation' => [
                    'format' => 't (l)',
                    'sep' => ', ',
                ],
                'date' => [
                    'explode' => true,
                    'format' => 'date',
                    'sep' => ', ',
                ],
                'number' => [
                    'format' => 'v',
                    'sep' => ', ',
                    'explode' => true,
                ],
                'language' => [
                    'sep' => ', ',
                ],
                'extent' => [
                    'format' => 'v',
                    'sep' => ', ',
                    'explode' => true,
                ],
                'format' => [
                    'sep' => ', ',
                ],
                'editor' => [
                    'explode' => true,
                    'format' => 'n, t, c, r',
                    'sep' => ', ',
                ],
                'edition' => [
                    'sep' => ', ',
                ],
                'collection' => [
                    'sep' => ', ',
                ],
                'topic' => [
                    'explode' => true,
                    'format' => 'v',
                    'sep' => ', ',
                ],
                'content' => [
                    'explode' => true,
                    'format' => 'v',
                    'sep' => ', ',
                ],
                'link' => [
                    'explode' => true,
                    'format' => 'link',
                    'sep' => ', ',
                ],
                'relation' => [
                    'explode' => true,
                    'format' => 'title-link',
                    'sep' => ', ',
                ],
                'owner' => [
                    'sep' => ', ',
                ],

                // Champs non affichés
                'group2' => [ 'type' => 'Docalist\Data\Type\Group', 'label' => __('Champs non affichés', 'docalist-biblio') ],
                'title',
            ]
        ]);
    }

    static public function excerptGrid() {
        return new Schema([
            'label' => __('Affichage court', 'docalist-biblio'),
            'description' => __("Affichage court d'un livre.", 'docalist-biblio'),
            'name' => 'excerpt',
            'fields' => [

                // Premier auteur
                'group3' => [ 'label' => __('Premier auteur', 'docalist-biblio'), 'setup' => '', 'format' => '%content', 'type' => 'Docalist\Data\Type\Group' ],
                'author' => [
                    'format' => 'f n',
                    'sep' => ', ',
                    'limit' => '1',
                    'ellipsis' => '<i> et al.</i>',
                    'prefix' => '<b>',
                    'suffix' => '</b>',
                    'before' => 'Livre de ',
                ],

                // Genre et support entre parenthèses
                'group4' => [ 'label' => __('Genre et support entre parenthèses', 'docalist-biblio'), 'before' => ' <span style="text-transform: lowercase">(', 'format' => '%content', 'after' => ')</span>', 'sep' => ', ', 'type' => 'Docalist\Data\Type\Group' ],
                'genre' => [
                    'sep' => ', ',
                ],
                'media' => [
                    'sep' => ', ',
                ],

                // Infos éditeur
                'group6' => [ 'label' => __('Infos éditeur', 'docalist-biblio'), 'before' => "<p>\r\n<i>Publié chez : </i>", 'format' => '%content', 'after' => '</p>', 'sep' => ', ', 'type' => 'Docalist\Data\Type\Group' ],
                'editor' => [
                    'before' => '<b>',
                    'after' => '</b>',
                    'format' => 'n, t, c, r',
                    'sep' => ', ',
                ],
                'number' => [
                    'format' => 'format',
                    'sep' => ', ',
                ],
                'edition' => [
                    'sep' => ', ',
                ],
                'date' => [
                    'format' => 'date',
                    'sep' => ', ',
                ],
                'extent' => [
                    'format' => 'format',
                    'sep' => ', ',
                ],
                'collection' => [
                    'sep' => ', ',
                ],

                // Résumé, mots-clés et premier lien
                'group7' => [ 'label' => __('Résumé, mots-clés et premier lien', 'docalist-biblio'), 'format' => '<p>%content</p>', 'type' => 'Docalist\Data\Type\Group' ],
                'content' => [
                    'format' => 'v',
                    'sep' => ', ',
                    'limit' => '1',
                    'prefix' => '<blockquote>',
                    'suffix' => '</blockquote>',
                ],
                'topic' => [
                    'format' => 'v',
                    'sep' => ', ',
                    'before' => '<i>Mots-clés : </i>',
                ],
                'link' => [
                    'format' => 'link',
                    'sep' => ', ',
                    'explode' => true,
                    'limit' => '1',
                ],

                // Champs non affichés
                'group2' => [ 'type' => 'Docalist\Data\Type\Group', 'label' => __('Champs non affichés', 'docalist-biblio') ],
                'ref',
                'parent',
                'title',
                'status',
                'creation',
                'lastupdate',
                'password',
                'posttype',
                'type',
                'corporation',
                'othertitle',
                'translation',
                'language',
                'format',
                'relation',
                'owner',
            ]
        ]);
    }
*/
}
