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
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\MultiField;
use Docalist\Schema\Field;

/**
 * Lien internet.
 *
 * @property String $type
 * @property String $url
 * @property String $label
 * @property String $date
 * @property String $lastcheck
 * @property String $checkstatus
 */
class Link extends MultiField {
    static protected $groupkey = 'type';

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type', 'docalist-biblio'),
                    'description' => __('Type de lien', 'docalist-biblio'),
                ],
                'url' => [
                    'label' => __('Adresse', 'docalist-biblio'),
                    'description' => __('Url complète du lien', 'docalist-biblio'),
                ],
                'label' => [
                    'label' => __('Libellé', 'docalist-biblio'),
                    'description' => __('Texte à afficher', 'docalist-biblio'),
                ],
                'date' => [
                    'label' => __('Accédé le', 'docalist-biblio'),
                    'description' => __('Date', 'docalist-biblio'),
                ],
                'lastcheck' => [
                    'label' => __('Lien vérifié le', 'docalist-biblio'),
                    'description' => __('Date de dernière vérification du lien', 'docalist-biblio'),
                ],
                'status' => [
                    'label' => __('Statut', 'docalist-biblio'),
                    'description' => __('Statut du lien lors de la dernière vérification.', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function map(array & $doc) {
        $doc['link'][] = $this->url();
    }

    public static function ESmapping(array & $mappings, Field $schema) {
        $mappings['properties']['link'] = self::stdIndex('simple');
        // cf. http://stackoverflow.com/a/18980048
    }

    protected static function initFormats() {
        self::registerFormat('link', 'Lien cliquable uniquement', function(Link $link) {
            $url = $link->url();

            $label = isset($link->label) ? $link->label() : $url;
            if (isset($link->date)) {
                $title = sprintf(__('Accédé le %s', 'docalist-biblio'), $link->date());
                $format = '<a href="%1$s" title="%3$s">%2$s</a>';
            } else {
                $title = '';
                $format = '<a href="%1$s">%2$s</a>';
            }

            $url = esc_attr($url);
            $title = esc_attr($title);
            $label = esc_html($label);

            return sprintf($format, $url, $label, $title);
        });

        self::registerFormat('label : link', 'Type et lien cliquable', function(Link $link, Links $parent) {
            $type = $parent->lookup($link->type());
            $link = self::callFormat('link', $link, $parent);
            return  $type . ' : ' . $link;
        });

        self::registerFormat('url', 'Url uniquement', function(Link $link, Links $parent) {
            return $link->url();
        });

        self::registerFormat('embed', 'Incorporé (embed) si possible, lien cliquable sinon', function(Link $link, Links $parent) {
            return wp_oembed_get($link->url()) ?: self::callFormat('link', $link, $parent);
        });
    }
}