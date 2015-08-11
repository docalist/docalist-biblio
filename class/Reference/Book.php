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
 */
namespace Docalist\Biblio\Reference;

use Docalist\Biblio\Reference;
use Docalist\Schema\Schema;

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
 */
class Book extends Reference {
    static protected function loadSchema() {
        // Récupère les champs d'une référence standard
        $fields = parent::loadSchema()['fields'];

        // Supprime les champs qu'on n'utilise pas
        unset($fields['journal']);

        // Personnalise les tables, les libellés, les description, etc.
        // todo

        // Contruit notre schéma
        return [
            'name' => 'book',
            'label' => __('Livre', 'docalist-biblio'),
            'description' => __('Un livre publié par un éditeur.', 'docalist-biblio'),
            'fields' => $fields,
        ];
    }

    static public function editGrid() {
        return new Schema([
            'label' => __('Formulaire de saisie', 'docalist-biblio'),
            'description' => __("Grille de saisie d'un livre.", 'docalist-biblio'),
            'fields' => [
                // Type, Genre, Media
                'group1' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Nature du document'],
                'genre',
                'media',

                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Titres'],
                'title',
                'othertitle',
                'translation',

                // Author, Organisation
                'group3' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Auteurs'],
                'author',
                'organisation',

                // Date / Language / Pagination / Format
                'group4' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations bibliographiques'],
                'date',
                'language',
                'number',
                'extent',
                'format',

                // Editor / Collection / Edition
                'group5' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations éditeur'],
                'editor',
                'collection',
                'edition',

                // Event
                'group6' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Congrès et diplômes'],
                'event',

                // Topic / Abstract / Note
                'group7' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Indexation et résumé'],
                'topic',
                'content',

                // // Liens et relations
                'group8' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Liens et relations'],
                'link',
                'relation',

                // Ref / Owner / Creation / Lastupdate
                'group9' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations de gestion'],
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
            'name' => 'content',
            'label' => __('Affichage long', 'docalist-biblio'),
            'description' => __("Grille utilisée pour l'affichage détaillé d'une notice complète de type livre.", 'docalist-biblio'),
            'fields' => [

                // Champs affichés
                'group1' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Champs affichés', 'docalist-biblio'), 'format' => '<tr><th style="width: 200px; text-align: right; vertical-align: top">%label : </th><td>%content</td></tr>', 'before' => '<table>', 'after' => '</table>' ],
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
                'organisation' => [
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
                'event',
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
                'imported',
                'errors' => [
                    'sep' => ', ',
                ],

                // Champs non affichés
                'group2' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Champs non affichés', 'docalist-biblio') ],
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
                'group3' => [ 'label' => __('Premier auteur', 'docalist-biblio'), 'setup' => '', 'format' => '%content', 'type' => 'Docalist\Biblio\Type\Group' ],
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
                'group4' => [ 'label' => __('Genre et support entre parenthèses', 'docalist-biblio'), 'before' => ' <span style="text-transform: lowercase">(', 'format' => '%content', 'after' => ')</span>', 'sep' => ', ', 'type' => 'Docalist\Biblio\Type\Group' ],
                'genre' => [
                    'sep' => ', ',
                ],
                'media' => [
                    'sep' => ', ',
                ],

                // Infos éditeur
                'group6' => [ 'label' => __('Infos éditeur', 'docalist-biblio'), 'before' => "<p>\r\n<i>Publié chez : </i>", 'format' => '%content', 'after' => '</p>', 'sep' => ', ', 'type' => 'Docalist\Biblio\Type\Group' ],
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
                'ref',
                'parent',
                'title',
                'status',
                'creation',
                'lastupdate',
                'password',
                'posttype',
                'type',
                'organisation',
                'othertitle',
                'translation',
                'language',
                'format',
                'event',
                'relation',
                'owner',
                'imported',
                'errors',
            ]
        ]);
    }
*/
}