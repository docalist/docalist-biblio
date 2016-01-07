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
 * Congrès, colloque.
 *
 * Décrit une manisfestation ou un regroupement professionnel visant à favoriser
 * les échanges, partager les expériences et informer les professionnels sur un
 * sujet donné : congrès, colloque, conférence, salon, séminaire, exposition,
 * assemblée générale, etc.
 *
 * Congrès (congress)
 * - Un congrès est une réunion solennelle ou une assemblée de personnes
 *   compétentes pour débattre d'une question.
 *   @see http://fr.wikipedia.org/wiki/Congr%C3%A8s
 *
 * - A congress is a formal meeting of the representatives of different nations,
 *   constituent states, independent organizations (such as trade unions), or
 *   groups.
 *   @see http://en.wikipedia.org/wiki/Congress
 *
 * Colloque (colloquium)
 * - Un colloque désigne une conférence de spécialistes (scientifiques).
 *   @see http://fr.wikipedia.org/wiki/Colloque
 *
 * - An academic seminar usually led by a different lecturer and on a different
 *   topic at each meeting.
 *   @see http://en.wikipedia.org/wiki/Colloquium
 *
 * Conférence (convention, meeting)
 * - Une conférence est une confrontation d'idées (scientifiques ou médicales,
 *   philosophiques, politique...) sur un sujet jugé d'importance par les
 *   participants. Son organisation est généralement formelle, elle rassemble
 *   un ou plusieurs intervenants (spécialistes) et leurs contradicteurs ou
 *   citoyens ou représentants de la société civile.
 *   @see http://fr.wikipedia.org/wiki/Conf%C3%A9rence
 *
 * - A convention, in the sense of a meeting, is a gathering of individuals who
 *   meet at an arranged place and time in order to discuss or engage in some
 *   common interest.
 *   @see http://en.wikipedia.org/wiki/Convention_(meeting)
 *
 * Salon (trade fair, trade show, trade exhibition, expo)
 * - Un salon désigne une exposition rassemblant, en guise d'exposants, des
 *   spécialistes (généralement des professionnels) d'un même secteur en vue de
 *   développer une activité.
 *   @see http://fr.wikipedia.org/wiki/Salon_(%C3%A9v%C3%A9nementiel)
 *
 * - A trade fair (trade show, trade exhibition or expo) is an exhibition
 *   organized so that companies in a specific industry can showcase and
 *   demonstrate their latest products, service, study activities of rivals
 *   and examine recent market trends and opportunities.
 *   @see http://en.wikipedia.org/wiki/Trade_fair
 *
 * Séminaire (seminar)
 * - Un séminaire est une réunion en petit groupe, généralement dans un but
 *   d'enseignement.
 *   @see http://fr.wikipedia.org/wiki/S%C3%A9minaire_(enseignement)
 *
 * - A seminar is, generally, a form of academic instruction, either at an
 *   academic institution or offered by a commercial or professional
 *   organization. It has the function of bringing together small groups for
 *   recurring meetings, focusing each time on some particular subject, in
 *   which everyone present is requested to actively participate.
 *   @see http://en.wikipedia.org/wiki/Seminar
 *
 * Exposition (exhibition)
 * - Une exposition artistique désigne l'espace et le temps où des oeuvres
 *   rencontrent un public.
 *   @see http://fr.wikipedia.org/wiki/Exposition
 *
 * - An exhibition is an organized presentation and display of a selection of
 *   items
 *   @see http://en.wikipedia.org/wiki/Exhibition
 *
 * Assemblée générale (annual general meeting, AGM, annual meeting)
 * - Une assemblée générale est le rassemblement de l'ensemble des membres
 *   d'une organisation (ou des représentants de ces membres) afin qu'ils
 *   rencontrent les dirigeants ou les membres du conseil d'administration et
 *   puissent éventuellement prendre des décisions.
 *   @see http://fr.wikipedia.org/wiki/Assembl%C3%A9e_g%C3%A9n%C3%A9rale
 *
 * - An annual general meeting is a meeting that official bodies, and
 *   associations involving the general public are often required by law (or
 *   the constitution, charter, by-laws etc. governing the body) to hold.
 *   @see http://en.wikipedia.org/wiki/Annual_general_meeting
 *
 * Principales caractéristiques :
 * - a eu lieu à une date ou une période précise
 * - a eu lieu a un endroit précis
 * - était organisé à l'initiative d'un ou plusieurs organismes
 * - pas d'auteur personne physique
 */
class Meeting extends Reference {
    static public function loadSchema() {
        // Récupère les champs d'une référence standard
        $fields = parent::loadSchema()['fields'];

        // Supprime les champs qu'on n'utilise pas
        unset($fields['othertitle']);
        unset($fields['journal']);
        unset($fields['editor']);
        unset($fields['collection']);
        unset($fields['edition']);
        unset($fields['event']);

        // Personnalise les tables, les libellés, les description, etc.
        // todo

        // Contruit notre schéma
        return [
            'name' => 'meeting',
            'label' => __('Congrès / colloque', 'docalist-biblio'),
            'description' => __('Une manisfestation ou un regroupement professionnel.', 'docalist-biblio'),
            'fields' => $fields,
        ];
    }

    static public function editGrid() {
        return new Schema([
            'label' => __('Formulaire de saisie', 'docalist-biblio'),
            'description' => __("Grille de saisie d'un colloque.", 'docalist-biblio'),
            'fields' => [
                // Type, Genre, Media
                'group1' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Nature du document'],
                'genre',
                'media',

                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Titres'],
                'title',
//              'event',
                'translation',
//              'othertitle',

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

                // Topic / Abstract / Note
                'group5' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Indexation et résumé'],
                'topic',
                'content',

                // // Liens et relations
                'group6' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Liens et relations'],
                'link',
                'relation',

                // Ref / Owner / Creation / Lastupdate
                'group7' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations de gestion'],
                'type',
                'ref',
                'owner',
            ]
        ]);
    }
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