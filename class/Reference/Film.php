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
namespace Docalist\Biblio\Reference;

use Docalist\Biblio\Reference;
use Docalist\Schema\Schema;

/**
 * Film.
 *
 * Décrit un film.
 *
 * - Film distribué : possède un diffuseur, etc. comme un livre.
 */
class Film extends Reference {
    static protected function loadSchema() {
        // Récupère les champs d'une référence standard
        $fields = parent::loadSchema()['fields'];

        // Supprime les champs qu'on n'utilise pas
        unset($fields['journal']);

        // Personnalise les tables, les libellés, les description, etc.
        $fields['title']['label'] = __('Titre du film', 'docalist-biblio');
        $fields['title']['description'] = __('Titre exact du film.', 'docalist-biblio');

        $fields['genre']['label'] = __('Genre', 'docalist-biblio');
        $fields['genre']['description'] = __("Type de film : documentaire, fiction, reportage, clip, congrès, film d'animation, débat, etc.", 'docalist-biblio');

        $fields['media']['label'] = __('Support de diffusion', 'docalist-biblio');
        $fields['media']['description'] = __('Support physique utilisé pour la diffusion du film : dvd, vhs, en ligne...', 'docalist-biblio');

        $fields['extent']['label'] = __('Durée du film', 'docalist-biblio');

        $fields['author']['label'] = __('Personnes', 'docalist-biblio');
        $fields['author']['description'] = __("Liste des personnes qui ont contribué à l'élaboration du film : producteur, réalisateur, scénariste, acteurs...", 'docalist-biblio');

        $fields['organisation']['description'] = __('Liste des organismes qui ont contribué au film : organisme considéré comme auteur, organisme commanditaire, financeur, producteur...', 'docalist-biblio');

//      $fields['othertitle']['label'] = __('Autres titres du film', 'docalist-biblio');
        $fields['othertitle']['description'] = __('Autres titres du film : variante, sous-titre, etc.', 'docalist-biblio');

        $fields['translation']['label'] = __("Titre dans d'autres langues", 'docalist-biblio');
        $fields['translation']['description'] = __("Titre du film dans d'autres langues : titre original, traduction du titre dans une autre langue...", 'docalist-biblio');

        $fields['date']['label'] = __('Date du film', 'docalist-biblio');
        $fields['date']['description'] = __("Dates au format <code>AAAAMMJJ</code> : date de sortie, date d'enregistrement, date de première diffusion...", 'docalist-biblio');

        $fields['language']['description'] = __('Langue du film et langues des pistes audio disponibles.', 'docalist-biblio');

        $fields['format']['label'] = __('Autres caractéristiques', 'docalist-biblio');
        $fields['format']['description'] = __('Autres informations sur le film : format de la vidéo, sous-titres disponibles, couleur/n&b, VO/VF, bonus, etc.', 'docalist-biblio');

        $fields['number']['description'] = __("Utilisez les zones ci-dessus pour indiquer les numéros éventuels associés au film : numéro de dépôt légal, visa d'exploitation, numéro de saison et d'épisode, numéro d'opus, etc.", 'docalist-biblio');

        $fields['editor']['label'] = __('Editeur ou distributeur', 'docalist-biblio');
        $fields['editor']['description'] = __("Organismes délégués par l'auteur pour assurer l'édition, la diffusion, la distribution ou la commercialisation du film.", 'docalist-biblio');

        $fields['edition']['label'] = __('Mentions de version', 'docalist-biblio');
        $fields['edition']['description'] = __("Si le film a fait l'objet de plusieurs versions, indiquez ici les informations permettant d'identifier la version cataloguée (exemple : version longue).", 'docalist-biblio');

        $fields['collection']['label'] = __('Série ou collection', 'docalist-biblio');
        $fields['collection']['description'] = __("Si le film fait partie d'une série ou d'une collection, vous pouvez l'indiquer ici et mentionner le numéro éventuel de ce film au sein de la série.", 'docalist-biblio');

        $fields['event']['label'] = __('Quoi, quand, où ?', 'docalist-biblio');
        $fields['event']['description'] = __("Si le film a été réalisé à l'occasion d'un événement (un colloque, un concert, une assemblée, etc.), indiquez les informations disponibles dans les zones ci-dessus.", 'docalist-biblio');

        $fields['content']['description'] = __("Informations relatives au contenu du film : synopsis, résumé, extrait d'une critique, ce qu'en dit la presse, remarques sur le financement, avertissement sur le contenu, objectifs pédagogiques, interview du réalisateur...", 'docalist-biblio');

        $fields['link']['description'] = __('Liens relatifs au film (site dédié, extrait, bande annonce...) ou à ses auteurs (site du film, du réalisateur, du distributeur...) et adresses de sites tiers liés au film (critique, forum...)', 'docalist-biblio');

        $fields['relation']['label'] = __('Notices associées', 'docalist-biblio');
        $fields['relation']['description'] = __("Relations entre ce film et d'autres notices de la base.", 'docalist-biblio');

        // Contruit notre schéma
        return [
            'name' => 'film',
            'label' => __('Film', 'docalist-biblio'),
            'description' => __('Un film distribué par un éditeur ou un diffuseur.', 'docalist-biblio'),
            'fields' => $fields,
        ];
    }

