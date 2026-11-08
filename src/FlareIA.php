<?php

namespace FlareIA\Sdk;

use FlareIA\Sdk\AttributesProviders\PhpRequestAttributesProvider;
use FlareIA\Sdk\Sampling\RateSampler;
use FlareIA\Sdk\Senders\CurlSender;
use FlareIA\Sdk\Support\SecureBaseUrl;
use FlareIA\Sdk\Support\StacktraceBuilder;
use Throwable;

class FlareIA
{
    private float $startedAt = 0;

    private ?Api $api = null;

    private ?StacktraceBuilder $stacktraceBuilder = null;

    private RateSampler $sampler;

    private PhpRequestAttributesProvider $requestAttributesProvider;

    private function __construct(
        private FlareIAConfig $config,
    ) {
        $this->sampler = new RateSampler;
        $this->requestAttributesProvider = new PhpRequestAttributesProvider;
    }

    public static function make(FlareIAConfig $config): self
    {
        return new self($config);
    }

    public function start(): void
    {
        $this->startedAt = microtime(true);
        $this->api = new Api(new CurlSender, $this->resolveBaseUrl(), $this->config->apiKey);
        $this->stacktraceBuilder = new StacktraceBuilder($this->config->applicationPath);

        set_exception_handler(function (Throwable $exception): void {
            $this->report($exception, handled: false);
            exit(255);
        });

        register_shutdown_function(function (): void {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                $this->report(new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line'],
                ), handled: false);
            }

            $this->end();
        });
    }

    public function end(): void
    {
        if ($this->startedAt <= 0 || $this->api === null) {
            return;
        }

        // Optional request timing hook for plain PHP — callers can extend later.
        $this->startedAt = 0;
    }

    public function report(Throwable $exception, bool $handled = true): void
    {
        if ($this->api === null || $this->stacktraceBuilder === null) {
            $this->api ??= new Api(new CurlSender, $this->resolveBaseUrl(), $this->config->apiKey);
            $this->stacktraceBuilder ??= new StacktraceBuilder($this->config->applicationPath);
        }

        if (! $this->sampler->shouldSample($this->config->sampleRate)) {
            return;
        }

        $this->api->sendError([
            'exception' => [
                'class' => $exception::class,
                'message' => $exception->getMessage(),
            ],
            'stacktrace' => $this->stacktraceBuilder->build($exception),
            'previous' => $this->stacktraceBuilder->buildChain($exception),
            'request' => $this->requestAttributesProvider->attributes(),
            'environment' => $this->config->environment,
            'release' => $this->config->release,
            'server_name' => gethostname() ?: null,
            'handled' => $handled,
            'occurred_at' => gmdate('c'),
        ]);
    }

    private function resolveBaseUrl(): string
    {
        if ($this->config->useDaemon) {
            $daemon = getenv('FLAREIA_DAEMON_URL') ?: 'http://127.0.0.1:8787';

            return SecureBaseUrl::validate($daemon);
        }

        return SecureBaseUrl::validate($this->config->baseUrl);
    }
}
