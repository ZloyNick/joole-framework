<?php

declare(strict_types=1);

namespace joole\framework\validator;

use JetBrains\PhpStorm\ArrayShape;
use joole\framework\exception\validation\ValidationException;

use joole\framework\http\file\UploadedFile;
use function is_null, is_string, is_array, is_numeric, count, mb_strlen, sprintf;

/**
 * The basic validator class.
 */
class Validator implements ValidatorInterface
{

    /**
     * @var array<string, <array<string>>> Errors
     */
    private array $errors = [];

    /**
     * @inheritDoc
     * @throws ValidationException
     */
    public function validate(mixed $data = []): bool
    {
        foreach ($data as $column => $value) {
            if (!isset($this->rules()[$column])) {
                continue;
            }

            $this->validateColumn($column, $value, $this->rules()[$column]);
        }

        return count($this->getErrors()) > 0;
    }

    /**
     * @throws ValidationException
     */
    final public function validateColumn(string $column, mixed $value, array $rules): void
    {

        if (method_exists($this, 'validate' . $column)) {
            $this->{'validate' . $column}($value);

            return;
        }

        $defaultMessages = $this->getDefaultRulesMessages();

        if (in_array('int', $rules)) {
            if (!is_numeric($value)) {
                $this->errors[$column] = sprintf($defaultMessages['int'], $column);
            }
        } elseif (in_array('string', $rules)) {
            if (!is_string($value)) {
                $this->errors[$column] = sprintf($defaultMessages['string'], $column);
            }
        } elseif (in_array('file', $rules)) {
            if ($value instanceof UploadedFile) {
                $this->errors[$column] = sprintf($defaultMessages['file'], $column);
            }
        } elseif (in_array('array', $rules)) {
            if (!is_array($value)) {
                $this->errors[$column] = sprintf($defaultMessages['array'], $column);
            }

            if (isset($rules['size'])) {
                if ($rules['size'] < 0) {
                    throw new ValidationException('The "size" rule should have a positive value');
                }

                if (count($value) > $rules['size']) {
                    $this->errors[$column] = sprintf($defaultMessages['size'], $column, $rules['size']);
                }
            }
        }

        if (isset($rules['min'])) {
            if (mb_strlen((string)$value) < $rules['min']) {
                $this->errors[$column] = sprintf($defaultMessages['min'], $column, $rules['min']);
            }
        }

        if (isset($rules['max'])) {
            if (mb_strlen((string)$value) > $rules['max']) {
                $this->errors[$column] = sprintf($defaultMessages['max'], $column, $rules['max']);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Returns errors of columns.
     *
     * @return array<string, <array<string>>>
     */
    final public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Adds error for column.
     *
     * @param string $column Column name.
     * @param string $errorCase Error type.
     *
     * <b>Note: Type must exist in messages array!</b>
     * @return void
     *
     * @throws ValidationException
     * @see ValidatorInterface::messages()
     *
     */
    final public function addError(string $column, string $errorCase): void
    {
        if (!isset($this->errors[$column])) {
            $this->errors[$column] = [];
        }

        if (!isset($this->messages()[$column])) {
            throw new ValidationException('Messages for column "' . $column . '" doesn\'t exists!');
        }

        $this->errors[$column][] = $errorCase;
    }

    /**
     * Removes all or given errors for column.
     *
     * @param string $column Column name.
     * @param array|null $errors Array of errors keys.
     * If "null" given, removes all errors of column.
     * @return void
     * @throws ValidationException Will through on invalid type of error.
     */
    final public function removeErrors(string $column, ?array $errors = null): void
    {
        if (!isset($this->errors[$column])) {
            throw new ValidationException('Errors for column "' . $column . '" doesn\'t exists!');
        }

        if (is_null($errors)) {
            unset($this->errors[$column]);

            return;
        }

        foreach ($errors as $errorName) {
            if (!is_string($errorName)) {
                throw new ValidationException('Invalid error name given. String expected. Column: ' . $column);
            }

            unset($this->errors[$column][$errorName]);
        }
    }

    /**
     * Clears all errors.
     *
     * @return void
     */
    final public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Messages for the default rules.
     *
     * @return string[]
     */
    #[ArrayShape([
            'int' => "string",
            'string' => "string",
            'array' => "string",
            'file' => "string",
            'min' => "string",
            'max' => "string",
            'size' => "string"]
    )]
    protected function getDefaultRulesMessages(): array
    {
        return [
            'int' => 'The %s attribute must have an integer value.',
            'string' => 'The %s attribute must have an string value.',
            'array' => 'The %s attribute must have an array value.',
            'file' => 'The %s attribute must be a file.',
            'min' => 'The %s attribute must have a minimum length of %s symbols.',
            'max' => 'The %s attribute must have a maximum length of %s symbols.',
            'size' => 'The number of elements of the %s attribute array should not exceed %s.',
        ];
    }
}