<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\Composite;
use Docalist\Type\Text;

/**
 * Champ "collection" : le nom d'une collection sous laquelle sont regroupés des travaux de mêmes nature et le numéro
 * du document catalogué au sein de cette collection.
 *
 * Chaque occurence du champ collection comporte deux sous-champs :
 * - `name` : nom de la collection,
 * - `number` : numéro au sein de la collection,
 *
 * @property Text   $name   Nom de la collection.
 * @property Text   $number Numéro au sein de la collection.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class CollectionField extends Composite
{
    public static function loadSchema()
    {
        return [
            'name' => 'collection',
            'repeatable' => true,
            'label' => __('Collection', 'docalist-biblio'),
            'description' => __(
                'Nom de la collection à laquelle appartient le document et son numéro au sein de cette collection.',
                'docalist-biblio'
            ),
            'fields' => [
                'name' => [
                    'type' => Text::class,
                    'label' => __("Nom", 'docalist-biblio'),
                    'description' => __('Nom de la collection.', 'docalist-biblio'),
                ],
                'number' => [
                    'type' => Text::class,
                    'label' => __('Numéro', 'docalist-biblio'),
                    'description' => __('Numéro au sein de la collection.', 'docalist-biblio'),
                ]
            ],
            'editor' => 'table',
        ];
    }

    public function getAvailableFormats()
    {
        return [
            'n (#)' => __('Collection (numéro)', 'docalist-biblio'),
            'n : #' => __('Collection : numéro', 'docalist-biblio'),
            'n: #'  => __('Collection: numéro', 'docalist-biblio'),
            'n;#'   => __('Collection;numéro', 'docalist-biblio'),
            'n #'   => __('Collection numéro', 'docalist-biblio'),
            'n'     => __('collection', 'docalist-biblio'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        $name = $this->formatField('name', $options);
        $number = $this->formatField('number', $options);

        switch ($format) {
            case 'n (#)': // Espace insécable avant la parenthèse ouvrante
                return empty($number) ? $name : ($name . ' ('  . $number . ')');

            case 'n : #': // Espace insécable avant le signe deux-points
                return empty($number) ? $name : ($name . ' : '  . $number);

            case 'n: #':
                return empty($number) ? $name : ($name . ': '  . $number);

            case 'n;#':
                return empty($number) ? $name : ($name . ';'  . $number);

            case 'n #': // Espace insécable
                return empty($number) ? $name : ($name . ' '  . $number);

            case 'n':
                return $name;
        }

        return parent::getFormattedValue($options);
    }
}
