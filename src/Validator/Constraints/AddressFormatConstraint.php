<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use CommerceGuys\Addressing\AddressFormat\FieldOverrides;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @codeCoverageIgnore
 */
class AddressFormatConstraint extends Constraint
{
    public ?FieldOverrides $fieldOverrides = null;

    public bool $validatePostalCode = true;

    public string $blankMessage = 'This value should be blank';

    public string $notBlankMessage = 'This value should not be blank';

    public string $invalidMessage = 'This value is invalid.';

    /**
     * Constructs a new AddressFormatConstraint object.
     *
     * Accepts named arguments, and the options array used by older code.
     * Symfony 7.3 deprecated the options array and Symfony 8 ignores it,
     * so the known options are read here directly.
     *
     * @param array|null $options The options (deprecated, use named arguments).
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        ?FieldOverrides $fieldOverrides = null,
        ?bool $validatePostalCode = null,
        ?string $blankMessage = null,
        ?string $notBlankMessage = null,
        ?string $invalidMessage = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        $options ??= [];
        parent::__construct(null, $groups ?? $options['groups'] ?? null, $payload ?? $options['payload'] ?? null);

        $this->fieldOverrides = $fieldOverrides ?? $options['fieldOverrides'] ?? new FieldOverrides([]);
        $this->validatePostalCode = $validatePostalCode ?? $options['validatePostalCode'] ?? $this->validatePostalCode;
        $this->blankMessage = $blankMessage ?? $options['blankMessage'] ?? $this->blankMessage;
        $this->notBlankMessage = $notBlankMessage ?? $options['notBlankMessage'] ?? $this->notBlankMessage;
        $this->invalidMessage = $invalidMessage ?? $options['invalidMessage'] ?? $this->invalidMessage;
    }

    /**
     * @return string|string[]
     */
    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
