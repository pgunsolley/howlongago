<?php

namespace DatesProgram;

use DateTime;
use DateTimeZone;
use DateInterval;
use Exception;
use TypeError;
use Throwable;
use Iterator;

abstract class ErrorBag 
{
    protected static $_errors = [];

    public static function setMessage(string $message): void 
    {
        self::$_errors[] = $message;
    }

    public static function getMessages(): array 
    {
        return self::$_errors;
    }

    public static function log(string $path): void 
    {
        $file = fopen($path, 'w');
        foreach (self::$_errors as $error) {
            fwrite($file, sprintf(
                "%s: %s\r\n",
                date('Y-m-d H:i:s'),
                $error
            ));
        }
        fclose($file);
    }
}

abstract class Program 
{
    protected static $_running = true;

    protected static function p(string $text): void 
    {
        echo $text . PHP_EOL;
    }

    protected static function rl(): string 
    {
        echo "$ ";
        return stream_get_line(STDIN, 1024, PHP_EOL);
    }

    // Wrap p and rl.
    protected static function q(string $text): string 
    {
        self::p($text);
        return self::rl();
    }

    protected static function validateDateFormat(string $dateString): bool 
    {
        if (1 === preg_match('/^(\d{2})\/(\d{2})\/(\d{4})/', $dateString)) {
            return true;
        }
        return false;
    }

    //TODO: Fix this darn thing
    protected static function getDateTimeZoneObject(string $tzString): DateTimeZone 
    {
        try {
            if ($tzString === '') {
                $tzObj = new DateTimeZone($tzString);                
            }
        } catch (Throwable $e) {
            ErrorBag::setMessage($e->getMessage());
        }
        return $tzObj;
    }

    protected static function getDateObject(?string $date = null, ?DateTimeZone $tzObj = null): DateTime 
    {
        $dateObj = new DateTime();
        try {
            if (!$tzObj) {
                $tzObj = self::getDateTimeZoneObject();
            }
            $dateObj = new DateTime($date, $tzObj);
        } catch (TypeError $e) {
            ErrorBag::setMessage($e->getMessage());
        }
        return $dateObj;
    }

    protected static function getDateInterval(DateTime $firstDate, DateTime $secondDate): DateInterval 
    {
        try {
            $interval = $firstDate->diff($secondDate);
        } catch (Exception $e) {
            ErrorBag::setMessage($e->getMessage());
        }
        return $interval;
    }

    protected static function getIterator(array $data, int $delay = 100000): Iterator 
    {
        foreach ($data as $iterableItem) {
            usleep($delay);
            yield $iterableItem;
        }
    }

    // TODO: Make sure this works lol
    protected static function displayIterator(Iterator $i, callable $displayCallback, callable $completeCallback): void 
    {
        foreach ($i as $item) {
            $displayCallback($item);
        }
        $completeCallback();
    }

    public static function main(): void 
    {
        self::p("Ready to feel old? Enter the date of a significant event in your life, and I will attempt to return how long ago it happened!");        
        while (self::$_running) {
            self::p("Please enter the date: (mm/dd/yyyy) ");
            $eventDateString = self::rl();
            if (!self::validateDateFormat($eventDateString)) {
                self::p("Date format is invalid. ");
                continue;
            }
            self::p("For more accurate results, please enter the timezone of the event (leave blank to pass): ");
            $eventTzString = self::rl();
            self::p("For more accurate results, please enter your current timezone: (leave blank to pass): ");
            $currentTzString = self::rl();
        
            $currentDateObj = self::getDateObject();
            $eventTzObj = self::getDateTimeZoneObject($eventTzString);
            $eventDateObj = self::getDateObject($eventDateString, $eventTzObj);
            
            $interval = self::getDateInterval($currentDateObj, $eventDateObj);

            self::displayIterator(
                self::getIterator(array_merge(['Calculating'], array_fill(0, 20, '.'))), function($item) {
                    $stdout = fopen('php://output', 'w');
                    fwrite($stdout, $item, strlen($item));
                    fclose($stdout);
                }, function() {
                    echo PHP_EOL;
                }
            );
            self::p($interval->format("The event happened %y years, %m months, %d days ago. Feel old, yet?"));            
            self::p("Would you like to try a different date? (Y/n) ");
            self::$_running = strtolower(self::rl()) !== 'y' ? false : true;
            ErrorBag::log('logs/simple-log.txt');
        }
    }
}
Program::main();
