<?php

namespace Geokrety\Repository;

class KonkretRepository extends AbstractRepository {
    protected $count = <<<EOQUERY
SELECT  count(*) as total
FROM    `gk-geokrety` gk
EOQUERY;

    const SELECT_KONKRET = <<<EOQUERY
SELECT      gk.id, gk.nr, gk.nazwa, gk.opis, gk.data ,gk.typ, gk.droga, gk.skrzynki, gk.zdjecia, gk.owner, gk.missing,
            gk.ost_log_id, ru.data, ru.logtype, ru.koment, ru.user, us.user, ru.username,
            gk.ost_pozycja_id, ru2.waypoint, ru2.lat, ru2.lon, ru2.country, ru2.logtype, ru2.user,
            gk.avatarid, pic.plik, pic.opis,
            owner.user, holder.userid, holder.user, owner.email
FROM        `gk-geokrety` gk
LEFT JOIN   `gk-ruchy` AS ru ON (gk.ost_log_id = ru.ruch_id)
LEFT JOIN   `gk-ruchy` AS ru2 ON (gk.ost_pozycja_id = ru2.ruch_id)
LEFT JOIN   `gk-users` AS owner ON (gk.owner = owner.userid)
LEFT JOIN   `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
LEFT JOIN   `gk-users` AS holder ON (gk.hands_of = holder.userid)
LEFT JOIN   `gk-users` AS us ON (ru.user = us.userid)
EOQUERY;

    const SELECT_USER_KONKRET_INVENTORY = <<<EOQUERY
SELECT      gk.id, gk.nr, gk.nazwa, gk.opis, gk.data ,gk.typ, gk.droga, gk.skrzynki, gk.zdjecia, gk.owner, gk.missing,
            gk.ost_log_id, ru.data, ru.logtype, ru.koment, ru.user, us.user, ru.username,
            gk.ost_pozycja_id, ru2.waypoint, ru2.lat, ru2.lon, ru2.country, ru2.logtype, ru2.user,
            gk.avatarid, pic.plik,
            owner.user
FROM        `gk-geokrety` gk
LEFT JOIN   `gk-ruchy` AS ru ON (gk.ost_log_id = ru.ruch_id)
LEFT JOIN   `gk-ruchy` AS ru2 ON (gk.ost_pozycja_id = ru2.ruch_id)
LEFT JOIN   `gk-users` AS us ON (ru.user = us.userid)
LEFT JOIN   `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
LEFT JOIN   `gk-users` AS owner ON (gk.owner = owner.userid)
EOQUERY;

