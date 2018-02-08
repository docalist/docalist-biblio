<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Reference;

use Docalist\Biblio\Reference;

/**
 * Film.
 *
 * Décrit un film.
 *
 * - Film distribué : possède un diffuseur, etc. comme un livre.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Film extends Reference
{
    public static function loadSchema()
    {
        return [
            'name' => 'film',
            'label' => __('Film', 'docalist-biblio'),
            'description' => __('Un film distribué par un éditeur ou un diffuseur.', 'docalist-biblio'),
            'fields' => [
                'title' => [
                    'label'         => __('Titre du film', 'docalist-biblio'),
                    'description'   => __('Titre exact du film.', 'docalist-biblio'),
                ],
                'genre' => [
                    'label'         => __('Type de film', 'docalist-biblio'),
                    'description'   => __(
                        "Type de film : documentaire, fiction, reportage, clip, congrès, film d'animation...",
                        'docalist-biblio'
                    ),
                ],
                'media' => [
                    'label'         => __('Support de diffusion', 'docalist-biblio'),
                    'description'   => __(
                        'Support physique utilisé pour la diffusion du film : dvd, vhs, en ligne...',
                        'docalist-biblio'
                    ),
                ],
                'extent' => [
                    'label'         => __('Durée du film', 'docalist-biblio'),
                    'default'       => [['type' => 'minutes']],
                ],
                'author' => [
                    'label'         => __('Équipe technique et casting', 'docalist-biblio'),
                    'description'   => __(
                        "Personnes qui ont contribué à l'élaboration du film : réalisateur, scénariste, acteurs...",
                        'docalist-biblio'
                    ),
                ],
                'corporation' => [
                    'description'   => __(
                        'Organismes considéré comme auteur, organisme commanditaire, financeur, producteur...',
                        'docalist-biblio'
                    ),
                ],
                'othertitle' => [
                    'description'   => __(
                        "Autres titres du film : variante, sous-titre...",
                        'docalist-biblio'
                    ),
                ],
                'translation' => [
                    'label'         => __("Titre dans d'autres langues", 'docalist-biblio'),
                    'description'   => __(
                        'Titre original, traduction du titre dans une autre langue...',
                        'docalist-biblio'
                    ),
                ],
                'date' => [
                    'label'         => __('Date du film', 'docalist-biblio'),
                    'description'   => __(
                        "Dates au format <code>AAAAMMJJ</code> : sortie, enregistrement, première diffusion...",
                        'docalist-biblio'
                    ),
                ],
                'language' => [
                    'description'   => __(
                        'Langue des pistes audio disponibles.',
                        'docalist-biblio'
                    ),
                ],
                'format' => [
                    'label'         => __('Autres caractéristiques', 'docalist-biblio'),
                    'description'   => __(
                        'Format de la vidéo, sous-titres disponibles, couleur/N&B, VO/VF, bonus...',
                        'docalist-biblio'
                    ),
                ],
                'number' => [
                    'description'   => __(
                        "Numéro de dépôt légal, visa d'exploitation, numéro de saison, épisode, opus...",
                        'docalist-biblio'
                    ),
                ],
                'editor' => [
                    'label'         => __('Editeur ou distributeur', 'docalist-biblio'),
                    'description'   => __(
                        "Organismes chargés de la diffusion, la distribution ou la commercialisation du film.",
                        'docalist-biblio'
                    ),
                ],
                'edition' => [
                    'label'         => __('Mentions de version', 'docalist-biblio'),
                    'description'   => __(
                        "Informations sur la version du film : version longue, version expurgée...",
                        'docalist-biblio'
                    ),
                ],
                'collection' => [
                    'label'         => __('Série ou collection', 'docalist-biblio'),
                    'description'   => __(
                        "Série ou collection dont fait partie le film et numéro éventuel associé.",
                        'docalist-biblio'
                    ),
                ],
                'content' => [
                    'description'   => __(
                        "Synopsis, résumé, critique, avertissement sur le contenu, objectifs pédagogiques...",
                        'docalist-biblio'
                    ),
//                     'editor' => 'integrated',
//                     'fields' => [
//                         'value' => [
//                             'editor' => 'wpeditor-teeny',
//                         ],
//                     ],
                ],
                'link' => [
                    'description'   => __(
                        'Extrait, bande annonce, site du film, site du distributeur, forum...)',
                        'docalist-biblio'
                    ),
                ],
                'journal'   => ['unused' => true],
            ],
        ];
    }

    public static function getEditGrid()
    {
        return static::buildEditGrid([
            __('Type de film et support', 'docalist-biblio')        => 'genre,media',
            __('Titres', 'docalist-biblio')                         => 'title,othertitle,translation',
            __('Distribution et production', 'docalist-biblio')     => 'author,corporation,editor',
            __("Événement à l'origine du film", 'docalist-biblio')  => 'event',
            __('Caractéristiques', 'docalist-biblio')               => 'date,extent,language,edition,format',
            __('Collection et numéros', 'docalist-biblio')          => 'collection,number',
            __('Indexation et résumé', 'docalist-biblio')           => 'topic,content',
            __('Liens et relations', 'docalist-biblio')             => 'link,relation',
            __('Informations de gestion', 'docalist-biblio')        => '-,type,ref,owner',
        ]);
    }

/*
    static public function contentGrid() {
        return new Schema([
            'name' => 'content',
            'label' => __('Affichage long', 'docalist-biblio'),
            'description' => __("Grille utilisée pour l'affichage détaillé d'une notice complète de type film.", 'docalist-biblio'),
            'fields' => [

                // Champs affichés
                'group1' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Champs affichés', 'docalist-biblio'),
                    'format' => '<span style="margin-left: -200px;float: left; font-weight: bold;">%label : </span>%content',
                    'before' => '<p style="margin-left: 200px">',
                    'after' => '</p>',
                    'sep' => '<br />'
                ],
                'genre' => [
                    'sep' => ', '
                ],
                'media' => [
                    'sep' => ', '
                ],
                'title',
                'othertitle' => [
                    'explode' => true,
                    'format' => 'v',
                    'sep' => ', '
                ],
                'translation' => [
                    'format' => 't (l)',
                    'sep' => ', '
                ],
                'author' => [
                    'explode' => true,
                    'format' => 'f n',
                    'sep' => ', '
                ],
                'corporation' => [
                    'format' => 'n (a), t, c, r',
                    'sep' => ' ; ',
                    'explode' => true
                ],
                'editor' => [
                    'explode' => true,
                    'format' => 'n, t, c, r',
                    'sep' => ', '
                ],
                'date' => [
                    'explode' => true,
                    'format' => 'date',
                    'sep' => ', '
                ],
                'edition' => [
                    'sep' => ', '
                ],
                'extent' => [
                    'format' => 'v',
                    'sep' => ', ',
                    'explode' => true
                ],
                'language' => [
                    'sep' => ', '
                ],
                'format' => [
                    'sep' => ', '
                ],
                'collection' => [
                    'sep' => ', '
                ],
                'number' => [
                    'format' => 'v',
                    'sep' => ', ',
                    'explode' => true
                ],
                'event',
                'topic' => [
                    'explode' => true,
                    'format' => 'v',
                    'sep' => ', '
                ],
                'relation' => [
                    'explode' => true,
                    'format' => 'title-link',
                    'sep' => ', '
                ],

                // Résumé, présentation, notes
                'group3' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Résumé, présentation, notes', 'docalist-biblio'),
                    'format' => "<h3>%label</h3>\r\n<blockquote style=\"font-style:italic\">%content</blockquote>"
                ],
                'content' => [
                    'explode' => true,
                    'format' => 'v',
                    'sep' => ', ',
                    'newlines' => '<br />'
                ],

                // Liens, extrait, bande annonce...
                'group4' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Liens, extrait, bande annonce...', 'docalist-biblio'),
                    'format' => "<h3>%label</h3>\r\n<blockquote>%content</blockquote>"
                ],
                'link' => [
                    'explode' => true,
                    'format' => 'embed',
                    'sep' => ', '
                ],

                // Champs de gestion
                'group5' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Champs de gestion', 'docalist-biblio'),
                    'before' => '<p style="margin-left: 200px">',
                    'format' => '<span style="margin-left: -200px;float: left; font-weight: bold;">%label : </span>%content',
                    'after' => '</p>',
                    'sep' => '<br />'
                ],
                'ref',
                'type',
                'status',
                'creation',
                'lastupdate',
                'owner' => [
                    'sep' => ', '
                ],

                // Champs non affichés
                'group2' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Champs non affichés', 'docalist-biblio')
                ],
                'posttype',
                'parent',
                'password',
            ]
        ]);
    }

    static public function excerptGrid() {
        return new Schema([
            'label' => __('Affichage court', 'docalist-biblio'),
            'description' => __("Affichage court d'un film.", 'docalist-biblio'),
            'name' => 'excerpt',
            'fields' => [

                // Premier auteur, support, date, genre, durée
                'group1' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Premier auteur, support, date, genre, durée', 'docalist-biblio'),
                    'format' => '%content',
                    'sep' => ', '
                ],
                'author' => [
                    'explode' => true,
                    'format' => 'f n',
                    'limit' => 1,
                    'before' => 'De '
                ],
                'media' => [
                    'limit' => 1
                ],
                'date' => [
                    'format' => 'year',
                    'limit' => 1
                ],
                'genre',
                'extent' => [
                    'limit' => 1,
                    'format' => 'format'
                ],

                // Producteur / distributeur
                'group2' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Producteur / distributeur', 'docalist-biblio'),
                    'format' => '%label : %content',
                    'before' => '<p>',
                    'after' => '.</p>',
                    'sep' => ', '
                ],
                'corporation' => [
                    'format' => 'name',
                    'sep' => ', ',
                    'limit' => 1,
                    'explode' => true
                ],
                'editor' => [
                    'format' => 'name',
                    'explode' => true,
                    'limit' => 1
                ],

                // Début du résumé et mots-clés
                'group3' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Début du résumé et mots-clés', 'docalist-biblio'),
                    'format' => '%content'
                ],
                'content' => [
                    'maxlen' => 330,
                    'format' => 'v',
                    'limit' => 1,
                    'before' => '<blockquote style="font-style: italic">',
                    'after' => '</blockquote>'
                ],

                // Mots-clés
                'group6' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Mots-clés', 'docalist-biblio'),
                    'before' => '<p>',
                    'format' => '%label : %content',
                    'after' => '.</p>',
                    'sep' => '.<br />'
                ],
                'topic' => [
                    'format' => 'v',
                    'labelspec' => __('Mots-clés', 'docalist-biblio')
                ],

                // Bande annonce, extrait ou lien
                'group4' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Bande annonce, extrait ou lien', 'docalist-biblio'),
                    'format' => '%content'
                ],
                'link' => [
                    'format' => 'embed',
                    'explode' => true,
                    'limit' => 1
                ],

                // Champs non affichés
                'group5' => [
                    'type' => 'Docalist\Data\Type\Group',
                    'label' => __('Champs non affichés', 'docalist-biblio')
                ],
                'number',
                'edition',
                'collection',
                'ref',
                'parent',
                'title',
                'status',
                'creation',
                'lastupdate',
                'password',
                'posttype',
                'type',
                'othertitle',
                'translation',
                'language',
                'format',
                'event',
                'relation',
                'owner',
            ]
        ]);
    }
*/
}
