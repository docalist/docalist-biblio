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

use Docalist\Biblio\Type\Repeatable;
use Docalist\Forms\Table;

/**
 * Une liste de collections ou de sous-collections.
 */
class Collections extends Repeatable {
    static protected $type = 'Docalist\Biblio\Field\Collection';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->input('name')->addClass('collection-name');
        $field->input('number')->addClass('collection-number');

        return $field;
    }
}