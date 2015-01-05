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

use Docalist\Biblio\Reference\ReferenceIterator;
use Docalist\Forms\Fragment;
use Docalist\Biblio\Reference;

/**
 * Classe de base pour les exporteurs.
 *
 * Un exporteur travaille de concert avec un convertisseur pour exporter
 * des notices dans un format donné.
 *
 * Pour chacune des notices, il appelle la méthode convert() du convertisseur
 * et écrit les données obtenus dans le format de fichier attendu.
 */
class Exporter extends BaseExport {
    // Paramétres communs à tous les exporteurs
    protected static $defaultSettings = [
        // Disposition du fichier d'export généré ('inline' ou 'attachment')
        'disposition' => 'inline',

        // Type MIME du fichier d'export généré
        'mime-type' => 'text/plain',

        // Charset du fichier généré
        'charset' => 'utf-8',

        // Nom de base du fichier exporté (sans extension).
        'filename' => 'export',

        // Extension du fichier
        'extension' => '.txt',

        // Indique s'il s'agit d'un fichier binaire.
        // Si false , l'option "afficher les notices" sera proposée lors de l'export.
        'binary' => false,
    ];

    /**
     * Le convertisseur à utiliser.
     *
     * @var Converter
     */
    protected $converter;

    /**
     * Initialise l'exporteur.
     *
     * @param Converter $converter Le convertisseur à utiliser.
     *
     * @param array $settings Les paramètres de l'exporteur.
     */
    public function __construct(Converter $converter = null, array $settings = []) {
        parent::__construct($settings);
        $this->converter = $converter;
    }

    /**
     * Exporte le lot de notices passé en paramètre.
     *
     * @param ReferenceIterator $references Un itérateur contenant les
     * notices à exporter.
     */
    public function export(ReferenceIterator $references) {
        foreach($references as $reference) { /* var $ref Reference */
            $data = $this->converter->convert($reference);
            var_export($data);
            echo "\n\n";
        }
    }

    /**
     * Retourne l'entête "content-type" de la réponse http générée lors de
     * l'export.
     *
     * L'entête est généré en combinant le résultat des méthodes mimeType() et
     * charset().
     *
     * @return string Par défaut, retourne la chaine
     * "text/plain; charset=utf-8"
     */
    public final function contentType() {
        return sprintf('%s; charset=%s', $this->get('mime-type'), $this->get('charset'));
    }

    /**
     * Retourne l'entête "content-disposition" de la réponse http générée lors
     * de l'export.
     *
     * L'entête est généré en combinant le résultat des méthodes inline(),
     * filename() et extension().
     *
     * @return string Par défaut, retourne la chaine
     * "inline; filename=export.txt"
     */
    public final function contentDisposition($disposition = null) {
        is_null($disposition) && $disposition = $this->get('disposition');
        $extension = '.' . trim($this->get('extension'), '. ');
        $filename = $this->get('filename');
        $fallback = sanitize_title($filename) . $extension;
        $filename .= $extension;

        $header = sprintf('%s; filename="%s"', $disposition, $fallback);

        if ($filename !== $fallback) {
            $header .= sprintf("; filename*=utf-8''%s", rawurlencode($filename));
        }

        return $header;
    }
}