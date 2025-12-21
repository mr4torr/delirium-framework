<?php

declare(strict_types=1);

namespace Delirium\Validation\Adapter;

use Delirium\Validation\Contract\ValidatorInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidator;

class SymfonyValidatorAdapter implements ValidatorInterface
{
    private SymfonyValidator $validator;

    public function __construct()
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    /**
     * @inheritDoc
     */
    public function validate(mixed $value): array
    {
        $violations = $this->validator->validate($value);
        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $propertyPath = $violation->getPropertyPath();
                $errors[$propertyPath][] = (string) $violation->getMessage();
            }
        }

        return $errors;
    }
}
