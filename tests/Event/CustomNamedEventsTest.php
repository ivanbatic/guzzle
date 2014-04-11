<?php

namespace GuzzleHttp\Tests\Event;

use GuzzleHttp\Client;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\EventInterface;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;

/**
 * @covers GuzzleHttp\Event\AbstractTransferEvent
 */
class CustomNamedEventsTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultEvents()
    {
        $client      = new Client();
        $namedEvents = $client->getNamedEvents();

        $typeOfNamedEvents = gettype($namedEvents);
        $this->assertInternalType('array', $namedEvents, "Named events are expected to be an array by default, $typeOfNamedEvents given."
        );

        $client->resetNamedEvents();

        $this->assertEquals(['before', 'complete', 'error'], $client->getNamedEvents());

        $client->unregisterNamedEvents('before', 'complete', 'error');
        $client->resetNamedEvents();

        $this->assertEquals(['before', 'complete', 'error'], $client->getNamedEvents());
    }

    public function testEventRegistering()
    {
        $client        = new Client();
        $defaultEvents = $client->getNamedEvents();

        $client->registerNamedEvents('test-1');
        $this->assertEquals(array_merge($defaultEvents, ['test-1']), $client->getNamedEvents());
        $client->resetNamedEvents();

        $client->registerNamedEvents('test-2', 'test-3', 'test-4');
        $this->assertEquals(array_merge($defaultEvents, ['test-2', 'test-3', 'test-4']), $client->getNamedEvents());
        $client->resetNamedEvents();

        $client->registerNamedEvents(['test-5', 'test-6', 'test-7']);
        $this->assertEquals(array_merge($defaultEvents, ['test-5', 'test-6', 'test-7']), $client->getNamedEvents());
    }

    public function testEventUnregistering()
    {
        $client = new Client();

        $client->unregisterNamedEvents('complete');

        $this->assertEquals(['before', 'error'], array_values($client->getNamedEvents()),
            'Failed unregistering a single event'
        );

        $client->unregisterNamedEvents(['before', 'error']);
        $this->assertEquals([], $client->getNamedEvents());

        $client->resetNamedEvents();

        $client->unregisterNamedEvents('before', 'error');
        $this->assertEquals(['complete'], array_values($client->getNamedEvents()));
    }

    public function testCustomEventTriggering()
    {
        $client = new Client();
        $client->registerNamedEvents('allowed');

        $customEventTriggered    = false;
        $forbiddenEventTriggered = false;

        $request = new Request('GET', '/');


        $client->sendAll([$request], [
                'before'    => function (BeforeEvent $event) {
                        $event->getRequest()->getEmitter()->emit('allowed', $event);
                        $event->getRequest()->getEmitter()->emit('forbidden', $event);
                        $event->intercept(new Response(200));
                    },
                'allowed'    => function (EventInterface $event) use (&$customEventTriggered) {
                        $customEventTriggered = true;
                    },
                'forbidden' => function (EventInterface $event) use (&$forbiddenEventTriggered) {
                        $forbiddenEventTriggered = true;
                    }
            ]
        );

        $this->assertTrue($customEventTriggered);
        $this->assertFalse($forbiddenEventTriggered);
    }
}
