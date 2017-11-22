<?php

// TODO: Abstract all functionality into a separated concerns.

namespace DatesProgram;

require_once 'FakeLoadingScreen.php';
require_once 'functions.php';

use PGunsolley\Tools\CLI\FakeLoadingScreen;
use DateTime;
use DateTimeZone;
use DateInterval;
use Exception;
use TypeError;
use Throwable;

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

    protected static function askRunAgain(string $prompt, string $yes = 'y', string $no = 'n'): bool 
    {
        while (true) {
            self::p($prompt);
            $option = strtolower(self::rl());
            if ($option === $yes) {
                return true;
            } elseif ($option === $no) {
                return false;
            }
        }
    }

    protected static function validateDateFormat(string $dateString): bool 
    {
        if (1 === preg_match('/^(\d{2})\/(\d{2})\/(\d{4})/', $dateString)) {
            return true;
        }
        return false;
    }

    protected static function getDateTimeZoneObject(string $tzString): DateTimeZone 
    {
        if ($tzString === '') {
            $tzString = 'America/Chicago';
        }
        try {
            $tzObj = new DateTimeZone($tzString);
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
            $eventName = self::q("What was the event? ");
            self::p("For more accurate results, please enter the timezone of the event (leave blank to pass): ");
            $eventTzString = self::rl();
            if ($eventTzString === '') {
                self::p("Defaulting to America/Chicago.");
            }
            self::p("For more accurate results, please enter your current timezone: (leave blank to pass): ");
            $currentTzString = self::rl();
            if ($currentTzString === '') {
                self::p("Defaulting to America/Chicago.");                
            }
        
            $currentDateObj = self::getDateObject();
            $eventTzObj = self::getDateTimeZoneObject($eventTzString);
            $eventDateObj = self::getDateObject($eventDateString, $eventTzObj);
            
            $interval = self::getDateInterval($currentDateObj, $eventDateObj);

            // Display fake loading prompt... lol
            FakeLoadingScreen::displayByLine([
                [
                    'message' => 'Loading',
                    'trailingCharCount' => 12,
                    'trailingChar' => '.',
                    'onRender' => 'onRenderHandler',
                    'onComplete' => 'onCompleteHandler'
                ],
                [
                    'message' => 'Reticulating Splines',
                    'trailingCharCount' => 23,
                    'trailingChar' => '.',
                    'onRender' => 'onRenderHandler',
                    'onComplete' => 'onCompleteHandler'
                ],
                [
                    'message' => 'Generating Time Paradox',
                    'trailingCharCount' => 30,
                    'trailingChar' => '.',
                    'onRender' => 'onRenderHandler',
                    'onComplete' => 'onCompleteHandler'
                ]
            ]);

            self::p($interval->format("{$eventName} happened %y years, %m months, %d days ago. Feel old, yet?"));
            if (!self::askRunAgain("Would you like to try a different date? (Y/n) ")) {
                self::$_running = false;
            }
            ErrorBag::log('logs/simple-log.txt');
        }
    }
}
Program::main();
