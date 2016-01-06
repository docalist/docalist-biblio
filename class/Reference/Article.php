<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Reference;

use Docalist\Biblio\Reference;
use Docalist\Schema\Schema;

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
 */
class Article extends Reference {
    static public function loadSchema() {
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

//         // Récupère les champs d'une référence standard
//         $fields = parent::loadSchema()['fields'];

//         // Supprime les champs qu'on n'utilise pas
//         unset($fields['editor']);
//         unset($fields['collection']);

//         // Personnalise les tables, les libellés, les description, etc.
//         // todo

//         // Contruit notre schéma
//         return [
//             'name' => 'article',
//             'label' => __('Article de périodique', 'docalist-biblio'),
//             'description' => __('Un article de presse publié dans un numéro de périodique.', 'docalist-biblio'),
//             'fields' => $fields,
//         ];
    }

    static public function editGrid() {
        return new Schema([
            'label' => __('Formulaire de saisie', 'docalist-biblio'),
            'description' => __("Grille de saisie d'un article.", 'docalist-biblio'),
            'fields' => [

                // Nature du document
                'group1' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Nature du document', 'docalist-biblio') ],
                'genre',
                'media',

                // Titres
                'group2' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Titres', 'docalist-biblio') ],
                'title',
                'othertitle',
                'translation',
                'event',

                // Auteurs
                'group3' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Auteurs', 'docalist-biblio') ],
                'author',
                'organisation',

                // Journal / Périodique
                'group4' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Journal / Périodique', 'docalist-biblio') ],
                'journal',
                'number',
                'date',
                'edition',

                // Informations bibliographiques
                'group5' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Informations bibliographiques', 'docalist-biblio') ],
                'language',
                'extent',
                'format',

                // Indexation et résumé
                'group6' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Indexation et résumé', 'docalist-biblio') ],
                'topic',
                'content',

                // Liens et relations
                'group7' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Liens et relations', 'docalist-biblio') ],
                'link',
                'relation',

                // Informations de gestion
                'group8' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Informations de gestion', 'docalist-biblio') ],
                'type',
                'ref',
                'owner',
/*
posttype
creation
lastupdate
password
parent
slug
imported
errors
*/
            ]
        ]);
    }
/*
    static public function contentGrid() {
        return new Schema([
            'label' => __('Affichage long', 'docalist-biblio'),
            'description' => __("Affichage long d'un article.", 'docalist-biblio'),
            'name' => 'content',
            'fields' => [

                // Champs affichés
                'group1' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Champs affichés', 'docalist-biblio'), 'format' => '<tr><th style="width: 200px; text-align: right; vertical-align: top">%label : </th><td>%content</td></tr>', 'before' => '<table>', 'after' => '</table>' ],
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
                'organisation' => [
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
                'group2' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Champs non affichés', 'docalist-biblio') ],
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
                'group3' => [ 'label' => __('Premier auteur', 'docalist-biblio'), 'setup' => '', 'format' => '%content', 'type' => 'Docalist\Biblio\Type\Group' ],
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
                'group4' => [ 'label' => __('Genre et support entre parenthèses', 'docalist-biblio'), 'before' => ' <span style="text-transform: lowercase">(', 'format' => '%content', 'after' => ')</span>', 'sep' => ', ', 'type' => 'Docalist\Biblio\Type\Group' ],
                'genre' => [
                    'sep' => ', ',
                ],
                'media' => [
                    'sep' => ', ',
                ],

                // Groupe "in"
                'group6' => [ 'label' => __('Groupe "in"', 'docalist-biblio'), 'before' => "<p>\r\n<i>in : </i>", 'format' => '%content', 'after' => '.</p>', 'sep' => ', ', 'type' => 'Docalist\Biblio\Type\Group' ],
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
                'group7' => [ 'label' => __('Résumé, mots-clés et premier lien', 'docalist-biblio'), 'format' => '<p>%content</p>', 'type' => 'Docalist\Biblio\Type\Group' ],
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
                'group2' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Champs non affichés', 'docalist-biblio') ],
                'title',
                'translation',
                'othertitle',
                'organisation',
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