<?php

declare(strict_types=1);

namespace joole\framework\validator;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{

    /**
     * Validates given data.
     *
     * Example:
     * <code>
     *      ```
     *          // action in controller
     *
     *      ```
     * </code>
     *
     * @param mixed $data
     * @return bool
     */
    public function validate(mixed $data = []): bool;

    /**
     * Returns errors for columns.
     *
     * Example:
     * <code>
     *      ```
     *      class PostDeleteValidator extends Validator{
     *      ```
     *      public function httpMessages():array{
     *          return [
     *              'id' => [
     *                  'required' => 'ID param required!',
     *                  'length.min' => 'id must be >= 0!'
     *                  'length.max' => 'id must be <= 99999!',
     *               ],
     *               // etc.
     *          ];
     *      }
     *      ```
     * </code>
     *
     * @return array
     */
    public function messages():array;

    /**
     * Returns rules for params of the array or object.
     *
     * Example:
     * ```
     * <code>
     *  ```
     *      public function rules():array{
     *          return [
     *              ['id', 'age', ['integer', 'required']],
     *              ['member_id', ['integer', 'required', 'belongs' => 'schema.members_t:id']],
     *          ];
     *      }
     *  ```
     * </code>
     * ```
     *
     * @return array
     */
    public function rules(): array;

}