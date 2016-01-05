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
namespace Docalist\Biblio\Type;

use Docalist\Type\Collection;
use Docalist\Forms\TopicsInput;

/**
 * Une collection de topics d'indexation.
 */
class Topics extends Collection
{
    protected static $type = 'Docalist\Biblio\Type\Topic';

    public static function loadSchema()
    {
        return [
            'key' => 'type',
            'table' => 'table:topics',
            'editor' => 'table',
        ];
    }

    public function getEditorForm($options = null)
    {
        return new TopicsInput($this->schema->name(), $this->schema->table());
    }
}
