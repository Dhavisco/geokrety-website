-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(4);

-- const USER_ACCOUNT_INVALID = 0;
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "account_valid") VALUES (1, 'username1', '127.0.0.1', 0::smallint)$$);
-- const USER_ACCOUNT_VALID = 1;
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "account_valid") VALUES (2, 'username2', '127.0.0.1', 1::smallint)$$);

SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "account_valid") VALUES (3, 'username3', '127.0.0.1', 2::smallint)$$);
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "account_valid") VALUES (4, 'username4', '127.0.0.1', 3::smallint)$$);


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
;
