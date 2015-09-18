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
use Docalist\Forms\Input;

/**
 * Une collection de producteurs.
 */
class Owners extends Repeatable {
    static protected $type = 'Docalist\Biblio\Field\Owner';

    public function editForm() {
        $field = new Input($this->schema->name());

        return $field;
    }
}