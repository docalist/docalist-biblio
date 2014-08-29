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

use Docalist\Forms\Table;

/**
 * Une liste de collections ou de sous-collections.
 */
class Collections extends Repeatable {
    static protected $type = 'Collection';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->input('name')->addClass('collection-name');
        $field->input('number')->addClass('collection-number');

        return $field;
    }
}