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
 * Législation.
 *
 * Décrit un texte législatif ou réglementaire : loi, projet de loi, proposition
 * de loi, ordonnance, décret, arrêté, circulaire, convention, décision, code
 * législatif, code réglementaire, note de service, etc.
 *
 * @see http://www.snphar.com/data/A_la_une/phar27/legislation27.pdf
 *
 * Principales caractéristiques :
 * - l'auteur est un député, un sénateur, ou le premier ministre
 * - a une date de dépôt
 * - est publié ou nom au bo, au jo, etc.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class LegislationEntity extends ReferenceEntity
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema()
    {
        return [
            'name' => 'legislation',
            'label' => __('Législation', 'docalist-biblio'),
            'description' => __('Un texte législatif ou réglementaire.', 'docalist-biblio'),
            'fields' => [
                'othertitle'    => ['unused' => true],
                'translation'   => ['unused' => true],
                'editor'        => ['unused' => true],
                'collection'    => ['unused' => true],
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
            'title'
        );
        $builder->addGroup(
            __('Auteurs', 'docalist-biblio'),
            'author,corporation'
        );
        $builder->addGroup(
            __('Journal / Périodique', 'docalist-biblio'),
            'journal,number,edition'
        );
        $builder->addGroup(
            __('Informations bibliographiques', 'docalist-biblio'),
            'date,language,extent,format'
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
