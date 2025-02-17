<?php

namespace xSuper\OqexPractice\utils;

use DateInterval;
use DateTime;
use Exception;
use pocketmine\utils\AssumptionFailedError;
use xSuper\OqexPractice\duel\utils\Leaderboard;
use xSuper\OqexPractice\duel\utils\LeaderboardIds;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\result\SqlSelectResult;
use xSuper\OqexPractice\libs\_5bc4ae2005dd3233\poggit\libasynql\SqlThread;
use xSuper\OqexPractice\OqexPractice;

class TimeUtils
{
	/** @var array{'daily': string, 'weekly': string, 'monthly': string}|array{} */
    private static array $lastResets = [];

    public static function attemptReRoll(): void
    {
        if (count(self::$lastResets) === 0) {
            OqexPractice::getDatabase()->executeSelect('oqex-practice.resets.daily.get', [], function (array $rows): void {
                $daily = $rows[0] ?? null;

                if ($daily === null) {
                    $daily = (TimeUtils::stringToTimestampAdd('1d', new DateTime()) ??
						throw new AssumptionFailedError('This should not return null'))[0]->format('Y-m-d H-i-s');
                    $weekly = (TimeUtils::stringToTimestampAdd('1w', new DateTime()) ??
						throw new AssumptionFailedError('This should not return null'))[0]->format('Y-m-d H-i-s');
                    $monthly = (TimeUtils::stringToTimestampAdd('1mo', new DateTime()) ??
						throw new AssumptionFailedError('This should not return null'))[0]->format('Y-m-d H-i-s');

                    $ar = [
                        'daily' => $daily,
                        'weekly' => $weekly,
                        'monthly' => $monthly
                    ];

                    OqexPractice::getDatabase()->executeInsert('oqex-practice.resets.create', $ar);

                    self::$lastResets = $ar;
                    return;
                }

                self::$lastResets['daily'] = $daily['daily'];

                OqexPractice::getDatabase()->executeSelect('oqex-practice.resets.weekly.get', [], function (array $rows): void {
                    $weekly = $rows[0] ?? null;

                    if ($weekly === null) throw new AssumptionFailedError('Wtf?');

                    self::$lastResets['weekly'] = $weekly['weekly'];

                });
                OqexPractice::getDatabase()->executeSelect('oqex-practice.resets.monthly.get', [], function (array $rows): void {
                    $monthly = $rows[0] ?? null;

                    if ($monthly === null) throw new AssumptionFailedError('Wtf?');

                    self::$lastResets['monthly'] = $monthly['monthly'];

                    self::attemptReRoll();
                });
            });

            return;
        }

        $from = date_create_from_format('Y-m-d H-i-s', self::$lastResets['daily']);
        $to = new DateTime();

        if ($from <= $to) self::daily();

        $from = date_create_from_format('Y-m-d H-i-s', self::$lastResets['weekly']);
        $to = new DateTime();

        if ($from <= $to) self::weekly();

        $from = date_create_from_format('Y-m-d H-i-s', self::$lastResets['monthly']);
        $to = new DateTime();

        if ($from <= $to) self::monthly();
    }

    public static function daily(): void
    {
		OqexPractice::getDatabase()->executeMulti('oqex-practice.stats.reset_and_get_daily', ['time' => (TimeUtils::stringToTimestampAdd('1d', new DateTime()) ??
			throw new AssumptionFailedError('Target should not be null at this point'))[0]->format('Y-m-d H-i-s')], SqlThread::MODE_SELECT, static function(array $results): void{
			$resultsRows = array_map(fn(SqlSelectResult $select) => $select->getRows(), $results);
			Leaderboard::updateKillsWithData(LeaderboardIds::DAILY, $resultsRows[5]);
			Leaderboard::updateDeathsWithData(LeaderboardIds::DAILY, $resultsRows[6]);
			Leaderboard::updateKDWithData(LeaderboardIds::DAILY, $resultsRows[7]);
			Leaderboard::updateParkourWithData(LeaderboardIds::DAILY, $resultsRows[8]);
		});
    }

