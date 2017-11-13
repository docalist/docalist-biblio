<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Export;

use Docalist\Biblio\Reference\ReferenceIterator;

/**
 * Un exporteur au format JSON.
 */
class Json extends Exporter
{
    protected static $defaultSettings = [
        // Surcharge les paramètres hérités
        'mime-type' => 'application/json',
        'extension' => '.json',

        // Indique s'il faut générer du code lisible ou indenté
        'pretty' => false,
    ];

    public function export(ReferenceIterator $references)
    {
        $pretty = $this->get('pretty');
        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $pretty && $options |= JSON_PRETTY_PRINT;

        $first = true;
        echo '[';
        $pretty && print("\n");
        $comma = $pretty ? ",\n" : ',';
        foreach ($references as $reference) {
            $data = $this->converter->convert($reference);
            $data = $this->removeEmpty($data);
            if (empty($data)) {
                continue;
            }
            $first ? ($first = false) : print($comma);
            echo json_encode($data, $options);
            $pretty && print("\n");
        }
        echo ']';
        $pretty && print("\n");
    }

    protected function removeEmpty($data)
    {
        return array_filter($data, function ($value) {
            is_array($value) && $value = $this->removeEmpty($value);

            return ! ($value === '' | $value === null | $value === []);
        });
    }

    public function getLabel()
    {
        return 'JSON';
    }

    public function getDescription()
    {
        return sprintf(
            __(
                'Fichier texte contenant des données au format <a href="%s">Javascript Object Notation</a>.',
                'docalist-biblio'
            ),
            'https://fr.wikipedia.org/wiki/JavaScript_Object_Notation'
        );
    }
}
