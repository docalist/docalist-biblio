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

use Docalist\Type\Collection as BaseCollection;
use Docalist\Forms\Tag;

/**
 * Type de base pour tous les champs répétables.
 */
class Repeatable extends BaseCollection {
    use SettingsFormTrait;

    public function editForm() {
        return new Tag('p', 'la classe ' . get_class($this) . ' doit implémenter editForm().');
    }
}