    public static function weekly(): void
    {
		OqexPractice::getDatabase()->executeMulti('oqex-practice.stats.reset_and_get_weekly', ['time' => (TimeUtils::stringToTimestampAdd('1w', new DateTime()) ??
			throw new AssumptionFailedError('Target should not be null at this point'))[0]->format('Y-m-d H-i-s')], SqlThread::MODE_SELECT, static function(array $results): void{
			$resultsRows = array_map(fn(SqlSelectResult $select) => $select->getRows(), $results);
			Leaderboard::updateKillsWithData(LeaderboardIds::WEEKLY, $resultsRows[4]);
			Leaderboard::updateDeathsWithData(LeaderboardIds::WEEKLY, $resultsRows[5]);
			Leaderboard::updateKDWithData(LeaderboardIds::WEEKLY, $resultsRows[6]);
			Leaderboard::updateParkourWithData(LeaderboardIds::WEEKLY, $resultsRows[7]);
		});
    }

    public static function monthly(): void
    {
		OqexPractice::getDatabase()->executeMulti('oqex-practice.stats.reset_and_get_monthly', ['time' => (TimeUtils::stringToTimestampAdd('1mo', new DateTime()) ??
			throw new AssumptionFailedError('Target should not be null at this point'))[0]->format('Y-m-d H-i-s')], SqlThread::MODE_SELECT, static function(array $results): void{
			$resultsRows = array_map(fn(SqlSelectResult $select) => $select->getRows(), $results);
			Leaderboard::updateKillsWithData(LeaderboardIds::MONTHLY, $resultsRows[4]);
			Leaderboard::updateDeathsWithData(LeaderboardIds::MONTHLY, $resultsRows[5]);
			Leaderboard::updateKDWithData(LeaderboardIds::MONTHLY, $resultsRows[6]);
			Leaderboard::updateParkourWithData(LeaderboardIds::MONTHLY, $resultsRows[7]);
		});
    }


    /**
     * @throws Exception
	 * @return ?array{DateTime, string}
     */
    public static function stringToTimestampAdd(string $string, DateTime $time): ?array
    {
        /**
         * Rules:
         * Integers without suffix are considered as seconds
         * "s" is for seconds
         * "m" is for minutes
         * "h" is for hours
         * "d" is for days
         * "w" is for weeks
         * "mo" is for months
         * "y" is for years
         */
        if (trim($string) === "") {
            return null;
        }
        $t = $time;
        preg_match_all("/\d+(y|mo|w|d|h|m|s)|\d+/", $string, $found);
        if (count($found[0]) < 1) {
            return null;
        }
        $found[2] = preg_replace("/\D/", "", $found[0]);
        foreach ($found[2] as $k => $i) {
            switch ($c = $found[1][$k]) {
                case "y":
                case "w":
                case "d":
                    $t->add(new DateInterval("P" . $i . strtoupper($c)));
                    break;
                case "mo":
                    $t->add(new DateInterval("P" . $i . strtoupper(substr($c, 0, strlen($c) - 1))));
                    break;
                case "h":
                case "m":
                case "s":
                    $t->add(new DateInterval("PT" . $i . strtoupper($c)));
                    break;
                default:
                    $t->add(new DateInterval("PT" . $i . "S"));
                    break;
            }
            $string = str_replace($found[0][$k], "", $string);
        }
        return [$t, ltrim(str_replace($found[0], "", $string))];
    }

    public static function formatDate(DateTime $to, DateTime $from): string
    {
        $interval = $to->diff($from);

        $str = '';

        $parts = [
            $interval->y => 'year',
            $interval->m => 'month',
            $interval->d => 'day',
            $interval->h => 'hour',
            $interval->i => 'minute',
            $interval->s => 'second'
        ];

        $includedParts = 0;

        foreach ($parts as $currentPart => $text) {
            if ($currentPart === 0) {
                continue;
            }

            if ($str !== '') {
                $str .= ', ';
            }

            $str .= sprintf('%d %s', $currentPart, $text);

            if ($currentPart > 1) {
                $str .= 's';
            }

            $includedParts++;
        }

        return $str;
    }
}