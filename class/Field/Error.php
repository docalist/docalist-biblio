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

use Docalist\Biblio\Type\Object;
use Docalist\Search\MappingBuilder;

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

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('error')->text()->filter();
    }

    public function map(array & $document) {
        $document['error'][] = $this->message();
    }
}