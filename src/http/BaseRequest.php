<?php

declare(strict_types=1);

namespace joole\framework\http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use TypeError;
use function explode;
use function is_string;
use function str_replace;
use function strtolower;
use function substr;
use function ucwords;

/**
 * Request - is an exemplar of user request as object.
 *
 * @package joole\framework\http
 */
abstract class BaseRequest implements RequestInterface
{

    /** @var string[] Headers. */
    private array $headers;
    /** @var string Protocol (https/http). */
    private string $protocol;
    /** @var string Protocol's version (1.0/1.1). */
    private string $protocolVersion;
    /** @var string Request method */
    private string $method;
    /** @var BaseUri Uri exemplar. */
    private BaseUri $uri;
    /** @var mixed Returns target. */
    private mixed $requestTarget;

    /** @var string[] Allowed protocol versions. */
    private const ALLOWED_PROTOCOL_VERSIONS = [
        '1.0',
        '1.1',
    ];

    /** @var string[] Allowed request methods */
    protected const ALLOWED_METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
    ];

    public function __construct()
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) !== 'HTTP_') {
                continue;
            }

            $header = str_replace(' ', '-', ucwords(
                str_replace('_', ' ', strtolower(substr($key, 5)))
            ));

            $headers[$header] = explode(', ', $value);
        }

        $this->headers = $headers;
        [$this->protocol, $this->protocolVersion] = explode('/', strtolower($_SERVER['SERVER_PROTOCOL']));
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = new BaseUri();
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

    public function withProtocolVersion($version): static
    {
        if (!is_string($version)) {
            throw new TypeError('Invalid protocol\'s version given: ' . gettype($version));
        }

        if (!in_array($version, self::ALLOWED_PROTOCOL_VERSIONS)) {
            throw new HttpException('Invalid http protocol\'s version given: ' . $version);
        }

        $oldVersion = $this->protocolVersion;
        $this->protocolVersion = $version;
        $exemplar = clone $this;
        $this->protocolVersion = $oldVersion;

        return $exemplar;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        if (!is_string($name)) {
            throw new TypeError('Invalid header\'s type given: ' . gettype($name));
        }

        return isset($this->headers[$name]);
    }

    public function getHeader($name): array
    {
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * @param string $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withHeader($name, $value): static
    {
        $values = is_string($value) ? explode(', ', $value) : $value;

        $this->headers[$name] = $values;

        return $this;
    }

    public function withAddedHeader($name, $value): static
    {
        if (!is_string($name)) {
            throw new TypeError('Invalid header\'s type given: ' . gettype($name));
        }

        if (!is_array($value) || !is_string($value)) {
            throw new TypeError('Invalid header\'s value type given: ' . gettype($value));
        }

        $oldValues = $this->headers[$name] ?? [];
        $newValues = is_string($value) ? explode(', ', $value) : [$value];
        $this->headers[$name] = array_merge($oldValues, $newValues);
        $exemplar = clone $this;
        $this->headers[$name] = $oldValues;

        return $exemplar;
    }

    public function withoutHeader($name): static
    {
        if (!is_string($name)) {
            throw new TypeError('Invalid header\'s type given: ' . gettype($name));
        }

        $oldValue = $this->headers[$name] ?? null;

        unset($this->headers[$name]);

        $exemplar = clone $this;

        if ($oldValue) {
            $this->headers[$name] = $oldValue;
        }

        return $exemplar;
    }

    public function getBody()
    {
        // TODO: Implement getBody() method.
    }

    public function withBody(StreamInterface $body)
    {
        // TODO: Implement withBody() method.
    }

    public function getRequestTarget():mixed
    {
        return $this->requestTarget;
    }

    public function withRequestTarget($requestTarget) : static
    {
        $oldTarget = $this->requestTarget ?? false;
        $this->requestTarget = $requestTarget;
        $exemplar = clone $this;

        if($oldTarget === false){
            unset($this->requestTarget);
        }else{
            $this->requestTarget = $oldTarget;
        }

        return $exemplar;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function withMethod($method) :static
    {
        if (!is_string($method)) {
            throw new TypeError('Invalid method\'s type given: ' . gettype($method));
        }

        if (!in_array($method, self::ALLOWED_METHODS)) {
            throw new HttpException(
                'Invalid method given: ' . $method.
                '. Expected: '.implode(', ', self::ALLOWED_METHODS)
            );
        }

        $oldMethod = $this->method;
        $this->method = $method;
        $exemplar = clone $this;
        $this->method = $oldMethod;

        return $exemplar;
    }

    public function getUri():BaseUri
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false):static
    {
        if($preserveHost){
            $this->uri = $uri;

            return $this;
        }

        $oldUri = $this->uri;
        $this->uri = $uri;
        $exemplar = clone $this;
        $this->uri = $oldUri;

        return $exemplar;
    }
}