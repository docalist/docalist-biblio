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

use Docalist\Biblio\Type\String;
use Docalist\Schema\Field;

/**
 * Un genre de document.
 */
class Genre extends String {
    public static function ESmapping(array & $mappings, Field $schema) {
        $mappings['properties']['genre'] = self::stdIndexAndFilter();
    }

    public function map(array & $doc) {
        // Ouvre la table utilisée par ce champ pour convertir les codes en libellés
        $table = $this->openTable();

        $item = $this->value();
        $label = $table->find('label', sprintf('code="%s"', $item));

        $doc['genre'][] = $label ?: $item;
    }
}