<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\Composite;
use Docalist\Type\Text;

/**
 * Collection et numéro au sein de la collection de l'éditeur.
 *
 * Ce champ composite permet d'indiquer le nom de la collection de l'éditeur à laquelle appartient le document
 * catalogué et de préciser le numéro de ce document au sein de cette collection.
 *
 * Chaque occurence du champ comporte deux sous-champs :
 * - `name` : nom de la collection,
 * - `number` : numéro au sein de la collection,
 *
 * @property Text   $name   Nom de la collection.
 * @property Text   $number Numéro au sein de la collection.
 */
class Collection extends Composite
{
    public static function loadSchema()
    {
        return [
            'label' => __('Collection', 'docalist-biblio'),
            'description' => __(
                "Collection et numéro au sein de la collection de l'éditeur.",
                'docalist-biblio'
            ),
            'fields' => [
                'name' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __("Nom", 'docalist-biblio'),
                    'description' => __('Nom de la collection ou de la sous-collection.', 'docalist-biblio'),
                ],
                'number' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Numéro', 'docalist-biblio'),
                    'description' => __(
                        'Numéro au sein de la collection ou de la sous-collection.',
                        'docalist-biblio'
                    ),
                ]
            ]
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

        throw new InvalidArgumentException("Invalid Collection format '$format'");
    }
}
