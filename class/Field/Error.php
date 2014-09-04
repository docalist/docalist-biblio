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

use Docalist\Biblio\Type\Object;
use Docalist\Schema\Field;

/**
 * Une erreur.
 *
 * @property String $code
 * @property String $value
 * @property String $message
 */
class Error extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'code',
                'value',
                'message'
            ]
        ];
        // @formatter:on
    }

    public function map(array & $doc) {
        $doc['error'][] = $this->message();
    }

    public static function ESmapping(array & $mappings, Field $schema) {
        $mappings['properties']['error'] = self::stdIndexAndFilter();
    }
}