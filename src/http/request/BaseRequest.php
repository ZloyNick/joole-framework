<?php

declare(strict_types=1);

namespace joole\framework\http\request;

use function explode;
use function str_starts_with;
use function str_replace;
use function strtolower;
use function substr;
use function ucwords;

/**
 * Request - is an exemplar of client request as object.
 */
abstract class BaseRequest
{

    /** @var array<string, array> Headers. */
    private array $headers;
    /** @var string Protocol (https/http). */
    private string $protocol;
    /** @var string Protocol's version (1.0/1.1). */
    private string $protocolVersion;
    /** @var string Request method */
    private string $method;
    /** @var BaseUri Uri exemplar. */
    private BaseUri $uri;

    public function __construct()
    {
        $headers = &$this->headers;

        foreach ($_SERVER as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $headers[self::extractHeaderName($key)] = explode(', ', $value);
        }

        [
            $this->protocol,
            $this->protocolVersion
        ] = explode('/', strtolower($_SERVER['SERVER_PROTOCOL']));

        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = new BaseUri();
    }

    /**
     * Extracts header from original name.
     *
     * @param string $header
     *
     * @return string
     */
    private static function extractHeaderName(string $header): string
    {
        return str_replace(' ', '-', ucwords(
            str_replace('_', ' ', strtolower(substr($header, 5)))
        ));
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * Returns protocol.
     *
     * @return string "http" or "https"
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

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
        return implode(', ', $this->getHeader($name));
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

    /**
     * Returns request method.
     *
     * @return string Method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns an uri exemplar.
     *
     * @return BaseUri
     */
    public function getUri(): BaseUri
    {
        return $this->uri;
    }

    /**
     * Sets uri exemplar.
     *
     * @param BaseUri $uri The uri exemplar.
     *
     * @return static
     */
    public function withUri(BaseUri $uri): static
    {
        $this->uri = $uri;

        return $this;
    }
}