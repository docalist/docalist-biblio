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
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\StringTable;
use Docalist\Search\MappingBuilder;

/**
 * Un genre de document.
 */
class Genre extends StringTable {
    public function mapping(MappingBuilder $mapping) {
        $mapping->field('genre')->text()->filter();
    }

    public function map(array & $document) {
        $document['genre'][] = $this->label();
    }
}