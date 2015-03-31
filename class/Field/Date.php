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
 * @version     $Id$
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\MultiField;
use Docalist\Schema\Field;
use Docalist\Search\MappingBuilder;

/**
 * Date.
 *
 * @property String $type
 * @property String $value
 */
class Date extends MultiField {
    static protected $groupkey = 'type';

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type de date', 'docalist-biblio'),
    //                 'description' => __('Date', 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Date', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('date')->date();
        $mapping->template('date.*')->idem('date')->copyTo('date');
    }

    public function map(array & $document) {
        $document['date.' . $this->type()][] = $this->__get('value')->value();
    }

    // TODO : créer un Type date
    // en attentant, la méthode est publique pour permettre à Event d'y accéder
    public static function formatDate($date) {
        if (strlen($date) < 4) {
            return $date;
        }

        $year = substr($date, 0, 4);
        $month = (strlen($date) < 6) ? '' : substr($date, 4, 2);
        $day = (strlen($date) < 8) ? '' : substr($date, 6, 2);

        $h = $year;
        $month && $h = $month . '/' . $h;
        $day && $h = $day . '/' . $h;

        return $h;
    }

    protected static function initFormats() {
        self::registerFormat('date (type)', 'JJ/MM/AAAA (type)', function(Date $date, Dates $parent) {
            return self::formatDate($date->__get('value')->value(), '') .
                   ' (' . $parent->lookup($date->type()) . ')';

        });

        self::registerFormat('date', 'JJ/MM/AAAA', function(Date $date) {
            return self::formatDate($date->__get('value')->value(), '');
        });

        self::registerFormat('month/year', 'MM/AAAA', function(Date $date, Dates $parent) {
            return substr(self::callFormat('date', $date, $parent), 3);
        });

        self::registerFormat('year', 'AAAA', function(Date $date, Dates $parent) {
            return substr(self::callFormat('date', $date, $parent), -4);
        });
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a que le type et pas de date
        return $this->filterEmptyProperty('value');
    }
}