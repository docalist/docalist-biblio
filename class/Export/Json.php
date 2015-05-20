<?php
/**
 * This file is part of the 'Docalist Biblio Export' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id: Json.php 1918 2015-01-05 10:29:27Z daniel.menard.35@gmail.com $
 */
namespace Docalist\Biblio\Export;

use Docalist\Biblio\Reference;
use Docalist\Biblio\Reference\ReferenceIterator;

/**
 * Un exporteur au format JSON.
 */
class Json extends Exporter {
    protected static $defaultSettings = [
        // Surcharge des paramètres hérités
        'mime-type' => 'application/json',
        'extension' => '.json',

        // Indique s'il faut générer du code lisible ou indenté
        'pretty' => false,
    ];

    public function export(ReferenceIterator $references) {
        $pretty = $this->get('pretty');
        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $pretty && $options |= JSON_PRETTY_PRINT;

        foreach($references as $reference) {
            $data = $this->converter->convert($reference);
            echo json_encode($data, $options);
            $pretty && print("\n\n");
        }
    }

    public static function label() {
        return 'JSON';
    }

    public static function description() {
        return __('Fichier texte contenant des données structurées au format <a href="http://fr.wikipedia.org/wiki/JavaScript_Object_Notation">Javascript Object Notation</a>.', 'docalist-biblio');
    }
}