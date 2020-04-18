<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property int|DateTime log_datetime
 * @property int author
 * @property string event
 * @property string|null old_value
 * @property string|null new_value
 */
class AuditLog extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_audit_logs';

    protected $fieldConf = [
        'log_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'author' => [
            'type' => Schema::DT_BIGINT,
            'nullable' => false,
        ],
        'event' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ],
        'ip' => [
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ],
//        'old_value' => [
////            'type' => Schema::DT_JSON,
//            'nullable' => true,
//            'validate' => 'is_date',
//        ],
//        'new_value' => [
////            'type' => Schema::DT_JSON,
//            'nullable' => true,
//            'filter' => 'trim|HTMLPurifier',
//        ],
    ];

    public function get_log_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->author = \Base::instance()->get('SESSION.CURRENT_USER');
            $self->ip = \Base::instance()->get('IP');
        });
    }

    public function jsonSerialize() {
        return [
            'log_datetime' => $this->log_datetime,
            'event' => $this->event,
        ];
    }
}
