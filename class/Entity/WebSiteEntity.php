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
 * Décrit un site web.
 *
 * - Un site web est un ensemble de pages web reliées entre elles et
 *   accessible à une adresse web.
 *   @see http://fr.wikipedia.org/wiki/Site_web
 *
 * - A website is a set of related web pages served from a single web domain.
 *   A website is hosted on at least one web server, accessible via a network
 *   such as the Internet or a private local area network through an URL.
 *   @see http://en.wikipedia.org/wiki/Web_site
 *
 * Principales caractéristiques :
 * - a une URL
 * - a un organisme ou une personne auteur
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class WebSiteEntity extends ReferenceEntity
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'website',
            'label' => __('Site web', 'docalist-biblio'),
            'description' => __('Un site web.', 'docalist-biblio'),
            'fields' => [
                'media'         => ['unused' => true],
                'journal'       => ['unused' => true],
                'extent'        => ['unused' => true],
                'collection'    => ['unused' => true],
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
            __('Type de site', 'docalist-biblio'),
            'genre'
        );
        $builder->addGroup(
            __('Titres', 'docalist-biblio'),
            'title,othertitle,translation,context'
        );
        $builder->addGroup(
            __('Auteurs', 'docalist-biblio'),
            'corporation,author'
        );
        $builder->addGroup(
            __('Informations bibliographiques', 'docalist-biblio'),
            'date,language,number,format'
        );
        $builder->addGroup(
            __('Informations éditeur', 'docalist-biblio'),
            'editor,edition'
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