    static public function editGrid() {
        return new Schema([
            'label' => __('Formulaire de saisie', 'docalist-biblio'),
            'description' => __("Grille de saisie d'un film.", 'docalist-biblio'),
            'fields' => [
                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Titres'],
                'title',
                'translation',
                'othertitle',

                // Author, Organisation
                'group3' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Personnes et organismes ayant contribué au film'],
                'author',
                'organisation',
                'editor',

                // Type, Genre, Media
                'group1' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Type de film et support'],
                'genre',
                'media',

                // Event
                'group6' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => __("Film réalisé à l'occasion d'un événement", 'docalist-biblio')],
                'event',

                // Date / Language / Pagination / Format
                'group4' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Caractéristiques'],
                'date',
                'extent' => [
                    'default' => [['type' => 'minutes']],
                ],
                'language',
                'edition',
                'format',

                // Collection et autres numéros
                'group5' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Collection et numéros'],
                'collection',
                'number',

                // Topic / Abstract / Note
                'group7' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'De quoi parle le film ?'],
                'content',
                'topic',

                // // Liens et relations
                'group8' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Liens et relations'],
                'link',
                'relation',

                // Ref / Owner / Creation / Lastupdate
                'group9' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations de gestion'],
                'type',
                'ref',
                'owner',
            ]
        ]);

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
                    'type' => 'Docalist\Biblio\Type\Group',
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
                'organisation' => [
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
                    'type' => 'Docalist\Biblio\Type\Group',
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
                    'type' => 'Docalist\Biblio\Type\Group',
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
                    'type' => 'Docalist\Biblio\Type\Group',
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
                'imported',
                'errors' => [
                    'sep' => ', '
                ],

                // Champs non affichés
                'group2' => [
                    'type' => 'Docalist\Biblio\Type\Group',
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
                    'type' => 'Docalist\Biblio\Type\Group',
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
                    'type' => 'Docalist\Biblio\Type\Group',
                    'label' => __('Producteur / distributeur', 'docalist-biblio'),
                    'format' => '%label : %content',
                    'before' => '<p>',
                    'after' => '.</p>',
                    'sep' => ', '
                ],
                'organisation' => [
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
                    'type' => 'Docalist\Biblio\Type\Group',
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
                    'type' => 'Docalist\Biblio\Type\Group',
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
                    'type' => 'Docalist\Biblio\Type\Group',
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
                    'type' => 'Docalist\Biblio\Type\Group',
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
                'imported',
                'errors',
            ]
        ]);
    }
*/
}