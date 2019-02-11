<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Biblio\Entity;

use Docalist\Biblio\Entity\ReferenceEntity;
use Docalist\Data\GridBuilder\EditGridBuilder;

/**
 * Diplôme.
 *
 * Décrit un document élaboré en vue de l'obtention d'un diplôme : thèse,
 * mémoire, dissertation, etc.
 *
 * - Dans le milieu universitaire, une thèse est un mémoire résumant un
 *   travail de recherche universitaire, soutenu devant un jury par un
 *   étudiant afin d'obtenir un diplôme ou un grade universitaire.
 *   @see http://fr.wikipedia.org/wiki/Th%C3%A8se
 *
 * - A thesis or dissertation is a document submitted in support of
 *   candidature for an academic degree or professional qualification
 *   presenting the author's research and findings
 *   @see http://en.wikipedia.org/wiki/Thesis
 *
 * Principales caractéristiques :
 * - écrit en vue d'obtenir un diplôme
 * - relié à une école, une fac, une université, etc.
 * - a un seul auteur physique
 * - peut avoir un ou plusieurs maitre de stage, directeur de thèse, etc.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DegreeEntity extends ReferenceEntity
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema()
    {
        return [
            'name' => 'degree',
            'label' => __('Mémoire ou thèse', 'docalist-biblio'),
            'description' => __('Un document élaboré en vue de l\'obtention d\'un diplôme.', 'docalist-biblio'),
            'fields' => [
                'translation'   => ['unused' => true],
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

        $builder->addGroup(
            __('Nature du document', 'docalist-biblio'),
            'genre,media'
        );
        $builder->addGroup(
            __('Titres', 'docalist-biblio'),
            'title,othertitle'
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

        return $builder->getGrid();
    }
}
