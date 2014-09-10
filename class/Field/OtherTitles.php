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
use Docalist\Forms\Fragment;

/**
 * Une collection de titres.
 */
class OtherTitles extends Repeatable {
    static protected $type = 'OtherTitle';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->TableLookup('type', $this->schema->table())
              ->addClass('othertitle-type');
        $field->input('value')->addClass('othertitle-value');

        return $field;
    }

    public function editSettings() {
        $form = parent::editSettings();
        return $this->addTableSelect($form, 'titles', __('Table des types de titres', 'docalist-biblio'));
    }

    public function displaySettings() {
        $form = parent::displaySettings();
        return $this->addTableSelect($form, 'titles', __('Table des types de titres', 'docalist-biblio'), true);
    }
}