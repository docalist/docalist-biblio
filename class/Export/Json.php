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
namespace Docalist\Biblio\Export;

use Docalist\Biblio\Reference;
use Docalist\Biblio\Entity\ReferenceIterator;

/**
 * Un exporteur au format JSON.
 */
class Json extends AbstractExporter {
    public function mimeType() {
        return 'application/json';
    }

    public function extension() {
        return 'json';
    }

    public function export(ReferenceIterator $references) {
        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $this->get('pretty') && $options |= JSON_PRETTY_PRINT;

        foreach($references as $key => $data) {
            echo json_encode($data, $options);
        }
    }
}