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
namespace Docalist\Biblio\Entity;

use Docalist\Biblio\Reference;

/**
 * Un périodique.
 *
 * Décrit un périodique : revue, magazine, journal, newsletter, etc.
 *
 * - Une publication périodique, ou simplement un périodique, est un titre de
 *   presse qui paraît régulièrement. Les périodiques sont généralement
 *   imprimés. Cependant, il existe aussi depuis quelques années des
 *   périodiques électroniques, à consulter sur Internet, sur un assistant
 *   personnel ou sur une liseuse.
 *   @see http://fr.wikipedia.org/wiki/Publication_p%C3%A9riodique
 *
 * - Periodical literature (also called a periodical publication or simply a
 *   periodical) is a published work that appears in a new edition on a regular
 *   schedule. The most familiar examples are the newspaper, often published
 *   daily, or weekly; or the magazine, typically published weekly, monthly
 *   or as a quarterly. Other examples would be a newsletter, a literary
 *   journal or learned journal, or a yearbook.
 *   @see http://en.wikipedia.org/wiki/Periodical_literature
 *
 * Types de périodiques :
 * - revue : périodique spécialisé dans un domaine précis
 *
 * - magazine : périodique, le plus souvent illustré, traitant de divers
 *   sujets ou parfois spécialisé
 *
 * - journal : document qui recense par ordre chronologique ou thématique un
 *   certain nombre d'événements pour une période donnée (généralement une
 *   journée, d'où le nom). Par extension, un journal désigne une publication
 *   regroupant des articles sur l'actualité du jour.
 *
 * @see http://fr.wikipedia.org/wiki/Journal @see http://en.wikipedia.org/wiki/Newspaper
 * @see http://fr.wikipedia.org/wiki/Revue
 * @see http://fr.wikipedia.org/wiki/Magazine @see http://en.wikipedia.org/wiki/Magazine
 *
 * Principales caractéristiques :
 * - a un éditeur (un organisme)
 * - a un ISSN
 * - a une périodicité
 */
class Periodical extends Reference {
    static protected function loadSchema() {
        $fields = parent::loadSchema()['fields'];

        // @formatter:off
        return [
            'name' => 'periodical',
            'label' => __('Périodique', 'docalist-biblio'),
            'description' => __('Une publication périodique (revue, magazine, journal...)', 'docalist-biblio'),
            'fields' => [
                // Type, Genre, Media
                'group1' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Nature du document'],
                $fields['genre'],
                $fields['media'],

                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Titres'],
                $fields['title'],
                $fields['number'],
                $fields['othertitle'],
//              $fields['translation'],

                // Author, Organisation
                'group3' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Auteurs'],
                $fields['author'],
                $fields['organisation'],

                // Date / Language / Pagination / Format
                'group4' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Informations bibliographiques'],
                $fields['date'],
                $fields['language'],
                $fields['extent'],
                $fields['format'],

                // Editor / Collection / Edition
                'group5' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Informations éditeur'],
                $fields['editor'],
                $fields['collection'],
  //              $fields['edition'],

                // Topic / Abstract / Note
                'group6' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Indexation et résumé'],
                $fields['topic'],
                $fields['content'],

                // // Liens et relations
                'group7' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Liens et relations'],
                $fields['link'],
                $fields['relation'],

                // Ref / Owner / Creation / Lastupdate
                'group8' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Informations de gestion'],
                $fields['type'],
                $fields['ref'],
                $fields['owner'],
            ]
        ];
        // @formatter:on
    }
}