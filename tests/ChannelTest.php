<?php

namespace NotificationChannels\Pushover\Test;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Notification;
use Mockery;
use NotificationChannels\Pushover\PushoverChannel;
use NotificationChannels\Pushover\PushoverMessage;
use NotificationChannels\Pushover\Pushover;
use PHPUnit_Framework_TestCase;

class ChannelTest extends PHPUnit_Framework_TestCase
{
    /** @var PushoverChannel */
    protected $channel;

    /** @var Pushover */
    protected $pushover;

    /** @var Notification */
    protected $notification;

    /** @var PushoverMessage */
    protected $message;

    /** @var Dispatcher */
    protected $events;

    public function setUp()
    {
        parent::setUp();

        $this->pushover = Mockery::mock(Pushover::class);
        $this->channel = new PushoverChannel($this->pushover);
        $this->notification = Mockery::mock(Notification::class);
        $this->message = Mockery::mock(PushoverMessage::class);

        $this->events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');

        app()->instance('events', $this->events);
    }

    /** @test */
    public function it_can_send_a_message_to_pushover()
    {
        $notifiable = new Notifiable;

        $this->events->shouldReceive('fire')->twice();
        $this->notification->shouldReceive('toPushover')
            ->with($notifiable)
            ->andReturn($this->message);
        $this->pushover->shouldReceive('send')
            ->with(Mockery::subset([
                'user' => 'pushover-key'
            ]));

        $this->channel->send($notifiable, $this->notification);

    }

    /** @test */
    public function it_fires_events_while_sending_a_message()
    {
        $this->notification->shouldReceive('toPushover')->andReturn($this->message);
        $this->pushover->shouldReceive('send');

        $this->events->shouldReceive('fire')->twice();

        $this->channel->send(new Notifiable, $this->notification);

    }
}
