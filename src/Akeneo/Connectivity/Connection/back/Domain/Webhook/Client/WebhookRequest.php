<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Client;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookRequest
{
    /** @var ActiveWebhook */
    private $webhook;

    /** @var array<WebhookEvent> */
    private $apiEvents;

    /**
     * @param array<WebhookEvent> $apiEvents
     */
    public function __construct(ActiveWebhook $webhook, array $apiEvents)
    {
        $this->webhook = $webhook;
        $this->apiEvents = $apiEvents;
    }

    /**
     * Returns webhook URL.
     */
    public function url(): string
    {
        return $this->webhook->url();
    }

    /**
     * Returns webhook secret to sign the request.
     */
    public function secret(): string
    {
        return $this->webhook->secret();
    }

    /**
     * Returns request content.
     *
     * @return array<array{
     *  action: string,
     *  event_id: string,
     *  event_date: string,
     *  author: string,
     *  author_type: string,
     *  pim_source: string,
     *  data: array
     * }>
     */
    public function content(): array
    {
        return \array_map(function (WebhookEvent $apiEvent) {
            return [
                'action' => $apiEvent->action(),
                'event_id' => $apiEvent->eventId(),
                'event_date' => $apiEvent->eventDate(),
                'author' => $apiEvent->author()->name(),
                'author_type' => $apiEvent->author()->type(),
                'pim_source' => $apiEvent->pimSource(),
                'data' => $apiEvent->data(),
            ];
        }, $this->apiEvents);
    }

    public function webhook(): ActiveWebhook
    {
        return $this->webhook;
    }

    /**
     * @return array<WebhookEvent>
     */
    public function apiEvents(): array
    {
        return $this->apiEvents;
    }
}
