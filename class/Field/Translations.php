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
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\Repeatable;
use Docalist\Forms\Table;

/**
 * Une collection de traductions du titre.
 */
class Translations extends Repeatable {
    static protected $type = 'Translation';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->TableLookup('language', $this->schema->table())
              ->addClass('translation-language');
        $field->input('title')->addClass('translation-title');

        return $field;
    }

    public function baseSettings() {
        $form = parent::baseSettings();
        return $this->addTableSelect($form, 'languages', __('Table des langues', 'docalist-biblio'), true);
    }

    public function editSettings() {
        $form = parent::editSettings();
        return $this->addTableSelect($form, 'languages', __('Table des langues', 'docalist-biblio'));
    }

    public function displaySettings() {
        $form = parent::displaySettings();
        return $this->addTableSelect($form, 'languages', __('Table des langues', 'docalist-biblio'), true);
    }
}