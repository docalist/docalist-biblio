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

use Docalist\Biblio\Type\String;
// use Docalist\Forms\Input;
use Docalist\Search\MappingBuilder;
use Docalist\Forms\TableLookup;

/**
 * Un titre de périodique.
 */
class Journal extends String {
    public function editForm() {
//         $field = new Input($this->schema->name());
//         $field->addClass('large-text');

        $field = new TableLookup('journal', 'index:journal');
        $field->addClass('large-text');

        return $field;
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('journal')->text()->filter()->suggest();
    }

    public function map(array & $document) {
        $document['journal'] = $this->value();
    }
}