<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|null id
 * @property string title
 * @property string content
 * @property string|null author_name
 * @property int|User|null author
 * @property int comments_count
 * @property DateTime created_on_datetime
 * @property DateTime|null last_commented_on_datetime
 */
class News extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_news';

    protected $fieldConf = [
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'author_name' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'title' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'content' => [
            'type' => Schema::DT_LONGTEXT,
            'nullable' => true,
        ],
        'comments' => [
            'has-many' => ['\GeoKrety\Model\NewsComment', 'news'],
        ],
        'subscriptions' => [
            'has-many' => ['\GeoKrety\Model\NewsSubscription', 'news'],
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ],
        'last_commented_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'comments_count' => [
            'type' => Schema::DT_INT2,
            'nullable' => false,
            'default' => 0,
        ],
    ];

    public function get_created_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_last_commented_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function isSubscribed(): bool {
        // Note: Cache count() for 1 second
        return $this->has('subscriptions', ['news = ? AND author = ? AND subscribed = ?', $this->id, \Base::instance()->get('SESSION.CURRENT_USER'), '1'])->count(null, null, 1) === 1;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            // 'title' => $this->title,
            // 'content' => $this->content,
            // 'author_name' => $this->author_name,
            // 'author' => $this->author->id ?? null,
            // 'comments_count' => $this->comments_count,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'last_commented_on_datetime' => $this->last_commented_on_datetime,
        ];
    }
}
