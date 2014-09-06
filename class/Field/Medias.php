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
use Docalist\Forms\TableLookup;

/**
 * Une collection de supports de documents.
 */
class Medias extends Repeatable {
    static protected $type = 'Media';

    public function editForm() {
        $field = new TableLookup($this->schema->name(), $this->schema->table());
        $field->multiple(true);

        return $field;
    }

    public function settingsForm() {
        $form = parent::settingsForm();
        return $this->addTableSelect($form, 'medias', __('Table des supports', 'docalist-biblio'));
    }

    public function formatSettings() {
        $form = parent::formatSettings();
        return $this->addTableSelect($form, 'medias', __('Table des supports', 'docalist-biblio'), true);
    }
}