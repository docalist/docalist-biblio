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

use Docalist\Biblio\Type\Integer;
use Docalist\Forms\Input;
use Docalist\Schema\Field;

/**
 * Le numéro de référence de la notice.
 */
class Ref extends Integer {
    public function editForm() {
        $field = new Input($this->schema->name());
        $field->addClass('small-text');

        return $field;
    }

    public function map(array & $doc) {
        $doc['ref'] = $this->value();
    }

    public static function ESmapping(array & $mappings, Field $schema) {
        $mappings['properties']['ref'] = [
            'type' => 'string',
            'index' => 'not_analyzed',
        ];
    }
}