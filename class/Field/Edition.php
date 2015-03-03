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

use Docalist\Biblio\Type\String;
use Docalist\Schema\Field;

/**
 * Une mention d'édition
 */
class Edition extends String {
    public function map(array & $doc) {
        $doc['edition'][] = $this->value();
    }

    public static function ESmapping(array & $mappings, Field $schema) {
        $mappings['properties']['edition'] = self::stdIndex();
    }
}