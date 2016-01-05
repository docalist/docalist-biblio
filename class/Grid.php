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
namespace Docalist\Biblio;

use Docalist\Schema\Schema;

class Grid extends Schema
{
    protected function addSubFields(Schema $field, Schema $base)
    {
        if ($base->hasFields()) {
            $fields = [];
            foreach ($base->getFields() as $name => $subfield) {
                $fields[$name] = new Schema(['name' => $name]);
                $this->addSubFields($fields[$name], $subfield);
            }
            $field->properties['fields'] = $fields;
        }
    }

    public function initSubfields(Schema $base)
    {
        foreach ($this->getFields() as $name => $field) {
            if ($base->hasField($name)) {
                $this->addSubFields($field, $base->getField($name));
            }
        }
    }

    public function mergeWith(array $data)
    {
        return new self($this->mergeProperties($this->value(), $data));
    }
}
