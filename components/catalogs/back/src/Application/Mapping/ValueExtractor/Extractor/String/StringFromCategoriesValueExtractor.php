<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\GetCachedCategoryLabelsByLocaleAndProduct;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringFromCategoriesValueExtractor implements StringValueExtractorInterface
{
    public function __construct(
        private GetCachedCategoryLabelsByLocaleAndProduct $getCachedCategoryLabelsByLocaleAndProduct,
    ) {
    }

    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        if (\is_null($parameters['locale_label'] ?? null)) {
            return null;
        }
        /** @var string */
        $uuid = $product['uuid']->serialize();
        $categoriesLabels = $this->getCachedCategoryLabelsByLocaleAndProduct->fetch(
            [$uuid],
            [$parameters['locale_label']]
        );
        if (\count($categoriesLabels[$uuid][$parameters['locale_label']]) === 0) {
            return null;
        }
        return \implode(', ', $categoriesLabels[$uuid][$parameters['locale_label']]);
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_CATEGORIES;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_STRING;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }
}
