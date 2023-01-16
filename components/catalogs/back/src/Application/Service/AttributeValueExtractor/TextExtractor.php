<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service\AttributeValueExtractor;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TextExtractor implements AttributeValueExtractorInterface
{
    public function extract(
        array $product,
        string $attributeCode,
        string $attributeType,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {

        return $product['raw_values'][$attributeCode][$scope][$locale] ?? null;
    }

    public function support(string $attributeType): bool
    {
        return $attributeType === 'pim_catalog_text';
    }
}
