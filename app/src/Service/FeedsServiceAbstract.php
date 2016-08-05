<?php

namespace App\Service;

class FeedsServiceAbstract
{
    private $feeds = array();


    /**
     * @return array
     */
    public function getFeeds()
    {
        return $this->feeds;
    }

    protected function addFeed(array $feed)
    {
        $this->feeds[] = $feed;
    }
}