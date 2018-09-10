<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */

/*
 * Ce script permet de recuperer les tables d'autorites de la Library of
 * Congress (http://id.loc.gov/) et de créer un fichier CSV contenant toutes
 * les entrées.
 *
 * On fait d'abord une requête pour récupérer la liste des termes de la table
 * puis une requête pour chaque terme afin de récupérer les détails du terme.
 *
 * On aurait pu également utiliser le service "download" de la LC, mais lors
 * de nos tests il s'est avéré que ces fichiers n'étaient pas à jour.
 */

// Le script doit être lancé en ligne de commande
if (php_sapi_name() !== 'cli') {
    die('Ce script soit être lancé en ligne de commande');
}

// Vérifie les paramètres
if ($argc < 2) {
    echo $argv[0], " : permet de recuperer les tables d'autorites de la Library of Congress.\n";
    echo "\n";
    echo "Usage : php ", $argv[0], " <authority>\n";
    echo "\n";
    echo "- <autority> : nom de la table a recuperer (relators, countries, iso693-1, etc.)\n";
    echo "               Consultez le site http://id.loc.gov/ pour consulter les tables disponibles.\n";
    echo "\n";
    echo "Le script genere un fichier CSV contenant toutes les entrees de la table.\n";
    echo "Attention, si le fichier CSV existe deja, il est ecrase sans confirmation.\n";

    exit(0);
}

// Stocke le nom de la table à récupérer
$table = $argv[1];

// tables testées :
// relators : ok
// countries : ok
// iso639-1 : ok


//     set_time_limit(180);
//     while(ob_get_level()) ob_end_clean();

// Stucture du fichier csv
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

// Ouvre le fichier CSV
$path = getcwd() . '/' . $table . '.txt';
echo "- Creation du fichier $path\n";
$file = fopen($path, 'w');
if (! is_resource($file)) die("Impossible de creer le fichier.\n");

// Génère l'entête
$header=file_get_contents(__DIR__ . '/table-header.txt');
$header = sprintf($header, date('Y'), $table, date('d/m/Y H:i:s'));
fputs($file, $header);

// Génère l'entête du fichier CSV
fputcsv($file, array_keys($row), $delimiter);

// Charge la librairie EasyRdf
require_once __DIR__ . '/../../lib/EasyRdf/lib/EasyRdf.php';

// important : easyrdf n'autorise pas un littéral à avoir à la fois les attributs
// lang et datatype, or c'e'st les cas dans les fichiers de la LC, au moins pour
// la table "relators".
// Le fichier EasyRdf\Graph.php a été modifié pour que cela fonctionne (mise
// en commentaire du teste, lignes 544-548)

// Indique à EasyRdf l'espace de nom utilisé pour MADS
EasyRdf_Namespace::set('mads', 'http://www.loc.gov/mads/rdf/v1#');

// Charge le graphe RDF de la table d'autorité
$url = "http://id.loc.gov/vocabulary/$table.rdf";
echo "- Chargement du fichier $url\n";
$graph = EasyRdf_Graph::newAndLoad($url);

// Récupère la liste de tous les termes
$relators = $graph->allOfType('mads:Authority');

// Génère toutes les entrées
echo "- Generation du fichier CSV, ", count($relators), " entrees :\n";
$nb = 0;

/* @var EasyRdf_Resource $relator */
$rows = [];
foreach($relators as $relator) {
    // Nouvelle ligne
    $row = array_fill_keys(array_keys($row), '');

    // Récupère l'URI de ce code
    $uri = $relator->getUri();

    // Charge le graphe de ce code
    $relatorGraph = EasyRdf_Graph::newAndLoad($uri . '.rdf');

    // Récupère le noeud principal du code
    $relator = $relatorGraph->resource($uri);

    // Crée l'entrée
    $row['code'] = $relator->get('mads:code');
    $row['label'] = $relator->get('mads:authoritativeLabel');
    $row['USE'] = codesFor($relator, 'mads:useInstead');
    $row['MT'] = '';
    $row['BT'] = codesFor($relator, 'mads:hasBroaderAuthority');
    $row['description'] = $relator->get('mads:definitionNote');
    $row['RT'] = codesFor($relator, 'mads:see');
    $row['SN'] = $relator->get('mads:editorialNote');
    $row['HS'] = history($relator);

    // Ecrit le terme dans le fichier CSV
    // fputcsv($file, $row, $delimiter);
    $rows[sortkey($row, count($rows))] = $row;

    // Si le terme a des variantes, génère un nondes pour chaque variante
    foreach($relator->all('mads:hasVariant') as $variant) {
        // Nouvelle ligne
        $row = array_fill_keys(array_keys($row), '');

        // Crée la variante
        $row['label'] = $variant->get('mads:variantLabel');
        $row['USE'] = $relator->get('mads:code');

        // Ecrit le terme dans le fichier CSV
        // fputcsv($file, $row, $delimiter);
        $rows[sortkey($row, count($rows))] = $row;
    }


    if (0 === $nb % 10) printf("\n%4d : ", 10 * floor($nb / 10));
    echo $relator->get('mads:code'), ' ';

    ++$nb;
    // if ($nb > 12) break;
}

ksort($rows);
foreach($rows as $row) {
    fputcsv($file, $row, $delimiter);
}
// Ferme le fichier CSV
fclose($file);

// Terminé
echo "\n\n- Termine : $path\n\n";
exit(0);

/**
 * Retourne une chaine contenant les codes pour la propriété passée en paramètre.
 *
 * @param EasyRdf_Resource $relator
 * @param string $property mads:useInstead, mads:hasBroaderAuthority, mads:see...
 *
 * @return string
 */
function codesFor(EasyRdf_Resource $relator, $property) {
    $t = [];
    foreach($relator->all($property) as $rel) {
        $t[] = $rel->get('mads:code'); // . $rel->get('mads:authoritativeLabel')
    }

    return implode("\n", $t);
}

function history(EasyRdf_Resource $relator) {
    $t = [];
    foreach($relator->all('mads:adminMetadata') as $meta){
        $t[] = sprintf('%s:%s',
            $meta->get('<http://id.loc.gov/ontologies/RecordInfo#recordChangeDate>')->format('Y-m-d'),
            $meta->get('<http://id.loc.gov/ontologies/RecordInfo#recordStatus>')
        );
    }

    // tri par date décroissante
    rsort($t);

    return implode("\n", $t);
}

function sortkey(array $row, $count) {
    $key = $row['USE'] ? $row['USE'] : $row['code'];
    $key.= $row['USE'] ? $row['USE'] : '---';
    $key.= substr('0000' . $count, -4);

    return $key;
}