<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class GeokretyMap extends Base {
    public function get($f3) {
        Smarty::render('pages/geokrety_map.tpl');
    }

    public function geojson($f3) {
//        header('Content-Type: application/json; charset=utf-8');
        $xmin = $f3->get('PARAMS.xmin');
        $ymin = $f3->get('PARAMS.ymin');
        $xmax = $f3->get('PARAMS.xmax');
        $ymax = $f3->get('PARAMS.ymax');

        if (!(is_numeric($xmin) && is_numeric($ymin) && is_numeric($xmax) && is_numeric($ymax))) {
            die();
        }

        //        $sql = <<<EOT
        //            SELECT json_build_object(
        //                'type', 'FeatureCollection',
        //                'features', json_agg(public.ST_AsGeoJSON(t.*)::json)::jsonb
        //            ) AS geojson
        //            FROM (
        //                SELECT position, gkid, name, waypoint, lat, lon, elevation, country, distance, author, author_username,
        //                    moved_on_datetime, caches_count, avatar_key,
        //                    coalesce(TRUNC(EXTRACT(EPOCH FROM (NOW() - moved_on_datetime))/86400), 0) AS days
        //                FROM "gk_geokrety_in_caches"
        //                WHERE position IS NOT NULL
        //            ) as t;
        //EOT;
        $sql = <<<EOT
            WITH
                envelope AS (SELECT public.ST_MakeEnvelope(?, ?, ?, ?, 4326)::geometry AS boundingBox),
                area AS (SELECT public.ST_Area(envelope.boundingBox) AS area, CASE WHEN public.ST_Area(envelope.boundingBox) > 150000 THEN 365 ELSE 730 END AS age FROM envelope)
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features',  coalesce(json_agg(public.ST_AsGeoJSON(t.*)::json)::jsonb, '[]'::jsonb)
            ) AS geojson
            FROM (
                SELECT position, gkid, name, waypoint, lat, lon, elevation, country, distance, author, author_username,
                    moved_on_datetime, caches_count, avatar_key, area.area, area.age, owner, owner_username,
                    coalesce(TRUNC(EXTRACT(EPOCH FROM (NOW() - moved_on_datetime))/86400), 0) AS days
                FROM "gk_geokrety_in_caches", envelope, area
                WHERE public.ST_Intersects(position::geometry, envelope.boundingBox)
                AND coalesce(TRUNC(EXTRACT(EPOCH FROM (NOW() - moved_on_datetime))/86400), 0) < area.age
                ORDER BY days DESC
            ) as t;
EOT;
//        echo $sql;
        $result = $f3->get('DB')->exec($sql, [$xmin, $ymin, $xmax, $ymax]);
        die($result[0]['geojson']);
    }
}
