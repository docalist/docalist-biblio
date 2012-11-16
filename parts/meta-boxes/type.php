<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Title:       Nature du document
 * Post Type:   dclrecord
 * Order:       10
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
use Docalist\Utils;

piklist('field', array(
    'type' => 'radio',
    'field' => 'type',
    'label' => 'Type de document',
    'choices' => Utils::choices('record_type'),
));

piklist('field', array(
    'type' => 'checkbox',
    'field' => 'genre',
    'label' => 'Genre de document',
    'choices' => Utils::choices('record_genre'),
));

piklist('field', array(
    'type' => 'checkbox',
    'field' => 'media',
    'label' => 'Support de document',
    'choices' => Utils::choices('record_media'),
));
