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

use Docalist\Biblio\Type\Integer;
use Docalist\Forms\Input;
use Docalist\Search\MappingBuilder;

/**
 * Le numéro de référence de la notice.
 */
class Ref extends Integer {
    public function editForm() {
        $field = new Input($this->schema->name());
        $field->attribute('type', 'number');

        return $field;
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('ref')->long();
    }

    public function map(array & $document) {
        $document['ref'] = $this->value();
    }
}