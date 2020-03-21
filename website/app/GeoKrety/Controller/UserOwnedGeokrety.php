<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use UserLoader;

class UserOwnedGeokrety extends Base {
    use UserLoader;

    public function get($f3) {
        // Load owned GeoKrety
        $geokret = new Geokret();
        $filter = ['owner = ?', $this->user->id];
        $option = ['order' => 'updated_on_datetime DESC'];
        $subset = $geokret->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_USER_OWNED_GEOKRETY, $filter, $option);
        Smarty::assign('geokrety', $subset);
        // Paginate
        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/user_geokrety_owned.tpl');
    }
}
