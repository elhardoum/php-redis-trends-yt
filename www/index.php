<?php

require __DIR__ . '/vendor/autoload.php';

use Predis\Client;

class TrendsApp
{
    const DEFAULT_EXPIRE = 60 *60 *24; # daily

    static function getRedis()
    {
        return new Client('tcp://cache:6379');
    }

    static function incrementTopic(string $set, string $member, int $by=1) : void
    {
        $setid = "trends_{$set}" . date('mdH');

        $redis = self::getRedis();

        $redis->zincrby($setid, (int) $by, $member);

        if ( $redis->ttl($setid) <= 0 ) {
            $redis->expire($setid, self::DEFAULT_EXPIRE);
        }
    }

    static function getTopics(string $set, int $limit=10) : array
    {
        $redis = self::getRedis();

        if ( $sets = $redis->keys( "trends_{$set}*" ) ) {
            $items = [];

            foreach ( $sets as $setName ) {
                if ( $list = array_map('intval', $redis->zrevrangebyscore( $setName, '+inf', '-inf', 'withscores', 'limit', 0, $limit )) ) {
                    foreach ( $list as $k=>$v ) {
                        if ( ! isset( $items[ $k ] ) ) {
                            $items[ $k ] = 0;
                        }

                        $items[ $k ] += $v;
                    }
                }
            }

            arsort($items);

            return array_slice($items, 0, $limit);
        }

        return [];
    }
}

var_dump( TrendsApp::incrementTopic('pets', 'dog', 1) );
var_dump( TrendsApp::incrementTopic('pets', 'goat', 2) );
var_dump( TrendsApp::incrementTopic('pets', 'cat', 5) ); # cats rule
var_dump( TrendsApp::incrementTopic('pets', 'bird', 3) );

var_dump( TrendsApp::getTopics('pets', 10) );