<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\UpdateAttributeCommand;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Exceptions\ViolationsException;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateAttributeCommandHandler
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly GetAttribute $getAttribute,
        private readonly CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver,
    ) {
    }

    public function __invoke(UpdateAttributeCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationsException($violations);
        }

        $attributeUuid = AttributeUuid::fromString($command->attributeUuid);
        $attribute = $this->getAttribute->byUuid($attributeUuid);
        if ($attribute === null) {
            throw new \InvalidArgumentException(sprintf('Attribute with uuid: %s does not exist', $command->attributeUuid));
        }

        $attribute->update($command->isRichTextArea, $command->labels);
        $this->categoryTemplateAttributeSaver->update($attribute);
    }
}
