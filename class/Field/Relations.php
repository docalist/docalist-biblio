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

use Docalist\Biblio\Type\Repeatable;
use Docalist\Forms\Table;

/**
 * Une collection de relations.
 */
class Relations extends Repeatable {
    static protected $type = 'Relation';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->TableLookup('type', $this->schema->table())
              ->addClass('relations-type');
        $field->input('ref')->addClass('relations-ref');

        return $field;
    }

    public function editSettings() {
        $form = parent::editSettings();
        return $this->addTableSelect($form, 'relations', __('Table des types de relations', 'docalist-biblio'));
    }

    public function displaySettings() {
        $form = parent::displaySettings();
        return $this->addTableSelect($form, 'relations', __('Table des types de relations', 'docalist-biblio'), true);
    }
}