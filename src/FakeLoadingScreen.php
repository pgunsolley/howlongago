<?php

namespace PGunsolley\Tools\CLI;

use Iterator;

abstract class FakeLoadingScreen
{
    /**
     * Get an iterator.
     * 
     * Pass an array of values you would like to wrap with an \Iterator.
     * 
     * @param array 
     * @param int
     * @return \Iterator
     */
    public static function getIterator(array $data, int $delay = 100000): \Iterator 
    {
        foreach ($data as $iterableItem) {
            usleep($delay);
            yield $iterableItem;
        }
    }

    /**
     * Display the iterator.
     * 
     * Pass an \Iterator, a callback for handling the current value, 
     * and a callback for handling total completion.
     * 
     * @param \Iterator
     * @param string
     * @param string
     * @return void
     */
    public static function displayIterator(Iterator $i, string $displayCallback, string $completeCallback): void 
    {
        foreach ($i as $item) {
            call_user_func($displayCallback, $item);
        }
        call_user_func($completeCallback);
    }

    /**
     * High level wrapper for displaying loading messages, per line.
     * 
     * Uses the methods in this class, and an $options array.
     * 
     * @param array
     * @return void
     */
    public static function displayByLine(array $options): void
    {
        foreach ($options as $item) {
            
            self::displayIterator(
                self::getIterator(array_merge([$item['message']], array_fill(0, $item['trailingCharCount'], $item['trailingChar']))), 
                $item['onRender'],
                $item['onComplete']
            );
        }
    }
}