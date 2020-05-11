<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BusinessErrorRepository
{
    /**
     * @param BusinessError[] $businessErrors
     */
    public function bulkInsert(array $businessErrors): void;
}
