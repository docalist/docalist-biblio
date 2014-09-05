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

/**
 * Etendue du document : pagination, nombre de pages, durée en minutes, etc.
 *
 * @property String $type
 * @property String $value
 */
class Extent extends MultiField {
    static protected $groupkey = 'type';

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __("Type", 'docalist-biblio'),
                    'description' => __("Type d'étendue", 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Valeur', 'docalist-biblio'),
                    'description' => __('Etendue dans le format indiqué par le type (n° de page, nb de pages, durée, etc.)', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    // map : champ non indexé

    protected static function initFormats() {
        self::registerFormat('format', "Format indiqué dans la table d'autorité", function(Extent $Extent, Extents $parent) {
            $format = $parent->lookup($Extent->type(), false, 'code', 'format');
            return trim(sprintf($format, $Extent->__get('value')->value()));
        });

        self::registerFormat('label', "Libellé indiqué dans la table suivi de la valeur", function(Extent $Extent, Extents $parent) {
            $label = $parent->lookup($Extent->type());
            return trim($label . ' ' . $Extent->__get('value')->value());
        });

        self::registerFormat('v', 'Valeur uniquement, sans aucune mention', function(Extent $Extent) {
            return $Extent->__get('value')->value();
        });

        self::registerFormat('v (t)', 'Valeur suivie du type entre parenthèses', function(Extent $Extent, Extents $parent) {
            $result = $Extent->__get('value')->value();
            if (isset($Extent->type)) {
                $result && $result .= ' '; // espace insécable avant '('
                $result .= '(' . $parent->lookup($Extent->type()) . ')';
            }

            return $result;
        });

        // TODO : return Extent exemple ou array(Extent, Extent...)
    }
}