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
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Forms\Select;
use Docalist\Biblio\Entity\Reference;

/**
 * Le type de la notice.
 */
class Type extends String {
    public function editForm() {
        $types = [];
        foreach(Reference::types() as $type => $class) {
            $types[$type] = $class::defaultSchema()->label() . " ($type)";
        }

        $field = new Select($this->schema->name());
        $field->options($types);

        return $field;
    }
}