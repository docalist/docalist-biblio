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
namespace Docalist\Biblio\Views;

use Docalist\Biblio\Pages\AdminDatabases;
use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Biblio\Settings\TypeSettings;
use Docalist\Schema\Schema;

/**
 * grid to php
 *
 * @var AdminDatabases      $this
 * @var DatabaseSettings    $database   La base à éditer.
 * @var int                 $dbindex    L'index de la base.
 * @var TypeSettings        $type       Le type à éditer.
 * @var int                 $typeindex  L'index du type.
 * @var Schema              $grid       La grille à éditer.
 * @var string              $gridname   L'index de la grille.
 * @var Schema              $base       La grille à éditer.
 * @var bool                $diffonly   Mode de comparaison (grille complète / différences uniquement).
 */
$urlfull = esc_url($this->url('GridToPhp', $dbindex, $typeindex, $gridname));
$urldiff = esc_url($this->url('GridToPhp', $dbindex, $typeindex, $gridname, true));
echo '<pre>', htmlspecialchars(json_encode($grid, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)), '</pre>';
die();
?>
<div class="wrap">
    <h1><?= sprintf(__('Code PHP de la grille "%s" pour le type "%s"', 'docalist-biblio'), $gridname, $typeindex) ?></h1>

	<p class="description">
        <?= __("Le code PHP ci-dessous peut être utilisé pour générer une grille identique.", 'docalist-biblio') ?>
    </p>

    <p>
        <?php if ($diffonly) :?>
            Seules les différences par rapport à la grille prédéfinie sont affichées.
            <a href="<?= $urlfull ?>">Afficher la grille complète...</a>
        <?php else: ?>
            La grille complète est affichée.
            <a href="<?= $urldiff ?>">Afficher uniquement les différences par rapport à la grille prédéfinie...</a>
        <?php endif ?>
    </p>
<?php

$properties = $grid->value();
$fields = $properties['fields'];
unset($properties['fields']);
//var_dump($base);die();
$compact = false; // true = propriétés sur une seule ligne, false = une ligne par propriété
echo '<textarea class="large-text code" rows="35" readonly style="white-space: pre; word-wrap: normal; overflow-x: scroll">';
echo "return new Schema([\n";

    // Propriétés de la grille
    foreach($properties as $key => $value) {
        if ($base->$key != $value) {
            $value = varExport($value, $key);
            echo '    ', var_export($key, true), ' => ', $value, ",\n";
        }
    }

    // Liste des champs
    echo "    'fields' => [\n";
    $format = ''; // propriété format du dernier groupe rencontré
    foreach ($fields as $name => $field) {

        // Récupère les propriétés du champ
        $properties = $field->value();

        // Supprime name des propriétés comme on l'affiche comme clé
        unset($properties['name']);

        // Si c'est un groupe, passe une ligne et génère un commentaire
        if ($field->type() === 'Docalist\Biblio\Type\Group') {
            echo "\n        // ", $field->label(), "\n";
            $properties = ['type' => $field->type()] + $properties; // type en premier
            $format = $field->format();
        }

        // Supprime les propriétés qui ont la valeur par défaut (dans base)
        if ($base->hasField($name)) {
            foreach ($properties as $key => $value) {
                if ($value == $base->getField($name)->$key) {
                    unset($properties[$key]);
                }
                if (isset($properties[$key . 'spec'])) { // exemple : si on a labelspec, label est juste une copie
                    unset($properties[$key]);
                }
            }
        }

        // Si c'est un champ dans un groupe sans format (i.e. non affiché)
        if (($gridname !== 'base') && empty($format) && $field->type() !== 'Docalist\Biblio\Type\Group') {
            $properties = [];
        }

        // Aucune propriété spécifique, génère uniquement le nom du champ
        if (empty($properties)) {
            echo '        ', var_export($name, true), ",\n";
            continue;
        }

        // Affiche les propriétés
        echo '        ', var_export($name, true), " => [ ";
        !$compact && print("\n");
        $i = 0;
        foreach ($properties as $key => $value) {
            $value = varExport($value, $key);
            !$compact && print('            ');
            echo var_export($key, true), ' => ', $value;
            ++$i < count($properties) && print(',');
            !$compact && print("\n");
        }
        !$compact && print('        ');
        echo " ],\n";
    }
    echo "    ]\n";

echo "]);";
echo '</textarea>';

function varExport($value, $key = '') {
    if (is_string($value)) {
        if ($key === 'explode' && (bool) $value) {
            $value = 'true';
        }

        elseif (($key === 'limit' || $key === 'maxlen') && (int) $value) {
            $value = (int) $value;
        }

        elseif (strpos($value, "\n") !== false || strpos($value, "'") !== false) {
            $value = str_replace(
        		['\\', '"', "\n", "\r", "\t"],
        		['\\\\', '\"', '\n', '\r', '\t'],
        		$value);
            $value = '"' . htmlspecialchars($value) . '"';
        }
        else {
            $value = "'" . htmlspecialchars($value) . "'";
        }

    } elseif (is_array($value)) {
        foreach($value as $key => & $item) {
            $item = htmlspecialchars(varExport($item));
            is_string($key) && $item = var_export($key, true) . ' => ' . $item;
        }
        $value = '['. implode(', ', $value) . ']';
    } else {
	   $value = var_export($value, true);
    }

    if ($key === 'label' || $key ==='description' || $key === 'labelspec' || $key ==='descriptionspec') {
        $value = "__($value, 'docalist-biblio')";
    }
	return $value;
}
?>
</div>