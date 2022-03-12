<?php

declare(strict_types=1);

namespace joole\framework\http;

use Psr\Http\Message\UriInterface;
use TypeError;
use function explode;
use function filter_var;
use function gettype;
use function is_int;
use function is_numeric;
use function is_string;
use function preg_match;
use function stripos;
use function strtolower;
use function substr_count;

/**
 * A base representation of URI.
 *
 * @package joole\framework\http
 */
class BaseUri implements UriInterface
{

    /** @var string Scheme of request. */
    private string $scheme;
    /** @var string Host. */
    private string $host;
    /** @var int Port. */
    private int $port;
    /** @var string|null User's info. */
    private ?string $userInfo;
    /** @var string Requested path. */
    private string $path;
    /** @var string Query string. */
    private string $query;

    /** @var string[] Allowed schemes */
    private const ALLOWED_SCHEMES = [
        'http://',
        'https://',
    ];

    public function __construct()
    {
        $host = $_SERVER['HTTP_HOST'];

        // Port setting and removing it from host.
        if (stripos($host, ':')) {
            [$host, $this->port] = explode(':', $host);
        }

        // Authority and user info
        if (stripos($host, '@') !== false) {

            if (substr_count($host, '@') > 1) {
                throw new HttpException(
                    'Invalid address got! An address can include only one occurrence of a character "@"!'
                );
            }

            [$this->userInfo, $host] = explode('@', $host);
        }

        $this->path = $_SERVER['PATH_INFO'] ?? '';
        $this->host = $host;
        $this->query = $_SERVER['QUERY_STRING'] ?? '';
        $this->scheme = explode('/', strtolower($_SERVER['SERVER_PROTOCOL']))[0] . '://';
    }

    /**
     * @inheritDoc
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority(): string
    {
        $authority = '';

        if (isset($this->userInfo)) {
            $authority .= $this->userInfo . '@';
        }

        $authority .= $this->host;

        if (isset($this->port)) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(): string
    {
        return $this->userInfo ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function getPort(): int
    {
        return
            $this->port ?? ($this->scheme === 'https://' ? 443 : 80);
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): string
    {
        if (!stripos($path = $this->path, '#')) {
            return '';
        }

        return explode('#', $path)[1];
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme): static
    {
        if (!is_string($scheme)) {
            throw new TypeError('Invalid scheme\'s type given: ' . gettype($scheme));
        }

        if (!in_array($scheme, self::ALLOWED_SCHEMES)) {
            throw new HttpException('Invalid http scheme given: ' . $scheme);
        }

        $oldScheme = $this->scheme;
        $this->scheme = $scheme;
        $exemplar = clone $this;
        $this->scheme = $oldScheme;

        return $exemplar;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null): static
    {
        if (!is_string($user)) {
            throw new TypeError('Invalid user\'s name type given: ' . gettype($user));
        }

        if ($password) {
            if (!is_string($password)) {
                throw new TypeError('Invalid user\'s password type given: ' . gettype($user));
            }
        }

        $oldInfo = $this->userInfo;
        $this->userInfo = $user;

        if ($password) {
            $this->userInfo .= ':' . $password;
        }

        $exemplar = clone $this;
        $this->userInfo = $oldInfo;

        return $exemplar;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host): static
    {
        if (!is_string($host)) {
            throw new TypeError('Invalid host\'s type given: ' . gettype($host));
        }

        if (
            preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $host)
            && preg_match("/^.{1,253}$/", $host)
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $host)
        ) {
            throw new HttpException('Invalid host given: ' . $host);
        }

        $oldHost = $this->host;
        $this->host = $host;
        $exemplar = clone $this;
        $this->host = $oldHost;

        return $exemplar;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port): static
    {
        if (!is_int($port) || is_string($port) && !is_numeric($port)) {
            throw new TypeError('Invalid port\'s type given: ' . gettype($port));
        }

        if ($port < 0 || $port > 65535) {
            throw new HttpException('Invalid port given: ' . $port . '. Allowed 0 - 65535 only.');
        }

        $portExists = isset($this->port);
        $oldPort = $this->getPort();
        $this->port = $port;
        $exemplar = clone $this;

        if (!$portExists) {
            unset($this->port);
        } else {
            $this->port = $oldPort;
        }

        return $exemplar;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path): static
    {
        if (!is_string($path)) {
            throw new TypeError('Invalid path\'s type given: ' . gettype($path));
        }

        if (!preg_match('/^[A-Za-z0-9_.]+$/', $path)) {
            throw new HttpException('Invalid path given: ' . $path);
        }

        $oldPath = $this->path;
        $this->path = $path;
        $exemplar = clone $this;
        $this->path = $oldPath;

        return $exemplar;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query): static
    {
        if (!is_string($query)) {
            throw new TypeError('Invalid query\'s type given: ' . gettype($query));
        }

        if (!preg_match('/^[A-Za-z0-9_=.]+$/', $query)) {
            throw new HttpException('Invalid query given: ' . $query);
        }

        $oldQuery = $this->query;
        $this->query = $query;
        $exemplar = clone $this;
        $this->query = $oldQuery;

        return $exemplar;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment): static
    {
        if (!filter_var($fragment, FILTER_VALIDATE_URL)) {
            throw new HttpException('Invalid fragment given: ' . $fragment);
        }

        $oldPath = $this->path;
        $path = $oldPath;

        if (stripos($path, '#')) {
            $path = explode('#', $path)[0];
        }

        $this->path = $path . '#' . $fragment;
        $exemplar = clone $this;
        $this->path = $oldPath;

        return $exemplar;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return
            $this->getScheme()// Scheme
            . $this->getHost()// Host
            . (isset($this->port) ? ':' . $this->port : '')// Port if set
            . $this->getPath()// Path
            . $this->getQuery();// Query
    }
}