<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Registry;

use Akeneo\Tool\Component\Messenger\TraceableMessageHandlerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TraceableMessageHandlerRegistrySpec extends ObjectBehavior
{
    public function let(TraceableMessageHandlerInterface $handler1, TraceableMessageHandlerInterface $handler2)
    {
        $this->registerHandler($handler1, 'consumer1');
        $this->registerHandler($handler2, 'consumer2');
    }

    public function it_returns_the_handler(
        TraceableMessageHandlerInterface $handler1,
        TraceableMessageHandlerInterface $handler2
    ) {
        $this->getHandler('consumer1')->shouldReturn($handler1);
        $this->getHandler('consumer2')->shouldReturn($handler2);
    }

    public function it_throws_an_exception_when_no_handler_is_found()
    {
        $this->shouldThrow(\LogicException::class)->during('getHandler', ['unknown']);
    }

    public function it_throws_an_exception_when_handler_is_registerer_twice_for_a_consumer(
        TraceableMessageHandlerInterface $handler3
    ) {
        $this->shouldThrow(\LogicException::class)->during('registerHandler', [$handler3, 'consumer1']);
    }
}
