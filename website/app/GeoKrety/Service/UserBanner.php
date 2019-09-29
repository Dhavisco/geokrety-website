<?php

namespace GeoKrety\Service;

use GeoKrety\LogType;
use GeoKrety\Model\User;

/**
 * UserBanner.
 */
class UserBanner {
    const TEXT_SIZE_BIG = 13;
    const TEXT_SIZE_SMALL = 9;

    public static function get_banner_url(User $user) {
        return S3Client::instance(true)->getObjectUrl(
             GK_BUCKET_STATPIC_NAME,
             sprintf('%d.png', $user->id)
        );
    }

    public static function generate(User $user) {
        $f3 = \Base::instance();

        // GeoKrety moved stats
        $geokretyMoved = $f3->get('DB')->exec(
            'SELECT COUNT(*) AS count, COALESCE(SUM(distance), 0) AS distance FROM `gk-moves` WHERE author = ? AND logtype NOT IN (?, ?)',
            array(
                $user->id,
                LogType::LOG_TYPE_COMMENT,
                LogType::LOG_TYPE_ARCHIVED,
            )
        );

        // GeoKrety owned stats
        $geokretyOwned = $f3->get('DB')->exec(
            'SELECT COUNT(*) AS count, COALESCE(SUM(distance), 0) AS distance FROM `gk-geokrety` WHERE owner = ?',
            array(
                $user->id,
            )
        );

        $img = new \Image(sprintf('%s/statpics/templates/%d.png', $f3->get('UI'), $user->statpic_template_id));
        $raw = $img->data();
        $font = sprintf('%s/fonts/%s', $f3->get('UI'), GK_USER_STATPIC_FONT);

        $text_color_black = imagecolorallocate($raw, 0, 0, 0);

        // username
        imagettftext($raw,
            self::TEXT_SIZE_BIG, 0, 74, 16,
            $text_color_black, $font, $user->username
        );

        // labels
        imagettftext($raw, self::TEXT_SIZE_SMALL, 0, 74, 31, $text_color_black, $font, sprintf('moved: %d GK %d km', $geokretyMoved[0]['count'], $geokretyMoved[0]['distance']));
        imagettftext($raw, self::TEXT_SIZE_SMALL, 0, 74, 46, $text_color_black, $font, sprintf('owns: %d GK %d km', $geokretyOwned[0]['count'], $geokretyOwned[0]['distance']));

        // Send the raw image back to \Image()
        ob_start();
        imagepng($raw);
        $img->load(ob_get_clean());

        // Store file
        S3Client::instance()->putObject([
            'Bucket' => GK_BUCKET_STATPIC_NAME,
            'Key' => sprintf('%d.png', $user->id),
            'Body' => $img->dump('png', 9),
        ]);
    }
}
