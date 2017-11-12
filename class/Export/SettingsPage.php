<?php
/**
 * This file is part of the "Docalist Biblio" plugin.
 *
 * Copyright (C) 2015-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Export;

use Docalist\AdminPage;
use Exception;

/**
 * Options de configuration du plugin.
 */
class SettingsPage extends AdminPage
{
    /**
     * Action par défaut du contrôleur.
     *
     * @var string
     */
    protected $defaultAction = 'ExportSettings';

    /**
     * Paramètres du plugin.
     *
     * @var Settings
     */
    protected $settings;

    /**
     * Crée la page de réglages des paramètres du plugin.
     *
     * @param Settings $settings Paramètres du plugin.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        parent::__construct(
            'docalist-biblio-export-settings',          // ID
            'options-general.php',                      // page parent
            __('Export et biblios', 'docalist-biblio')  // libellé menu
        );
    }

    /**
     * Paramètres de l'export.
     */
    public function actionExportSettings()
    {
        if ($this->isPost()) {
            try {
                $_POST = wp_unslash($_POST);
                $this->settings->exportpage = (int) $_POST['exportpage'];
                $this->settings->limit = $_POST['limit'];

                // $settings->validate();
                $this->settings->save();

                docalist('admin-notices')->success(
                    __('Options enregistrées.', 'docalist-biblio')
                );

                return $this->redirect($this->url($this->defaultAction()), 303);
            } catch (Exception $e) {
                docalist('admin-notices')->error($e->getMessage());
            }
        }

        return $this->view('docalist-biblio:export/settings/export', [
            'settings' => $this->settings,
        ]);
    }
}
