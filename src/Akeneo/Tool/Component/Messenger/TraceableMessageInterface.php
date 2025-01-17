<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Messenger;

use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TraceableMessageInterface extends TenantAwareInterface, CorrelationAwareInterface
{
}
