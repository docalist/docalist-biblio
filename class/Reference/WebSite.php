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
class WebSite extends Reference
{
    public static function loadSchema()
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

    public static function getEditGrid()
    {
        return static::buildEditGrid([
            __('Type de site', 'docalist-biblio')                   => 'genre',
            __('Titres', 'docalist-biblio')                         => 'title,othertitle,translation',
            __('Auteurs', 'docalist-biblio')                        => 'organisation,author',
            __('Informations bibliographiques', 'docalist-biblio')  => 'date,language,number,format',
            __('Informations éditeur', 'docalist-biblio')           => 'editor,edition',
            __('Congrès et diplômes', 'docalist-biblio')            => 'event',
            __('Indexation et résumé', 'docalist-biblio')           => 'topic,content',
            __('Liens et relations', 'docalist-biblio')             => 'link,relation',
            __('Informations de gestion', 'docalist-biblio')        => '-,type,ref,owner',
        ]);
    }
}
