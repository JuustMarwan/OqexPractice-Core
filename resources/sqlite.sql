-- #! sqlite
-- #{ oqex-practice

-- # { resets

-- # { init
CREATE TABLE IF NOT EXISTS resets
(
    id int PRIMARY KEY DEFAULT 0,
    daily TEXT NOT NULL,
    weekly TEXT NOT NULL,
    monthly TEXT NOT NULL
);
-- # }

-- # { create
-- #    :daily string
-- #    :weekly string
-- #    :monthly string
INSERT INTO resets (id, daily, weekly, monthly)
VALUES (0, :daily, :weekly, :monthly) ON CONFLICT DO NOTHING;
-- # }

-- # { daily

-- # { get
SELECT daily
FROM resets
WHERE id = 0;
-- # }

-- # { set
-- #    :time string
UPDATE resets
SET daily =:time
WHERE id = 0;
-- # }

-- # }

-- # { weekly

-- # { get
SELECT weekly
FROM resets
WHERE id = 0;
-- # }

-- # { set
-- #    :time string
UPDATE resets
SET weekly =:time
WHERE id = 0;
-- # }

-- # }

-- # { monthly

-- # { get
SELECT monthly
FROM resets
WHERE id = 0;
-- # }

-- # { set
-- #    :time string
UPDATE resets
SET monthly =:time
WHERE id = 0;
-- # }

-- # }

-- # }

-- # { players

-- # { init
CREATE TABLE IF NOT EXISTS players
(
    uuid     VARCHAR(36) PRIMARY KEY,
    username VARCHAR(16) NOT NULL,
    op int NOT NULL DEFAULT 0,
    joined TEXT NOT NULL,
    joinedPos int NOT NULL,
    rGames  int NOT NULL DEFAULT 20,
    rank    TEXT NOT NULL DEFAULT 'guest',
    tempRank TEXT DEFAULT NULL,
    expires TEXT DEFAULT NULL,
    eGames  int NOT NULL DEFAULT 0,
    coins   int NOT NULL DEFAULT 0,
    banned TEXT DEFAULT NULL,
    muted TEXT DEFAULT NULL,
    addresses TEXT NOT NULL default '[]',
    clientRandomIds TEXT NOT NULL default '[]',
    deviceIds TEXT NOT NULL default '[]',
    selfSignedIds TEXT NOT NULL default '[]',
    xuids TEXT NOT NULL default '[]',
    FOREIGN KEY (rank) REFERENCES ranks(rank) ON DELETE SET DEFAULT
    );
-- # }

-- # { all
SELECT *
FROM players;
-- # }

-- # { get
-- #    :uuid string
SELECT *
FROM players
WHERE uuid=:uuid;
-- # }

-- # { fetch
-- #    :username string
SELECT *
FROM players
WHERE username=:username;
-- # }

