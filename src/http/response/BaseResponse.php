<?php

namespace joole\framework\http\response;


abstract class BaseResponse
{

    /** @var array<string, array> Headers. */
    private array $headers = [];
    /** @var string */
    protected string $content = '';
    /** @var int Response code */
    protected int $code = 200;

    /**
     * Returns headers and values.
     *
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Checks header existing.
     *
     * @param string $name Header name.
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * Returns header values.
     *
     * If header doesn't exists - returns empty array.
     *
     * @param string $name
     *
     * @return array
     */
    public function getHeader(string $name): array
    {
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * Sets values to header.
     *
     * <b>Note: </b>all headers will be rewritten.
     *
     * @param string $name Header name.
     * @param array<string, mixed> $values Header new values.
     *
     * @return static
     */
    public function withHeader(string $name, array $values): static
    {
        header($name . ': ' . implode(',', $values));

        $this->headers[$name] = $values;

        return $this;
    }

    /**
     * Adds header with values.
     *
     * @param string $name
     * @param array $values
     * @return $this
     */
    public function withAddedHeader(string $name, array $values): static
    {
        $this->headers[$name] = [...$this->headers[$name] ?? [], ...$values];

        return $this;
    }

    /**
     * Removes header.
     *
     * @param string $name Header name
     *
     * @return static
     */
    public function withoutHeader(string $name): static
    {
        unset($this->headers[$name]);

        return $this;
    }

    public function withOutput(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function __toString(): string
    {
        http_response_code($this->code);

        return $this->content;
    }

    /**
     * Sets json format for response.
     *
     * @param array $data Data.
     * @param int $code Http code.
     *
     * @return static
     */
    abstract public function asJson(array $data, int $code):static;

    /**
     * Returns file.
     *
     *
     *
     * @param string $filePath
     * @return $this
     */
    abstract public function asFile(string $filePath):static;

}