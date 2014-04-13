<?php

namespace GuzzleHttp\Event;

interface EventTriggerInterface
{

    /**
     * Returns an array of events that can be triggered by this class
     *
     * @return array
     */
    public function getTriggeredEvents();
}