-- # { create
-- #    :uuid string
-- #    :username string
-- #    :joined string
INSERT INTO players (uuid, username, joined, joinedPos, rank)
SELECT :uuid, :username, :joined, (SELECT COUNT(*) + 1 FROM players), 'guest' ON CONFLICT DO UPDATE SET username = :username;
-- # &
INSERT INTO elos(uuid, ladder)
SELECT :uuid, ladder FROM ladders WHERE NOT EXISTS(SELECT 1 FROM elos WHERE uuid = :uuid AND elos.ladder = ladders.ladder);
-- # &
INSERT INTO games(uuid, game)
SELECT :uuid, ladder FROM ladders WHERE NOT EXISTS(SELECT 1 FROM games WHERE uuid = :uuid AND games.game = ladders.ladder);
-- # &
INSERT INTO kills(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO deaths(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO parkour(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO equippedCosmetics(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO ownedHats(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO ownedBackpacks(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO ownedBelts(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO ownedCapes(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO ownedTags(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO ownedTrails(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO ownedKillPhrases(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO ownedChatColors(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # &
INSERT INTO ownedPotColors(uuid) VALUES(:uuid) ON CONFLICT DO NOTHING;
-- # }

-- # { delete
-- #    :uuid string
DELETE
FROM players
WHERE uuid=:uuid;
-- # }

-- # { save
-- #    :uuid string
-- #    :username string
-- #    :op int
-- #    :joined string
-- #    :joinedPos int
-- #    :rGames int
-- #    :rank string
-- #    :tempRank ?string
-- #    :expires ?string
-- #    :eGames int
-- #    :coins int
-- #    :muted ?string
UPDATE players
SET username=:username,
    op=:op,
    joined=:joined,
    joinedPos=:joinedPos,
    rGames=:rGames,
    rank=:rank,
    tempRank=:tempRank,
    expires=:expires,
    eGames=:eGames,
    coins=:coins,
    muted=:muted
WHERE uuid = :uuid;
-- # }

-- # { get_aliases
-- #   :uuid string
SELECT uuid FROM players
WHERE uuid <> :uuid
  AND (
    Addresses IN (SELECT Addresses FROM Players WHERE uuid = :uuid) OR
    ClientRandomIds IN (SELECT ClientRandomIds FROM Players WHERE uuid = :uuid) OR
    DeviceIds IN (SELECT DeviceIds FROM Players WHERE uuid = :uuid) OR
    SelfSignedIds IN (SELECT SelfSignedIds FROM Players WHERE uuid = :uuid) OR
    Xuids IN (SELECT Xuids FROM Players WHERE uuid = :uuid)
    );
-- # }
-- # { get_data
-- #   :uuid string
SELECT Addresses, ClientRandomIds, DeviceIds, SelfSignedIds, Xuids FROM Players WHERE uuid = :uuid;
-- # }
-- # { set_data
-- #   :uuid string
-- #   :addresses string
-- #   :clientRandomIds string
-- #   :deviceIds string
-- #   :selfSignedIds string
-- #   :xuids string
UPDATE Players
SET
    Addresses = :addresses,
    ClientRandomIds = :clientRandomIds,
    DeviceIds = :deviceIds,
    SelfSignedIds = :selfSignedIds,
    Xuids = :xuids
WHERE uuid = :uuid;
-- # }

-- # { unban
-- #   :uuid string
-- #   :includingAliases bool
-- TODO: Split the datum into tables
DELETE FROM banned
WHERE (:includingAliases AND uuid IN (
  SELECT uuid FROM players
  WHERE uuid <> :uuid
    AND (
      Addresses IN (SELECT Addresses FROM Players WHERE uuid = :uuid) OR
      ClientRandomIds IN (SELECT ClientRandomIds FROM Players WHERE uuid = :uuid) OR
      DeviceIds IN (SELECT DeviceIds FROM Players WHERE uuid = :uuid) OR
      SelfSignedIds IN (SELECT SelfSignedIds FROM Players WHERE uuid = :uuid) OR
      Xuids IN (SELECT Xuids FROM Players WHERE uuid = :uuid)
    )
  )
) OR uuid = :uuid;
-- # }

-- # { ban_if_banned_alias_exists
-- #   :uuid string
-- #   :duration string
-- #   :staff string
-- #   :reason ?string
-- TODO: Split the datum into tables
WITH calculate AS (SELECT uuid aUuid FROM players
                   WHERE uuid <> :uuid
                     AND (
                       Addresses IN (SELECT Addresses FROM Players WHERE uuid = :uuid) OR
                       ClientRandomIds IN (SELECT ClientRandomIds FROM Players WHERE uuid = :uuid) OR
                       DeviceIds IN (SELECT DeviceIds FROM Players WHERE uuid = :uuid) OR
                       SelfSignedIds IN (SELECT SelfSignedIds FROM Players WHERE uuid = :uuid) OR
                       Xuids IN (SELECT Xuids FROM Players WHERE uuid = :uuid)
                       ) LIMIT 1)
UPDATE banned
SET
    duration = :duration,
    staff = :staff,
    reason = CASE WHEN :reason IS NOT NULL THEN :reason || ' - ' || (SELECT aUuid FROM calculate) END
WHERE EXISTS(SELECT aUuid FROM calculate) AND uuid = :uuid;
-- # }

-- # { fetch_lowercase
-- #   :username string
SELECT * FROM players WHERE LOWER(username) = LOWER(:username);
-- # }

-- # { get_lowercase_usernames
SELECT LOWER(username) lowerUsername FROM players;
-- # }

-- # { get_data_to_load
-- #   :uuid string
SELECT rank, op, rGames, eGames, coins, tempRank, expires, muted FROM players WHERE uuid = :uuid;
-- # &
SELECT duration, staff, reason FROM banned WHERE uuid = :uuid;
-- # &
SELECT lifetime, monthly, weekly, daily FROM kills WHERE uuid = :uuid;
-- # &
SELECT lifetime, monthly, weekly, daily FROM deaths WHERE uuid = :uuid;
-- # &
SELECT lifetime, monthly, weekly, daily FROM parkour WHERE uuid = :uuid;
-- # &
SELECT setting, value FROM settings WHERE uuid = :uuid;
-- # &
SELECT ladder, elo FROM elos WHERE uuid = :uuid;
-- # &
SELECT game, played FROM games WHERE uuid = :uuid;
-- # &
SELECT name, contents FROM kits WHERE uuid = :uuid;
-- # &
SELECT hat, backpack, belt, cape, tag, trail, potColor, chatColor, killPhrase FROM equippedCosmetics WHERE uuid = :uuid;
-- # &
SELECT hat FROM ownedHats WHERE uuid = :uuid;
-- # &
SELECT backpack FROM ownedBackpacks WHERE uuid = :uuid;
-- # &
SELECT belt FROM ownedBelts WHERE uuid = :uuid;
-- # &
SELECT cape FROM ownedCapes WHERE uuid = :uuid;
-- # &
SELECT tag FROM ownedTags WHERE uuid = :uuid;
-- # &
SELECT trail FROM ownedTrails WHERE uuid = :uuid;
-- # &
SELECT killPhrase FROM ownedKillPhrases WHERE uuid = :uuid;
-- # &
SELECT color FROM ownedChatColors WHERE uuid = :uuid;
-- # &
SELECT color FROM ownedPotColors WHERE uuid = :uuid;
-- # }

-- # { set_op_by_name
-- #   :username string
-- #   :op bool
UPDATE players
SET op = CASE WHEN :op THEN 1 ELSE 0 END
WHERE username = :username;
-- # }

-- # { add_egames_by_name
-- #   :username string
-- #   :eGames int
UPDATE players
SET eGames = eGames + :eGames
WHERE username = :username;
-- # }

-- # { add_coins_by_lowercase_name
-- #   :username string
-- #   :coins int
UPDATE players
SET coins = coins + :coins
WHERE LOWER(username) = LOWER(:username);
-- # }

-- # { ban_by_lowercase_username
-- #   :username string
-- #   :duration string
-- #   :staff string
-- #   :reason ?string
-- #   :staffUuid ?string
SELECT (CASE
    WHEN EXISTS(SELECT * FROM banned WHERE uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username))) THEN 1
    WHEN :staffUuid IS NOT NULL AND (SELECT priority FROM ranks WHERE rank = (SELECT rank from players WHERE LOWER(username) = LOWER(:username))) >=
                                    (SELECT priority FROM ranks WHERE rank = (SELECT rank from players WHERE uuid = :staffUuid)) THEN 2
    ELSE 0 END) ret;
-- # &
UPDATE banned
SET
    duration = :duration,
    staff = :staff,
    reason = :reason
WHERE (SELECT (CASE
    WHEN EXISTS(SELECT * FROM banned WHERE uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username))) THEN 1
    WHEN :staffUuid IS NOT NULL AND (SELECT priority FROM ranks WHERE rank = (SELECT rank from players WHERE LOWER(username) = LOWER(:username))) >=
                                    (SELECT priority FROM ranks WHERE rank = (SELECT rank from players WHERE uuid = :staffUuid)) THEN 2
    ELSE 0 END)) = 0 AND uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username));
-- # }

-- # { set_rank_by_lowercase_username
-- #   :username string
-- #   :rank string
UPDATE players
SET rank = :rank
WHERE LOWER(username) = LOWER(:username);
-- # }

-- # { unban_by_lowercase_username
-- #   :username string
DELETE FROM banned
WHERE uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username));
-- # }

-- # { decrease_ranked_game
-- #   :uuid string
UPDATE players
SET
    rGames = CASE WHEN rGames > 0 THEN rGames - 1 ELSE 0 END,
    eGames = CASE WHEN rGames <= 0 AND eGames > 0 THEN eGames - 1 ELSE 0 END
WHERE uuid = :uuid;
-- # }

-- # { migrate_data
INSERT INTO banned(uuid, duration, staff, reason)
SELECT uuid, banned->>'$.duration', banned->>'$.staff', banned->>'$.reason'
FROM players
WHERE banned IS NOT NULL AND banned != '[]';
-- # &
UPDATE players SET banned = NULL WHERE TRUE;
-- # }

-- # { temp_rank_expired
-- #   :uuid string
UPDATE players
SET tempRank = NULL, expires = NULL
WHERE uuid = :uuid;
-- # }

-- # }

-- # { kills

-- # { init
CREATE TABLE IF NOT EXISTS kills(
    uuid VARCHAR(36) PRIMARY KEY,
    daily INT NOT NULL DEFAULT 0,
    weekly INT NOT NULL DEFAULT 0,
    monthly INT NOT NULL DEFAULT 0,
    lifetime INT NOT NULL DEFAULT 0,
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO kills(uuid) VALUES(:uuid);
-- # }

-- # { all

-- # { increment
-- #   :uuid string
UPDATE kills SET
    daily = daily + 1,
    weekly = weekly + 1,
    monthly = monthly + 1,
    lifetime = lifetime + 1
WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE kills
SET
    daily = 0,
    weekly = 0,
    monthly = 0,
    lifetime = 0
WHERE uuid = :uuid;
-- # }

-- # }

-- # { daily

-- # { get
-- #   :uuid string
SELECT daily FROM kills WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE kills SET daily = 0 WHERE uuid = :uuid;
-- # }

-- # }

-- # { weekly

-- # { get
-- #   :uuid string
SELECT weekly FROM kills WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE kills SET weekly = 0 WHERE uuid = :uuid;
-- # }

-- # }

-- # { monthly

-- # { get
-- #   :uuid string
SELECT monthly FROM kills WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE kills SET monthly = 0 WHERE uuid = :uuid;
-- # }

-- # }

-- # { lifetime

-- # { get
-- #   :uuid string
SELECT lifetime FROM kills WHERE uuid = :uuid;
-- # }

-- # }

-- # }

-- # { deaths

-- # { init
CREATE TABLE IF NOT EXISTS deaths(
    uuid VARCHAR(36) PRIMARY KEY,
    daily INT NOT NULL DEFAULT 0,
    weekly INT NOT NULL DEFAULT 0,
    monthly INT NOT NULL DEFAULT 0,
    lifetime INT NOT NULL DEFAULT 0,
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
    );
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO deaths(uuid) VALUES(:uuid);
-- # }

-- # { all

-- # { increment
-- #   :uuid string
UPDATE deaths SET
    daily = daily + 1,
    weekly = weekly + 1,
    monthly = monthly + 1,
    lifetime = lifetime + 1
WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE deaths
SET
    daily = 0,
    weekly = 0,
    monthly = 0,
    lifetime = 0
WHERE uuid = :uuid;
-- # }

-- # }

-- # { daily

-- # { get
-- #   :uuid string
SELECT daily FROM deaths WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE deaths SET daily = 0 WHERE uuid = :uuid;
-- # }

-- # }

-- # { weekly

-- # { get
-- #   :uuid string
SELECT weekly FROM deaths WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE deaths SET weekly = 0 WHERE uuid = :uuid;
-- # }

-- # }

-- # { monthly

-- # { get
-- #   :uuid string
SELECT monthly FROM deaths WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE deaths SET monthly = 0 WHERE uuid = :uuid;
-- # }

-- # }

-- # { lifetime

-- # { get
-- #   :uuid string
SELECT lifetime FROM deaths WHERE uuid = :uuid;
-- # }

-- # }

-- # }

-- # { kits

-- # { init
CREATE TABLE IF NOT EXISTS kits(
    uuid VARCHAR(36),
    name TEXT,
    contents TEXT NOT NULL,
    PRIMARY KEY(uuid, name),
    FOREIGN KEY(uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { save
-- #   :uuid string
-- #   :name string
-- #   :contents string
INSERT OR REPLACE INTO kits(uuid, name, contents) VALUES(:uuid, :name, :contents);
-- # }

-- # { get
-- #   :uuid string
-- #   :name string
SELECT contents FROM kits WHERE uuid = :uuid AND name = :name;
-- # }

-- # }

-- # { settings

-- # { init
CREATE TABLE IF NOT EXISTS settings(
    uuid VARCHAR(36),
    setting INT,
    value INT,
    PRIMARY KEY(uuid, setting),
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { save
-- #   :uuid string
-- #   :setting int
-- #   :value int
INSERT OR REPLACE INTO settings(uuid, setting, value) VALUES(:uuid, :setting, :value);
-- # }

-- # { get
-- # :uuid string
-- # :setting int
SELECT value FROM settings WHERE uuid = :uuid AND setting = :setting;
-- # }

-- # }

-- # { elos

-- # { init
CREATE TABLE IF NOT EXISTS elos(
    uuid VARCHAR(36),
    ladder TEXT,
    elo INT NOT NULL DEFAULT 1000,
    PRIMARY KEY (uuid, ladder),
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE,
    FOREIGN KEY (ladder) REFERENCES ladders(ladder) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
-- #   :ladder string
INSERT OR IGNORE INTO elos(uuid, ladder) VALUES(:uuid, :ladder);
-- # }

-- # { get
-- #   :uuid string
-- #   :ladder string
SELECT elo FROM elos WHERE uuid = :uuid AND ladder = :ladder;
-- # }

-- # { set
-- #   :uuid string
-- #   :ladder string
-- #   :elo int
UPDATE elos
SET elo = :elo
WHERE uuid = :uuid AND ladder = :ladder;
-- # }

-- # { average
-- #   :uuid string
SELECT AVG(elo) avg FROM elos WHERE uuid = :uuid;
-- # }

-- # { all
-- #   :uuid string
SELECT ladder, elo FROM elos WHERE uuid = :uuid;
-- # }

-- # }

-- # { games

-- # { init
CREATE TABLE IF NOT EXISTS games(
    uuid VARCHAR(36),
    game TEXT,
    played INT NOT NULL DEFAULT 0,
    PRIMARY KEY (uuid, game),
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE,
    FOREIGN KEY (game) REFERENCES ladders(ladder) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
-- #   :game string
INSERT OR IGNORE INTO games(uuid, game) VALUES(:uuid, :game);
-- # }

-- # { get
-- #   :uuid string
-- #   :game string
SELECT played FROM games WHERE uuid = :uuid AND game = :game;
-- # }

-- # }

-- # { cosmetics

-- # { equipped

-- # { init
CREATE TABLE IF NOT EXISTS equippedCosmetics(
    uuid VARCHAR(36) PRIMARY KEY,
    hat TEXT DEFAULT NULL,
    backpack TEXT DEFAULT NULL,
    belt TEXT DEFAULT NULL,
    cape TEXT DEFAULT NULL,
    tag TEXT DEFAULT NULL,
    trail TEXT DEFAULT NULL,
    potColor TEXT DEFAULT NULL,
    chatColor TEXT DEFAULT NULL,
    killPhrase TEXT DEFAULT NULL,
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO equippedCosmetics(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT hat, backpack, belt, cape, tag, trail, potColor, chatColor, killPhrase FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { hat

-- # { get
-- #  :uuid string
SELECT hat FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { set
-- #   :uuid string
-- #   :hat ?string
UPDATE equippedCosmetics SET hat = :hat WHERE uuid = :uuid;
-- # }

-- # }

-- # { backpack

-- # { get
-- #  :uuid string
SELECT backpack FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { set
-- #   :uuid string
-- #   :backpack ?string
UPDATE equippedCosmetics SET backpack = :backpack WHERE uuid = :uuid;
-- # }

-- # }

-- # { belt

-- # { get
-- #  :uuid string
SELECT belt FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { set
-- #   :uuid string
-- #   :belt ?string
UPDATE equippedCosmetics SET belt = :belt WHERE uuid = :uuid;
-- # }

-- # }

-- # { cape

-- # { get
-- #  :uuid string
SELECT cape FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { set
-- #   :uuid string
-- #   :cape ?string
UPDATE equippedCosmetics SET cape = :cape WHERE uuid = :uuid;
-- # }

-- # }

-- # { potColor

-- # { get
-- #  :uuid string
SELECT potColor FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { set
-- #   :uuid string
-- #   :potColor ?string
UPDATE equippedCosmetics SET potColor = :potColor WHERE uuid = :uuid;
-- # }

-- # }

-- # { trail

-- # { get
-- #  :uuid string
SELECT trail FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { set
-- #   :uuid string
-- #   :trail ?string
UPDATE equippedCosmetics SET trail = :trail WHERE uuid = :uuid;
-- # }

-- # }

-- # { tag

-- # { get
-- #  :uuid string
SELECT tag FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { set
-- #   :uuid string
-- #   :tag ?string
UPDATE equippedCosmetics SET tag = :tag WHERE uuid = :uuid;
-- # }

-- # }

-- # { killPhrase

-- # { get
-- #  :uuid string
SELECT killPhrase FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { set
-- #   :uuid string
-- #   :killPhrase ?string
UPDATE equippedCosmetics SET killPhrase = :killPhrase WHERE uuid = :uuid;
-- # }

-- # }

-- # { chatColor

-- # { get
-- #  :uuid string
SELECT chatColor FROM equippedCosmetics WHERE uuid = :uuid;
-- # }

-- # { set
-- #   :uuid string
-- #   :chatColor ?string
UPDATE equippedCosmetics SET chatColor = :chatColor WHERE uuid = :uuid;
-- # }

-- # }

-- # }

-- # { owned

-- # { capes

-- # { init
CREATE TABLE IF NOT EXISTS ownedCapes(
    uuid VARCHAR(36),
    cape TEXT DEFAULT 0,
    PRIMARY KEY (uuid, cape),
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO ownedCapes(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT cape FROM ownedCapes WHERE uuid = :uuid;
-- # }

-- # { save
-- #   :uuid string
-- #   :cape string
INSERT OR IGNORE INTO ownedCapes(uuid, cape) VALUES (:uuid, :cape);
-- # }

-- # }

-- # { hats

-- # { init
CREATE TABLE IF NOT EXISTS ownedHats(
    uuid VARCHAR(36),
    hat TEXT DEFAULT '',
    PRIMARY KEY (uuid, hat),
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO ownedHats(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT hat FROM ownedHats WHERE uuid = :uuid;
-- # }

-- # { save
-- #   :uuid string
-- #   :hat string
INSERT OR IGNORE INTO ownedHats(uuid, hat) VALUES (:uuid, :hat);
-- # }

-- # }

-- # { backpacks

-- # { init
CREATE TABLE IF NOT EXISTS ownedBackpacks(
                                        uuid VARCHAR(36),
                                        backpack TEXT DEFAULT '',
                                        PRIMARY KEY (uuid, backpack),
                                        FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO ownedBackpacks(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT backpack FROM ownedBackpacks WHERE uuid = :uuid;
-- # }

-- # { save
-- #   :uuid string
-- #   :backpack string
INSERT OR IGNORE INTO ownedBackpacks(uuid, backpack) VALUES (:uuid, :backpack);
-- # }

-- # }

-- # { belts

-- # { init
CREATE TABLE IF NOT EXISTS ownedBelts(
                                        uuid VARCHAR(36),
                                        belt TEXT DEFAULT '',
                                        PRIMARY KEY (uuid, belt),
                                        FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO ownedBelts(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT belt FROM ownedBelts WHERE uuid = :uuid;
-- # }

-- # { save
-- #   :uuid string
-- #   :belt string
INSERT OR IGNORE INTO ownedBelts(uuid, belt) VALUES (:uuid, :belt);
-- # }

-- # }

-- # { trails

-- # { init
CREATE TABLE IF NOT EXISTS ownedTrails(
    uuid VARCHAR(36),
    trail TEXT DEFAULT '',
    PRIMARY KEY (uuid, trail),
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO ownedTrails(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT trail FROM ownedTrails WHERE uuid = :uuid;
-- # }

-- # { save
-- #   :uuid string
-- #   :trail string
INSERT OR IGNORE INTO ownedTrails(uuid, trail) VALUES (:uuid, :trail);
-- # }

-- # }

-- # { tags

-- # { init
CREATE TABLE IF NOT EXISTS ownedTags(
   uuid VARCHAR(36),
   tag TEXT DEFAULT '',
   PRIMARY KEY (uuid, tag),
   FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO ownedTags(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT tag FROM ownedTags WHERE uuid = :uuid;
-- # }

-- # { save
-- #   :uuid string
-- #   :tag string
INSERT OR IGNORE INTO ownedTags(uuid, tag) VALUES (:uuid, :tag);
-- # }

-- # }

-- # { killPhrases

-- # { init
CREATE TABLE IF NOT EXISTS ownedKillPhrases(
   uuid VARCHAR(36),
   killPhrase TEXT DEFAULT '',
   PRIMARY KEY (uuid, killPhrase),
   FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO ownedKillPhrases(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT killPhrase FROM ownedKillPhrases WHERE uuid = :uuid;
-- # }

-- # { save
-- #   :uuid string
-- #   :killPhrase string
INSERT OR IGNORE INTO ownedKillPhrases(uuid, killPhrase) VALUES (:uuid, :killPhrase);
-- # }

-- # }

-- # { chatColors

-- # { init
CREATE TABLE IF NOT EXISTS ownedChatColors(
                                               uuid VARCHAR(36),
                                               color TEXT DEFAULT '',
                                               PRIMARY KEY (uuid, color),
                                               FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO ownedChatColors(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT color FROM ownedChatColors WHERE uuid = :uuid;
-- # }

-- # { save
-- #   :uuid string
-- #   :color string
INSERT OR IGNORE INTO ownedChatColors(uuid, color) VALUES (:uuid, :color);
-- # }

-- # }

-- # { potColors

-- # { init
CREATE TABLE IF NOT EXISTS ownedPotColors(
                                               uuid VARCHAR(36),
                                               color TEXT DEFAULT '',
                                               PRIMARY KEY (uuid, color),
                                               FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO ownedPotColors(uuid) VALUES(:uuid);
-- # }

-- # { all
-- #   :uuid string
SELECT color FROM ownedPotColors WHERE uuid = :uuid;
-- # }

-- # { save
-- #   :uuid string
-- #   :color string
INSERT OR IGNORE INTO ownedPotColors(uuid, color) VALUES (:uuid, :color);
-- # }

-- # }

-- # }

-- # }

-- # { packs

-- # { init
CREATE TABLE IF NOT EXISTS packs(
    uuid VARCHAR(36) NOT NULL,
    pack TEXT NOT NULL,
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { add
-- #   :uuid string
-- #   :pack string
INSERT INTO packs(uuid, pack) VALUES(:uuid, :pack);
-- # }

-- # { remove
-- #   :uuid string
-- #   :pack string
DELETE FROM packs WHERE ROWID IN (SELECT ROWID FROM packs WHERE uuid = :uuid AND pack = :pack LIMIT 1);
-- # }

-- # { all
-- #   :uuid string
-- #   :offset int
-- #   :limit int
SELECT pack FROM packs WHERE uuid = :uuid LIMIT :limit OFFSET :offset;
-- # }

-- # }

-- # { parkour

-- # { init
CREATE TABLE IF NOT EXISTS parkour(
    uuid VARCHAR(36) PRIMARY KEY,
    daily FLOAT DEFAULT NULL,
    weekly FLOAT DEFAULT NULL,
    monthly FLOAT DEFAULT NULL,
    lifetime FLOAT DEFAULT NULL,
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
);
-- # }

-- # { create
-- #   :uuid string
INSERT OR IGNORE INTO parkour(uuid) VALUES(:uuid);
-- # }

-- # { all

-- # { newBest
-- #   :uuid string
-- #   :best float
UPDATE parkour SET daily = :best WHERE uuid = :uuid AND (daily ISNULL OR daily > :best);
-- # &
UPDATE parkour SET weekly = :best WHERE uuid = :uuid AND (weekly ISNULL OR weekly > :best);
-- # &
UPDATE parkour SET monthly = :best WHERE uuid = :uuid AND (monthly ISNULL OR monthly > :best);
-- # &
UPDATE parkour SET lifetime = :best WHERE uuid = :uuid AND (lifetime ISNULL OR lifetime > :best);
-- # &
SELECT lifetime best FROM parkour WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE parkour
SET daily = NULL,
    weekly = NULL,
    monthly = NULL,
    lifetime = NULL
WHERE uuid = :uuid;
-- # }

-- # }

-- # { daily

-- # { get
-- #   :uuid string
SELECT daily FROM parkour WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE parkour SET daily = NULL WHERE uuid = :uuid;
-- # }

-- # }

-- # { weekly

-- # { get
-- #   :uuid string
SELECt weekly FROM parkour WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE parkour SET weekly = NULL WHERE uuid = :uuid;
-- # }

-- # }

-- # { monthly

-- # { get
-- #  :uuid string
SELECT monthly FROM parkour WHERE uuid = :uuid;
-- # }

-- # { reset
-- #   :uuid string
UPDATE parkour SET monthly = NULL WHERE uuid = :uuid;
-- # }

-- # }

-- # { lifetime

-- # { get
-- #   :uuid string
SELECT lifetime FROM parkour WHERE uuid = :uuid;
-- # }

-- # }

-- # }

-- # { ladders

-- # { init
CREATE TABLE IF NOT EXISTS ladders(
    ladder TEXT PRIMARY KEY
);
-- # }

-- # }

-- # { stats

-- # { get
-- #   :uuid string
SELECT username FROM players WHERE uuid = :uuid;
-- # &
SELECT lifetime, monthly, weekly, daily FROM kills WHERE uuid = :uuid;
-- # &
SELECT lifetime, monthly, weekly, daily FROM deaths WHERE uuid = :uuid;
-- # &
SELECT lifetime, monthly, weekly, daily FROM parkour WHERE uuid = :uuid;
-- # }

-- # { get_by_lowercase_name
-- #   :username string
SELECT username FROM players WHERE LOWER(username) = LOWER(:username);
-- # &
SELECT lifetime, monthly, weekly, daily FROM kills WHERE uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username));
-- # &
SELECT lifetime, monthly, weekly, daily FROM deaths WHERE uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username));
-- # &
SELECT lifetime, monthly, weekly, daily FROM parkour WHERE uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username));
-- # }

-- # { get_top_ten_elo_by_ladder
-- #   :ladder string
SELECT (SELECT username FROM players WHERE uuid = elos.uuid) name, elo
FROM elos
WHERE ladder = :ladder
ORDER BY elo DESC
LIMIT 10;
-- # }

-- # { get_top_ten_average_elos
SELECT (SELECT username FROM players WHERE uuid = avgElos.uuid) name, avg
FROM (SELECT AVG(elo) avg, uuid FROM elos) avgElos
ORDER BY avg DESC
LIMIT 10;
-- # }

-- # { get_top_ten_parkour_records_by_timeframe
-- #   :timeframe int
SELECT
    (SELECT username FROM players WHERE uuid = parkour.uuid) name,
    CASE :timeframe
        WHEN 0 THEN lifetime
        WHEN 1 THEN monthly
        WHEN 2 THEN weekly
        ELSE daily END record
FROM parkour
WHERE record IS NOT NULL
ORDER BY record
LIMIT 10;
-- # }

-- # { get_top_ten_kills_by_timeframe
-- #   :timeframe int
SELECT
    (SELECT username FROM players WHERE uuid = kills.uuid) name,
    CASE :timeframe
        WHEN 0 THEN lifetime
        WHEN 1 THEN monthly
        WHEN 2 THEN weekly
        ELSE daily END amount
FROM kills
ORDER BY amount DESC
LIMIT 10;
-- # }

-- # { get_top_ten_deaths_by_timeframe
-- #   :timeframe int
SELECT
    (SELECT username FROM players WHERE uuid = deaths.uuid) name,
    CASE :timeframe
        WHEN 0 THEN lifetime
        WHEN 1 THEN monthly
        WHEN 2 THEN weekly
        ELSE daily END amount
FROM deaths
WHERE amount IS NOT NULL
ORDER BY amount DESC
LIMIT 10;
-- # }

-- # { get_top_ten_kdrs_by_timeframe
-- #   :timeframe int
SELECT (SELECT username FROM players WHERE uuid = kills.uuid) name,
       ROUND(CAST((CASE :timeframe
           WHEN 0 THEN kills.lifetime
           WHEN 1 THEN kills.monthly
           WHEN 2 THEN kills.weekly
           ELSE kills.daily END) AS DOUBLE) /
             MAX(1.0, CAST((CASE :timeframe
                 WHEN 0 THEN deaths.lifetime
                 WHEN 1 THEN deaths.monthly
                 WHEN 2 THEN deaths.weekly
                 ELSE deaths.daily END) AS DOUBLE)
             ),
             2
       ) amount
FROM deaths INNER JOIN kills USING(uuid)
ORDER BY amount DESC
LIMIT 10;
-- # }

-- # { reset_and_get_daily
-- #   :time string
UPDATE resets
SET daily = :time
WHERE id = 0;
-- # &
UPDATE kills SET daily = 0 WHERE TRUE;
-- # &
UPDATE deaths SET daily = 0 WHERE TRUE;
-- # &
UPDATE parkour SET daily = 0 WHERE TRUE;
-- # &
UPDATE players SET rGames = CASE
    WHEN (SELECT priority FROM ranks WHERE rank = players.rank) >= (SELECT priority FROM ranks WHERE rank = 'elite') THEN 80
    WHEN (SELECT priority FROM ranks WHERE rank = players.rank) >= (SELECT priority FROM ranks WHERE rank = 'ultra') THEN 60
    ELSE 20 END
WHERE TRUE;
-- # &
SELECT (SELECT username FROM players WHERE uuid = kills.uuid) name, daily amount
FROM kills
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = deaths.uuid) name, daily amount
FROM deaths
ORDER BY amount
LIMIT 10;
-- # &
SELECT
    (SELECT username FROM players WHERE uuid = kills.uuid) name,
    ROUND(CAST(kills.daily AS DOUBLE) / MAX(1.0, CAST(deaths.daily AS DOUBLE)), 2) amount
FROM deaths INNER JOIN kills USING(uuid)
ORDER BY amount DESC
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = parkour.uuid) name, daily record
FROM parkour
WHERE record IS NOT NULL
ORDER BY record
LIMIT 10;
-- # }

-- # { reset_and_get_weekly
-- #   :time string
UPDATE resets
SET weekly =:time
WHERE id = 0;
-- # &
UPDATE kills SET weekly = 0 WHERE TRUE;
-- # &
UPDATE deaths SET weekly = 0 WHERE TRUE;
-- # &
UPDATE parkour SET weekly = 0 WHERE TRUE;
-- # &
SELECT (SELECT username FROM players WHERE uuid = kills.uuid) name, weekly amount
FROM kills
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = deaths.uuid) name, weekly amount
FROM deaths
ORDER BY amount
LIMIT 10;
-- # &
SELECT
    (SELECT username FROM players WHERE uuid = kills.uuid) name,
    ROUND(CAST(kills.weekly AS DOUBLE) / MAX(1.0, CAST(deaths.weekly AS DOUBLE)), 2) amount
FROM deaths INNER JOIN kills USING(uuid)
ORDER BY amount DESC
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = parkour.uuid) name, weekly record
FROM parkour
WHERE record IS NOT NULL
ORDER BY record
LIMIT 10;
-- # }

-- # { reset_and_get_monthly
-- #   :time string
UPDATE resets
SET monthly =:time
WHERE id = 0;
-- # &
UPDATE kills SET monthly = 0 WHERE TRUE;
-- # &
UPDATE deaths SET monthly = 0 WHERE TRUE;
-- # &
UPDATE parkour SET monthly = 0 WHERE TRUE;
-- # &
SELECT (SELECT username FROM players WHERE uuid = kills.uuid) name, monthly amount
FROM kills
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = deaths.uuid) name, monthly amount
FROM deaths
ORDER BY amount
LIMIT 10;
-- # &
SELECT
    (SELECT username FROM players WHERE uuid = kills.uuid) name,
    ROUND(CAST(kills.monthly AS DOUBLE) / MAX(1.0, CAST(deaths.monthly AS DOUBLE)), 2) amount
FROM deaths INNER JOIN kills USING(uuid)
ORDER BY amount DESC
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = parkour.uuid) name, monthly record
FROM parkour
WHERE record IS NOT NULL
ORDER BY record
LIMIT 10;
-- # }

-- # { reset_and_get_by_lowercase_username
-- #   :username string
UPDATE kills
SET daily = 0, weekly = 0, monthly = 0, lifetime = 0
WHERE uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username));
-- # &
UPDATE deaths
SET daily = 0, weekly = 0, monthly = 0, lifetime = 0
WHERE uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username));
-- # &
UPDATE parkour
SET daily = 0, weekly = 0, monthly = 0, lifetime = 0
WHERE uuid = (SELECT uuid FROM players WHERE LOWER(username) = LOWER(:username));
-- # &
SELECT (SELECT username FROM players WHERE uuid = kills.uuid) name, daily amount
FROM kills
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = kills.uuid) name, weekly amount
FROM kills
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = kills.uuid) name, monthly amount
FROM kills
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = kills.uuid) name, lifetime amount
FROM kills
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = deaths.uuid) name, daily amount
FROM deaths
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = deaths.uuid) name, weekly amount
FROM deaths
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = deaths.uuid) name, monthly amount
FROM deaths
ORDER BY amount
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = deaths.uuid) name, lifetime amount
FROM deaths
ORDER BY amount
LIMIT 10;
-- # &
SELECT
    (SELECT username FROM players WHERE uuid = kills.uuid) name,
    ROUND(CAST(kills.daily AS DOUBLE) / MAX(1.0, CAST(deaths.daily AS DOUBLE)), 2) amount
FROM deaths INNER JOIN kills USING(uuid)
ORDER BY amount DESC
LIMIT 10;
-- # &
SELECT
    (SELECT username FROM players WHERE uuid = kills.uuid) name,
    ROUND(CAST(kills.weekly AS DOUBLE) / MAX(1.0, CAST(deaths.weekly AS DOUBLE)), 2) amount
FROM deaths INNER JOIN kills USING(uuid)
ORDER BY amount DESC
LIMIT 10;
-- # &
SELECT
    (SELECT username FROM players WHERE uuid = kills.uuid) name,
    ROUND(CAST(kills.monthly AS DOUBLE) / MAX(1.0, CAST(deaths.monthly AS DOUBLE)), 2) amount
FROM deaths INNER JOIN kills USING(uuid)
ORDER BY amount DESC
LIMIT 10;
-- # &
SELECT
    (SELECT username FROM players WHERE uuid = kills.uuid) name,
    ROUND(CAST(kills.lifetime AS DOUBLE) / MAX(1.0, CAST(deaths.lifetime AS DOUBLE)), 2) amount
FROM deaths INNER JOIN kills USING(uuid)
ORDER BY amount DESC
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = parkour.uuid) name, daily record
FROM parkour
WHERE record IS NOT NULL
ORDER BY record
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = parkour.uuid) name, weekly record
FROM parkour
WHERE record IS NOT NULL
ORDER BY record
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = parkour.uuid) name, monthly record
FROM parkour
WHERE record IS NOT NULL
ORDER BY record
LIMIT 10;
-- # &
SELECT (SELECT username FROM players WHERE uuid = parkour.uuid) name, lifetime record
FROM parkour
WHERE record IS NOT NULL
ORDER BY record
LIMIT 10;
-- # }

-- # }

-- # { ranks

-- # { init
CREATE TABLE IF NOT EXISTS ranks(
    rank TEXT PRIMARY KEY,
    priority INT NOT NULL
);

-- # }

-- # }

-- # { banned

-- # { init
CREATE TABLE IF NOT EXISTS banned(
    uuid TEXT PRIMARY KEY,
    duration TEXT NOT NULL,
    staff TEXT NOT NULL,
    reason TEXT,
    FOREIGN KEY (uuid) REFERENCES players (uuid) ON DELETE CASCADE
);

-- # }

-- # }

-- # }