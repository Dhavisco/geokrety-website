<?php

namespace GeoKrety\Controller\Admin\Traits;

use Base;
use GeoKrety\Model\Scripts;
use GeoKrety\Service\Smarty;

trait ScriptLoader {
    protected Scripts $script;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);
        $script_id = $f3->get('PARAMS.scriptid');

        $script = new Scripts();
        $this->script = $script;
        $this->filterHook();
        $script->load(['id = ?', $script_id]);
        if ($script->dry()) {
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            exit();
        }
        Smarty::assign('script', $script);
    }

    protected function filterHook() {
        // empty
    }
}