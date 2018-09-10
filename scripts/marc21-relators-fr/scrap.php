<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

/*
 * Ce script permet de recuperer les étiquettes de rôle en français du format
 * MARC21 (relator codes) à partir de la page html :
 *
 * http://www.lac-bac.gc.ca/marc/040010-220-f.html
 *
 * Les données ne sont pas disponibles sous forme structurée : on fait du
 * "scrapping" de la page en utilisant le composant Symfony DomCrawler.
 *
 * Le script génère le fichier "marc21-relators_fr.txt" (fichier CSV au format
 * thesaurus). Si le fichier existe déjà, il est écrasé sans confirmation.
*/

use Docalist\Autoloader;
use Symfony\Component\DomCrawler\Crawler;

$plugins = dirname(dirname(dirname(__DIR__)));
require_once "$plugins/docalist-core/class/Autoloader.php";

$autoloader = new Autoloader([
    'Docalist' => "$plugins/docalist-core/class",
    'Docalist\Forms' => "$plugins/docalist-core/lib/docalist-forms/class",
    'Symfony' => "$plugins/docalist-core/lib/Symfony"
]);

//$url = 'http://www.lac-bac.gc.ca/marc/040010-220-f.html';
$url = __DIR__ . '/040010-220-f.html';
$html = file_get_contents($url);

$file = fopen(__DIR__ . '/marc21-relators_fr.txt', 'w');
$delimiter = ';';
$row = [
    'code' => '',
    'label' => '',
    'USE' => '',
    'MT' => '',
    'BT' => '',
    'RT' => '',
    'description' => '',
    'SN' => '',
    'HS' => '',
];

// Génère l'entête
$header=file_get_contents(__DIR__ . '/table-header.txt');
$header = sprintf($header, date('Y'), 'marc21-relators_fr.txt', date('d/m/Y H:i:s'));
fputs($file, $header);

// Génère l'entête du fichier CSV
fputcsv($file, array_keys($row), $delimiter);

$crawler = new Crawler($html);

// Liste des termes
$terms = [];

// Tableau de conversion code -> label
$labelToCode = [];

$nb = 0;
/* @var \DOMElement $domElement */
foreach ($crawler->filter('dl') as $dl) {
    ++$nb;

    // Les 6 premiers sont les exemples
    // analyste / Témoin / Témoin oculaire / Témoignant / Graphiste / Resp de l'attr des grades
    if ($nb <= 6) continue;

    // Nouvelle ligne
    $row = array_fill_keys(array_keys($row), '');

    $dl = new Crawler($dl);
    $dt = $dl->filter('dt');
    $strong = $dt->filter('strong');
    if (count($strong)) { // descripteur
        $row['label'] = text($strong->filterXPath('text()')->text());

        $code = text($dt->filterXPath('text()')->text());
        // descripteur de la forme "<strong>Compositeur</strong> (imprimerie) [cmt]"
        if (preg_match('~(.*)\s\[(...)\]$~', $code, $match)) {
            $row['label'] .= ' ' . text($match[1]);
            $code = $match[2];
        }

        // certains codes sont de la forme "<strong>Auteur d'une dédicace</strong>   [dto]    (<em>remplace « Dédicateur »</em>)"
        // on obtient alors un code de la forme "[dto] ("
        $code = rtrim($code, ' (');

        // Enlève les crochets et stocke
        $row['code'] = rtrim(ltrim($code, '['), ']');

        $description = $dl->filterXPath('dd/text()');
        if (count($description)) {
            $row['description'] = text($description->text());
        }
    } else { // NON descripteur, pas de strong
        $row['label'] = text($dt->filterXPath('text()')->text());
        $em = $dl->filter('dd p em');
        if (count($em) && $em->text() === 'remplacé par') {
            $use = $dl->filter('dd p strong')->text();
            $use = trim($use, '« »');
            // cas particulier des devenu non-des, a un code à la fin du label
            if (preg_match('~(.*)\s\[(...)\]$~', $row['label'], $match)) {
                $row['label'] = $match[1];
                $row['code'] = $match[2];
            }
        } else {
            $use = text($dl->filterXPath('dd/text()')->text());
            if (substr($use, 0, 5) !== 'VOIR ') {
                echo 'NON DES SANS VOIR pour : ', $row['label'], "\n";
            } else {
                $use = substr($use, 5);
            }
        }

        $row['USE'] = $use;
    }

    // Stocke le terme
    $terms[] = $row;
    if (isset($labelToCode[$row['label']])) {
        echo $row['label'], " defined multiple times\n";
    }
    $labelToCode[$row['label']] = $row['code'];

    //if ($nb > 30) die();
}

// corrections des erreurs connues dans les USE
$errors = [
    'Auteur de dédicace' => "Auteur d'une dédicace",
    'Preneur de licence' => "Porteur de licence",
    'Concepteur de caractères' => "Concepteur de caractères typographiques",
    "Auteur de d'une mention de représentation" => "Auteur d'une mention de représentation",
];

// Convertit les Use et génère le fichier CSV
foreach($terms as & $row) {
    // Traduit les libellés des USE en codes
    if ($row['USE']) {
        $use = $row['USE'];
        // corrections des erreurs connues
        if (isset($errors[$use])) $use = $errors[$use];

        if (! isset($labelToCode[$use])) {
            echo "Unable to find code for label '$use'\n";
            $use = 'ERROR CODE NOT FOUND:' . $use;
        } else {
            $use = $labelToCode[$use];
        }
        $row['USE'] = $use;
    }

    // Ecrit le terme dans le fichier CSV
    fputcsv($file, $row, $delimiter);
}

// Ferme le fichier CSV
fclose($file);

// Terminé
echo "\n\n- Termine.\n\n";
exit(0);

function text($text) {
    $text = trim($text, "  \r\n\t");
    $text = preg_replace('~\s+~uim', ' ', $text);

    return $text;
}