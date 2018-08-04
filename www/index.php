<?php

require __DIR__ . '/vendor/autoload.php';

use Predis\Client;

class TrendsApp
{
    const SET_NAME = '_topics';
    const TRENDS_INTERVAL = 60 *60;
    const DATE_SUFFIX_FORMAT = 'Hi';
    const CONNECTION_ARGS = 'tcp://cache:6379';

    private static $connection;

    private static function getRedis()
    {
        if ( ! isset( self::$connection ) || ! self::$connection instanceOf \Predis\Client ) {
            self::$connection = new Client( self::CONNECTION_ARGS );
        }

        return self::$connection;
    }

    public static function incrementTopic( string $topic, int $by=1 )
    {
        $redis = self::getRedis();
        $setname = self::SET_NAME . '_' . date( self::DATE_SUFFIX_FORMAT );
        $redis->zincrby( $setname, $by, $topic );

        if ( $redis->ttl( $setname ) < 0 ) {
            $redis->expire( $setname, self::TRENDS_INTERVAL );
        }
    }

    public static function getTopics( int $limit ) : array
    {
        $redis = self::getRedis();
        $topics = [];

        if ( $sets = $redis->keys( self::SET_NAME . '_*' ) ) {
            foreach ( $sets as $set ) {
                $items = $redis->zrevrangebyscore( $set, '+inf', '-inf', 'withscores', 'limit', '0', $limit );

                if ( $items = array_map('intval', $items) ) {
                    foreach ( $items as $t => $score ) {
                        $topics[ $t ] = ( $topics[ $t ] ?: 0 ) + $score;
                    }
                }
            }

            arsort( $topics );
            $topics = array_slice($topics, 0, $limit);
        }

        return $topics;
    }
}

// incrementing topics
// TrendsApp::incrementTopic( 'twitter', 1 );
// TrendsApp::incrementTopic( 'linkedin', 4 );
// TrendsApp::incrementTopic( 'youtube', 2 );
// TrendsApp::incrementTopic( 'amazon', 10 );

// listing topics
var_dump( TrendsApp::getTopics( 2 ) );
