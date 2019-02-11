<?php declare(strict_types=1);
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Biblio\Import\Crossref;

use Docalist\Data\Import\Importer\StandardImporter;
use Docalist\Biblio\Import\Crossref\CrossrefReader;
use Docalist\Biblio\Import\Crossref\CrossrefConverter;

/**
 * Importeur CrossRef.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class CrossrefImporter extends StandardImporter
{
    public function __construct()
    {
        parent::__construct(new CrossrefReader(), new CrossrefConverter());
    }

    public static function getID(): string
    {
        return 'crossref';
    }

    public function getLabel(): string
    {
        return __('Fichier CrossRef', 'docalist-biblio');
    }

    public function getDescription(): string
    {
        return __(
            "Importe les références (works) qui figurent dans un fichier généré depuis l'API REST de CrossRef",
            'docalist-biblio'
        );
    }
}