    const SELECT_USER_KONKRET_WATCHED = <<<EOQUERY
SELECT      gk.id, gk.nr, gk.nazwa, gk.opis, gk.data ,gk.typ, gk.droga, gk.skrzynki, gk.zdjecia, gk.owner, gk.missing,
            gk.ost_log_id, ru.data, ru.logtype, ru.koment, ru.user, us.user, ru.username,
            gk.ost_pozycja_id, ru2.waypoint, ru2.lat, ru2.lon, ru2.country, ru2.logtype, ru2.user,
            gk.avatarid, pic.plik,
            owner.user
FROM        (`gk-obserwable` ob)
LEFT JOIN   `gk-geokrety` AS gk ON (ob.id = gk.id)
LEFT JOIN   `gk-ruchy` AS ru ON (gk.ost_log_id = ru.ruch_id)
LEFT JOIN   `gk-ruchy` AS ru2 ON (gk.ost_pozycja_id = ru2.ruch_id)
LEFT JOIN   `gk-users` AS us ON (ru.user = us.userid)
LEFT JOIN   `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
LEFT JOIN   `gk-users` AS owner ON (gk.owner = owner.userid)
EOQUERY;

    public function getById($id) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);

        $where = <<<EOQUERY
  WHERE gk.id = ?
  LIMIT 1
EOQUERY;

        $sql = self::SELECT_KONKRET.$where;

        $geokrety = $this->getBySql($sql, 'i', array($id));
        if (sizeof($geokrety) > 0) {
            return $geokrety[0];
        }
    }

    public function getByTrackingCode($nr) {
        $where = <<<EOQUERY
  WHERE gk.nr = ?
  LIMIT 1
EOQUERY;

        $sql = self::SELECT_KONKRET.$where;

        $geokrety = $this->getBySql($sql, 's', array($nr));
        if (sizeof($geokrety) > 0) {
            return $geokrety[0];
        }
    }

    public function getByName($name) {
        $where = <<<EOQUERY
  WHERE     gk.nazwa LIKE ?
  ORDER BY  id DESC
  LIMIT     100
EOQUERY;

        $sql = self::SELECT_KONKRET.$where;

        return $this->getBySql($sql, 's', array("%$name%"));
    }

    public function getByVisitedCache($name) {
        $where = <<<EOQUERY
  WHERE gk.id IN (
                SELECT      DISTINCT(ru.id)
                FROM        `gk-ruchy` AS ru
                WHERE       ru.waypoint = ?
                ORDER BY    id DESC
      )
  LIMIT       100
EOQUERY;

        $sql = self::SELECT_KONKRET.$where;

        return $this->getBySql($sql, 's', array("$name"));
    }

    public function getBySql($sql, $bindStr, $bind) {
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param($bindStr, ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return array();
        }

        // associate result vars
        $stmt->bind_result($id, $trackingCode, $name, $description, $datePublished, $type, $distance, $cachesCount, $picturesCount, $ownerId, $missing,
                           $lastLogId, $lastLogDate, $lastLogLogType, $lastLogComment, $lastLogUserId, $lastLogUsername, $lastLogUsername_,
                           $lastPositionId, $lastPositionWaypoint, $lastPositionLat, $lastPositionLon, $lastPositionCountry, $lastPositionLogType, $lastPositionUserId,
                           $avatarId, $avatarFilename, $avatarCaption,
                           $ownerName, $holderId, $holderName, $ownerEmail);

        $geokrety = array();
        while ($stmt->fetch()) {
            // Workaround: Fix database encoding
            $name = html_entity_decode($name);
            $description = html_entity_decode($description);
            $ownerName = html_entity_decode($ownerName);
            $holderName = html_entity_decode($holderName);

            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->ownerName = $ownerName;
            $geokret->ownerEmail = $ownerEmail;
            $geokret->holderId = $holderId;
            $geokret->holderName = $holderName;
            $geokret->setDatePublished($datePublished);
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->avatarFilename = $avatarFilename;
            $geokret->avatarCaption = $avatarCaption;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $lastLog = new \Geokrety\Domain\TripStep($this->dblink);
            $lastLog->ruchId = $lastLogId;
            $lastLog->setDate($lastLogDate);
            $lastLog->setLogtype($lastLogLogType);
            $lastLog->setComment($lastLogComment);
            $lastLog->userId = $lastLogUserId;
            $lastLog->username = $lastLogUsername_ ? $lastLogUsername_ : $lastLogUsername;
            $geokret->lastLog = $lastLog;

            $lastPosition = new \Geokrety\Domain\TripStep($this->dblink);
            $lastPosition->ruchId = $lastLogId;
            $lastPosition->userId = $lastPositionUserId;
            $lastPosition->waypoint = $lastPositionWaypoint;
            $lastPosition->lat = $lastPositionLat;
            $lastPosition->lon = $lastPositionLon;
            $lastPosition->country = $lastPositionCountry;
            $lastPosition->setLogtype($lastPositionLogType);
            $geokret->lastPosition = $lastPosition;

            $geokret->enrichFields();
            array_push($geokrety, $geokret);
        }

        $stmt->close();

        return $geokrety;
    }

    public function getInventoryByUserId($id, $orderBy = null, $defaultWay = 'asc', $limit = null, $curPage = 1) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);
        list($order, $way) = $this->validationService->ensureOrderBy('orderBy', $orderBy, ['id', 'owner', 'ru.data', 'droga', 'skrzynki'], $defaultWay);

        $orderDate = ($order == 'ru.data' ? 'if(ru.data <> \'\', 0, 1), ' : '');
        $where = <<<EOQUERY
    WHERE     gk.hands_of = ?
    ORDER BY  $orderDate $order $way, nazwa ASC
EOQUERY;
        if (!is_null($limit)) {
            $total = self::count('WHERE gk.hands_of = ?', array('d', $id));
            $start = $this->paginate($total, $curPage, $limit);
            $where .= <<<EOQUERY
    LIMIT     $start, $limit
EOQUERY;
        }

        $sql = self::SELECT_USER_KONKRET_INVENTORY.$where;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $id)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        $geokrety = array();
        if ($nbRow == 0) {
            return array($geokrety, $total);
        }

        // associate result vars
        $stmt->bind_result($id, $trackingCode, $name, $description, $datePublished, $type, $distance, $cachesCount, $picturesCount, $ownerId, $missing,
                           $lastLogId, $lastLogDate, $lastLogLogType, $lastLogComment, $lastLogUserId, $lastLogUsername, $lastLogUsername_,
                           $lastPositionId, $lastPositionWaypoint, $lastPositionLat, $lastPositionLon, $lastPositionCountry, $lastPositionLogType, $lastPositionUserId,
                           $avatarId, $avatarFilename,
                           $ownerName);
        while ($stmt->fetch()) {
            // Workaround: Fix database encoding
            $name = html_entity_decode($name);
            $description = html_entity_decode($description);
            $ownerName = html_entity_decode($ownerName);
            $holderName = html_entity_decode($holderName);
            $lastLogComment = html_entity_decode($lastLogComment);

            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->ownerName = $ownerName;
            $geokret->setDatePublished($datePublished);
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->avatarFilename = $avatarFilename;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $lastLog = new \Geokrety\Domain\TripStep($this->dblink);
            $lastLog->ruchId = $lastLogId;
            $lastLog->setDate($lastLogDate);
            $lastLog->setLogtype($lastLogLogType);
            $lastLog->setComment($lastLogComment);
            $lastLog->userId = $lastLogUserId;
            $lastLog->username = $lastLogUsername_ ? $lastLogUsername_ : $lastLogUsername;
            $geokret->lastLog = $lastLog;

            $lastPosition = new \Geokrety\Domain\TripStep($this->dblink);
            $lastPosition->ruchId = $lastLogId;
            $lastPosition->userId = $lastPositionUserId;
            $lastPosition->waypoint = $lastPositionWaypoint;
            $lastPosition->lat = $lastPositionLat;
            $lastPosition->lon = $lastPositionLon;
            $lastPosition->country = $lastPositionCountry;
            $lastPosition->setLogtype($lastPositionLogType);
            $geokret->lastPosition = $lastPosition;

            $geokret->enrichFields();
            array_push($geokrety, $geokret);
        }

        $stmt->close();

        return array($geokrety, $total);
    }

    public function getOwnedByUserId($id, $orderBy = null, $defaultWay = 'asc', $limit = 20, $curPage = 1) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);
        list($order, $way) = $this->validationService->ensureOrderBy('orderBy', $orderBy, ['id', 'waypoint', 'ru.data', 'droga', 'skrzynki'], $defaultWay);

        $total = self::count('WHERE gk.owner = ?', array('d', $id));
        $start = $this->paginate($total, $curPage, $limit);

        $orderDate = ($order == 'ru.data' ? 'if(ru.data <> \'\', 0, 1), ' : '');
        $where = <<<EOQUERY
    WHERE     gk.owner = ?
    ORDER BY  $orderDate $order $way, nazwa ASC
    LIMIT     $start, $limit
EOQUERY;
        $sql = self::SELECT_USER_KONKRET_INVENTORY.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $id)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        $geokrety = array();
        if ($nbRow == 0) {
            return array($geokrety, $total);
        }

        // associate result vars
        $stmt->bind_result($id, $trackingCode, $name, $description, $datePublished, $type, $distance, $cachesCount, $picturesCount, $ownerId, $missing,
                           $lastLogId, $lastLogDate, $lastLogLogType, $lastLogComment, $lastLogUserId, $lastLogUsername, $lastLogUsername_,
                           $lastPositionId, $lastPositionWaypoint, $lastPositionLat, $lastPositionLon, $lastPositionCountry, $lastPositionLogType, $lastPositionUserId,
                           $avatarId, $avatarFilename,
                           $ownerName);
        $geokrety = array();
        while ($stmt->fetch()) {
            // Workaround: Fix database encoding
            $name = html_entity_decode($name);
            $description = html_entity_decode($description);
            $ownerName = html_entity_decode($ownerName);
            $lastLogComment = html_entity_decode($lastLogComment);

            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->ownerName = $ownerName;
            $geokret->setDatePublished($datePublished);
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->avatarFilename = $avatarFilename;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $lastLog = new \Geokrety\Domain\TripStep($this->dblink);
            $lastLog->ruchId = $lastLogId;
            $lastLog->setDate($lastLogDate);
            $lastLog->setLogtype($lastLogLogType);
            $lastLog->setComment($lastLogComment);
            $lastLog->userId = $lastLogUserId;
            $lastLog->username = $lastLogUsername_ ? $lastLogUsername_ : $lastLogUsername;
            $geokret->lastLog = $lastLog;

            $lastPosition = new \Geokrety\Domain\TripStep($this->dblink);
            $lastPosition->ruchId = $lastLogId;
            $lastPosition->userId = $lastPositionUserId;
            $lastPosition->waypoint = $lastPositionWaypoint;
            $lastPosition->lat = $lastPositionLat;
            $lastPosition->lon = $lastPositionLon;
            $lastPosition->country = $lastPositionCountry;
            $lastPosition->setLogtype($lastPositionLogType);
            $geokret->lastPosition = $lastPosition;

            $geokret->enrichFields();
            array_push($geokrety, $geokret);
        }

        $stmt->close();

        return array($geokrety, $total);
    }

    public function getWatchedByUserId($id, $orderBy = null, $defaultWay = 'asc', $limit = 20, $curPage = 1) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        list($order, $way) = $this->validationService->ensureOrderBy('orderBy', $orderBy, ['id', 'waypoint', 'ru.data', 'droga', 'skrzynki'], $defaultWay);

        $total = self::count('LEFT JOIN `gk-obserwable` AS ob ON (ob.id = gk.id) WHERE ob.userid = ?', array('d', $id));
        $start = $this->paginate($total, $curPage, $limit);

        $orderDate = ($order == 'ru.data' ? 'if(ru.data <> \'\', 0, 1), ' : '');
        $where = <<<EOQUERY
    WHERE     ob.userid = ?
    ORDER BY  $orderDate $order $way, nazwa ASC
    LIMIT     $start, $limit
EOQUERY;

        $sql = self::SELECT_USER_KONKRET_WATCHED.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $id)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        $geokrety = array();
        if ($nbRow == 0) {
            return array($geokrety, $total);
        }

        // associate result vars
        $stmt->bind_result($id, $trackingCode, $name, $description, $datePublished, $type, $distance, $cachesCount, $picturesCount, $ownerId, $missing,
                           $lastLogId, $lastLogDate, $lastLogLogType, $lastLogComment, $lastLogUserId, $lastLogUsername, $lastLogUsername_,
                           $lastPositionId, $lastPositionWaypoint, $lastPositionLat, $lastPositionLon, $lastPositionCountry, $lastPositionLogType, $lastPositionUserId,
                           $avatarId, $avatarFilename,
                           $ownerName);

        while ($stmt->fetch()) {
            // Workaround: Fix database encoding
            $name = html_entity_decode($name);
            $description = html_entity_decode($description);
            $ownerName = html_entity_decode($ownerName);
            $lastLogComment = html_entity_decode($lastLogComment);

            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->ownerName = $ownerName;
            $geokret->setDatePublished($datePublished);
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->avatarFilename = $avatarFilename;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $lastLog = new \Geokrety\Domain\TripStep($this->dblink);
            $lastLog->ruchId = $lastLogId;
            $lastLog->setDate($lastLogDate);
            $lastLog->setLogtype($lastLogLogType);
            $lastLog->setComment($lastLogComment);
            $lastLog->userId = $lastLogUserId;
            $lastLog->username = $lastLogUsername_ ? $lastLogUsername_ : $lastLogUsername;
            $geokret->lastLog = $lastLog;

            $lastPosition = new \Geokrety\Domain\TripStep($this->dblink);
            $lastPosition->ruchId = $lastLogId;
            $lastPosition->userId = $lastPositionUserId;
            $lastPosition->waypoint = $lastPositionWaypoint;
            $lastPosition->lat = $lastPositionLat;
            $lastPosition->lon = $lastPositionLon;
            $lastPosition->country = $lastPositionCountry;
            $lastPosition->setLogtype($lastPositionLogType);
            $geokret->lastPosition = $lastPosition;

            $geokret->enrichFields();
            array_push($geokrety, $geokret);
        }

        $stmt->close();

        return array($geokrety, $total);
    }

    public function getRecentCreation($limit = 20) {
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
    ORDER BY  id DESC
    LIMIT     $limit
EOQUERY;

        $sql = self::SELECT_USER_KONKRET_INVENTORY.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        $geokrety = array();
        if ($nbRow == 0) {
            return $geokrety;
        }

        // associate result vars
        $stmt->bind_result($id, $trackingCode, $name, $description, $datePublished, $type, $distance, $cachesCount, $picturesCount, $ownerId, $missing,
                           $lastLogId, $lastLogDate, $lastLogLogType, $lastLogComment, $lastLogUserId, $lastLogUsername, $lastLogUsername_,
                           $lastPositionId, $lastPositionWaypoint, $lastPositionLat, $lastPositionLon, $lastPositionCountry, $lastPositionLogType, $lastPositionUserId,
                           $avatarId, $avatarFilename,
                           $ownerName);

        while ($stmt->fetch()) {
            // Workaround: Fix database encoding
            $name = html_entity_decode($name);
            $description = html_entity_decode($description);
            $ownerName = html_entity_decode($ownerName);
            $lastLogComment = html_entity_decode($lastLogComment);

            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->ownerName = $ownerName;
            $geokret->setDatePublished($datePublished);
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->avatarFilename = $avatarFilename;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $lastLog = new \Geokrety\Domain\TripStep($this->dblink);
            $lastLog->ruchId = $lastLogId;
            $lastLog->setDate($lastLogDate);
            $lastLog->setLogtype($lastLogLogType);
            $lastLog->setComment($lastLogComment);
            $lastLog->userId = $lastLogUserId;
            $lastLog->username = $lastLogUsername_ ? $lastLogUsername_ : $lastLogUsername;
            $geokret->lastLog = $lastLog;

            $lastPosition = new \Geokrety\Domain\TripStep($this->dblink);
            $lastPosition->ruchId = $lastLogId;
            $lastPosition->userId = $lastPositionUserId;
            $lastPosition->waypoint = $lastPositionWaypoint;
            $lastPosition->lat = $lastPositionLat;
            $lastPosition->lon = $lastPositionLon;
            $lastPosition->country = $lastPositionCountry;
            $lastPosition->setLogtype($lastPositionLogType);
            $geokret->lastPosition = $lastPosition;

            $geokret->enrichFields();
            array_push($geokrety, $geokret);
        }

        $stmt->close();

        return $geokrety;
    }

    public function getCountryTrack($gkId) {
        $gkId = $this->validationService->ensureIntGTE('gkid', $gkId, 1);

        $sql = <<<EOQUERY
SELECT    country, COUNT(*) as count
FROM      (SELECT @r := @r + (@country COLLATE utf8mb4_general_ci != country) AS gn,
                  @country := country AS sn,
                  s.*
           FROM   (SELECT @r := 0, @country := '') vars,
                   `gk-ruchy` as s
           WHERE id = ?
           AND s.lat is not null
           AND s.lon is not null
           ORDER BY data_dodania asc, data
          ) q
GROUP BY  gn
EOQUERY;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $gkId)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        $steps = array();
        if ($nbRow == 0) {
            return $steps;
        }

        // associate result vars
        $stmt->bind_result($country, $count);

        while ($stmt->fetch()) {
            $step = new \Geokrety\Domain\CountryTrackStep();
            $step->country = $country;
            $step->count = $count;

            array_push($steps, $step);
        }

        $stmt->close();

        return $steps;
    }

    public function hasUserTouched($userId, $gkId) {
        if (is_null($userId)) {
            return false;
        }
        $userId = $this->validationService->ensureIntGTE('userid', $userId, 1);
        $gkId = $this->validationService->ensureIntGTE('gkid', $gkId, 1);

        $sql = <<<EOQUERY
SELECT  user FROM `gk-ruchy`
WHERE   id = ?
AND     user = ?
AND     logtype <> '2'
LIMIT   1
EOQUERY;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('dd', $gkId, $userId)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows;
    }

    public function getStatsByUserId($userId) {
        if (is_null($userId)) {
            return false;
        }
        $userId = $this->validationService->ensureIntGTE('userid', $userId, 1);

        $sql = <<<EOQUERY
SELECT COUNT(id), COALESCE(SUM(droga),0)
FROM `gk-geokrety`
WHERE owner = ?
AND typ != '2'
LIMIT 1
EOQUERY;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $userId)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return array();
        }

        // associate result vars
        $stmt->bind_result($count, $distance);
        $stmt->fetch();
        $stmt->close();

        return array(
            'count' => $count,
            'distance' => $distance,
        );
    }

    public function updateGeokret(\Geokrety\Domain\Konkret &$geokret) {
        $sql = <<<EOQUERY
UPDATE  `gk-geokrety`
SET     nr = ?, nazwa = ?, opis = ?, data = ?, typ = ?, droga = ?, skrzynki = ?,
        zdjecia = ?, owner = ?, missing = ?, ost_log_id = ?, ost_pozycja_id = ?,
        avatarid = ?
WHERE   id = ?
LIMIT   1
EOQUERY;
        $bind = array(
            $geokret->trackingCode, $geokret->name, $geokret->description,
            $geokret->getDatePublished(), $geokret->type, $geokret->distance,
            $geokret->cachesCount, $geokret->picturesCount, $geokret->ownerId,
            $geokret->missing, $geokret->lastLogId, $geokret->lastPositionId,
            $geokret->avatarId,
            $geokret->id,
        );

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('sssssiiiiiiiii', ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->store_result();

        if ($stmt->affected_rows >= 0) {
            return true;
        }

        danger(_('Failed to update GeoKret…'));

        return false;
    }
}