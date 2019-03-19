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
 * Article.
 *
 * Décrit un article de presse publié dans un numéro particulier d'un
 * périodique.
 *
 * - Un article est un texte qui relate un événement, présente des faits ou
 *   expose un point de vue. Il s'appuie pour cela sur différentes sources
 *   d'information orales ou écrites.
 *   @see http://fr.wikipedia.org/wiki/Article_de_presse
 *
 * - An article is a written work published in a print or electronic medium.
 *   It may be for the purpose of propagating the news, research results,
 *   academic analysis or debate.
 *   @see http://en.wikipedia.org/wiki/Article_(publishing)
 *
 * Principales caractéristiques :
 * - a un parent de type Issue
 * - écrit par un ou plusieurs auteurs physiques
 * - pas d'auteur organisme
 * - pagination de type "page de début - page de fin"
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ArticleEntity extends ReferenceEntity
{
    // Un type de référence ne doit pas créer de champs, juste paramétrer les champs existant ou les marquer "unused".

    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'article',
            'label' => __('Article de périodique', 'docalist-biblio'),
            'description' => __('Un article de presse publié dans un numéro de périodique.', 'docalist-biblio'),
            'fields' => [
                'editor' => [
                    'unused' => true
                ],
                'collection' => [
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

        $builder->setProperty('stylesheet', 'docalist-biblio-edit-reference');

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
            __('Journal / Périodique', 'docalist-biblio'),
            'journal,number,date,edition'
        );
        $builder->addGroup(
            __('Informations bibliographiques', 'docalist-biblio'),
            'language,extent,format'
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
                ['type' => 'focus'],                    // Titre du dossier
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
                ['type' => 'issn-p'],                   // ISSN de la version imprimée
                ['type' => 'issn-e'],                   // ISSN de la version en ligne
                ['type' => 'volume-no'],                // Numéro de volume
                ['type' => 'issue-no'],                 // Numéro de fascicule
                ['type' => 'special-issue-no'],         // Numéro de hors série
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
            ],
            'content' => [
                ['type' => 'publisher-abstract'],       // Présentation de l'éditeur
            ],
            'link' => [
                ['type' => 'doi'],                      // DOI
                ['type' => 'D04'],                      // Accès à la version en ligne
            ],
        ]);

        return $builder->getGrid();
    }

/*
    static public function contentGrid() {
        return new Schema([
            'label' => __('Affichage long', 'docalist-biblio'),
            'description' => __("Affichage long d'un article.", 'docalist-biblio'),
            'name' => 'content',
            'fields' => [

                // Champs affichés
                'group1' => [
                    'type' => 'Docalist\Data\Type\Group', 'label' => __('Champs affichés', 'docalist-biblio'), 'format' => '<tr><th style="width: 200px; text-align: right; vertical-align: top">%label : </th><td>%content</td></tr>', 'before' => '<table>', 'after' => '</table>' ],
                'genre' => [
                    'sep' => ', ',
                ],
                'media' => [
                    'sep' => ', ',
                ],
                'othertitle' => [
                    'explode' => true,
                    'format' => 'v',
                    'sep' => ', ',
                ],
                'translation' => [
                    'explode' => true,
                    'format' => 't',
                    'sep' => ', ',
                ],
                'author' => [
                    'explode' => true,
                    'format' => 'f n (r)',
                    'sep' => ', ',
                ],
                'corporation' => [
                    'explode' => true,
                    'format' => 'n (a), t, c, r',
                    'sep' => ', ',
                ],
                'journal',
                'number' => [
                    'explode' => true,
                    'format' => 'v',
                    'sep' => ', ',
                ],
                'date' => [
                    'explode' => true,
                    'format' => 'date',
                    'sep' => ', ',
                ],
                'extent' => [
                    'format' => 'v',
                    'sep' => ', ',
                    'explode' => true,
                ],
                'language' => [
                    'sep' => ', ',
                ],
                'format' => [
                    'sep' => ', ',
                ],
                'edition' => [
                    'sep' => ', ',
                ],
                'content' => [
                    'explode' => true,
                    'format' => 'v',
                    'sep' => ', ',
                ],
                'topic' => [
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
                    'format' => 'ref',
                    'sep' => ', ',
                ],
                'type',
                'owner' => [
                    'sep' => ', ',
                ],
                'ref',

                // Champs non affichés
                'group2' => [ 'type' => 'Docalist\Data\Type\Group', 'label' => __('Champs non affichés', 'docalist-biblio') ],
                'title',
            ]
        ]);
    }

    static public function excerptGrid() {
        return new Schema([
            'label' => __('Affichage court', 'docalist-biblio'),
            'description' => __("Affichage court d'un article.", 'docalist-biblio'),
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
                    'before' => 'Article de ',
                ],

                // Genre et support entre parenthèses
                'group4' => [ 'label' => __('Genre et support entre parenthèses', 'docalist-biblio'), 'before' => ' <span style="text-transform: lowercase">(', 'format' => '%content', 'after' => ')</span>', 'sep' => ', ', 'type' => 'Docalist\Data\Type\Group' ],
                'genre' => [
                    'sep' => ', ',
                ],
                'media' => [
                    'sep' => ', ',
                ],

                // Groupe "in"
                'group6' => [ 'label' => __('Groupe "in"', 'docalist-biblio'), 'before' => "<p>\r\n<i>in : </i>", 'format' => '%content', 'after' => '.</p>', 'sep' => ', ', 'type' => 'Docalist\Data\Type\Group' ],
                'journal' => [
                    'before' => '<b>',
                    'after' => '</b>',
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
                'title',
                'translation',
                'othertitle',
                'corporation',
                'type',
                'ref',
                'owner',
                'format',
                'language',
                'relation',
            ]
        ]);
    }
*/
}
