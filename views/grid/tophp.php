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
namespace Docalist\Biblio\Views;

use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Biblio\Settings\TypeSettings;
use Docalist\Schema\Schema;
use Docalist\Schema\Field;
use Docalist\Forms\Fragment;

/**
 * grid to php
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param int $dbindex L'index de la base.
 * @param TypeSettings $type Le type à éditer.
 * @param int $typeindex L'index du type.
 * @param Schema $grid La grille à éditer.
 * @param string $gridname L'index de la grille.
 */

/* @var $database DatabaseSettings */
/* @var $type TypeSettings */
/* @var $grid Schema */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('Code PHP de la grille "%s" pour le type "%s"', 'docalist-biblio'), $gridname, $typeindex) ?></h2>

    <p class="description">
        <?= __("Le code PHP ci-dessous peut être utilisé pour générer une grille identique.", 'docalist-biblio') ?>
    </p>

<?php
$properties = $grid->toArray();
$fields = $properties['fields'];
unset($properties['fields']);

echo '<textarea class="large-text code" rows="35" cols="500" readonly>';
echo "return [\n";

    foreach($properties as $key => $value) {
        $value = varExport($value, $key);
        echo '    ', var_export($key, true), ' => ', $value, ",\n";
    }

    echo "    'fields' => [\n";
    foreach ($fields as $key => $field) {

    	if ($field['type'] === 'Docalist\Biblio\Type\Group') {
    	    echo "\n        // ", $field['label'], "\n";
    	}
    	echo '        ', var_export($key, true), " => [\n";
    	foreach ($field as $key => $value) {
        	$value = varExport($value, $key);
        	echo '            ', var_export($key, true), ' => ', $value, ",\n";
    	}
    	echo "        ]\n";
    }
    echo "    ]\n";

echo "];";
echo '</textarea>';

function varExport($value, $key = '') {
//	if (false === strpos($value, "'")) {}
    if (is_string($value)) {
        if (strpos($value, "\n") !== false || strpos($value, "'") !== false) {
            // If the string contains a line break or a single quote, use the
            // double quote export mode. Encode backslash and double quotes and
            // transform some common control characters.
            $value = str_replace(
        		['\\', '"', "\n", "\r", "\t"],
        		['\\\\', '\"', '\n', '\r', '\t'],
        		$value);
            $value = '"' . $value . '"';
        }
        else {
            $value = "'" . $value . "'";
        }

    } else {
	   $value = var_export($value, true);
    }

	$value = htmlspecialchars($value);
    if ($key === 'label' || $key ==='description') {
        $value = "__($value, 'docalist-biblio')";
    }
	return $value;
}

?>
</div>