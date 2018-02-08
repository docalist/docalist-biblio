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
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Meeting extends Reference
{
    public static function loadSchema()
    {
        return [
            'name' => 'meeting',
            'label' => __('Congrès / colloque', 'docalist-biblio'),
            'description' => __('Une manisfestation ou un regroupement professionnel.', 'docalist-biblio'),
            'fields' => [
                'othertitle'    => ['unused' => true],
                'journal'       => ['unused' => true],
                'editor'        => ['unused' => true],
                'collection'    => ['unused' => true],
                'edition'       => ['unused' => true],
                'event'         => ['unused' => true],
            ],
        ];
    }

    public static function getEditGrid()
    {
        return static::buildEditGrid([
            __('Nature du document', 'docalist-biblio')             => 'genre,media',
            __('Titres', 'docalist-biblio')                         => 'title,translation',
            __('Auteurs', 'docalist-biblio')                        => 'author,corporation',
            __('Informations bibliographiques', 'docalist-biblio')  => 'date,language,number,extent,format',
            __('Indexation et résumé', 'docalist-biblio')           => 'topic,content',
            __('Liens et relations', 'docalist-biblio')             => 'link,relation',
            __('Informations de gestion', 'docalist-biblio')        => '-,type,ref,owner',
        ]);
    }
}
