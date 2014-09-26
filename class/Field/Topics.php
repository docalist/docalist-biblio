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
use Docalist\Forms\TopicsInput;

/**
 * Une collection de topics d'indexation.
 */
class Topics extends Repeatable {
    static protected $type = 'Topic';

    public function editForm() {
        return new TopicsInput($this->schema->name(), $this->schema->table());
    }

    public function baseSettings() {
        $form = parent::baseSettings();
        return $this->addTableSelect($form, 'topics', __('Table des vocabulaires', 'docalist-biblio'));
    }

    public function editSettings() {
        $form = parent::editSettings();
        return $this->addTableSelect($form, 'topics', __('Table des vocabulaires', 'docalist-biblio'), true);
    }

    public function displaySettings() {
        $form = parent::displaySettings();
        return $this->addTableSelect($form, 'topics', __('Table des vocabulaires', 'docalist-biblio'), true);
    }
}