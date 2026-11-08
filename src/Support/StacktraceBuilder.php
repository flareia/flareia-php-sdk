<?php

namespace FlareIA\Sdk\Support;

use Throwable;

class StacktraceBuilder
{
    /** @var array<string, list<string>|null> file path => its lines, cached across frames in one report */
    private array $fileLinesCache = [];

    public function __construct(
        private string $applicationPath,
        private bool $collectArguments = false,
    ) {}

    /**
     * @return list<array{file: string, line: int|null, function: string|null, is_application_frame: bool}>
     */
    public function build(Throwable $exception): array
    {
        $frames = [];

        // The throw site itself isn't part of getTrace(), so it's prepended here.
        $frames[] = $this->frame($exception->getFile(), $exception->getLine(), null);

        foreach ($exception->getTrace() as $entry) {
            $function = isset($entry['class'])
                ? $entry['class'].($entry['type'] ?? '::').($entry['function'] ?? '')
                : ($entry['function'] ?? null);

            $frames[] = $this->frame($entry['file'] ?? null, $entry['line'] ?? null, $function, $entry['args'] ?? null);
        }

        return $frames;
    }

    /**
     * @return list<array{class: string, message: string, stacktrace: array}>
     */
    public function buildChain(Throwable $exception): array
    {
        $chain = [];
        $previous = $exception->getPrevious();

        while ($previous !== null) {
            $chain[] = [
                'class' => get_class($previous),
                'message' => $previous->getMessage(),
                'stacktrace' => $this->build($previous),
            ];

            $previous = $previous->getPrevious();
        }

        return $chain;
    }

    /**
     * @return array{file: string, line: int|null, function: string|null, is_application_frame: bool}
     */
    private function frame(?string $file, ?int $line, ?string $function, ?array $args = null): array
    {
        $file ??= '';

        $isApp = $file !== ''
            && ! str_contains($file, '/vendor/')
            && ($this->applicationPath === '' || str_starts_with($file, $this->applicationPath));

        $frame = [
            'file' => $file,
            'line' => $line,
            'function' => $function,
            'is_application_frame' => $isApp,
        ];

        if ($this->collectArguments && $args) {
            $frame['arguments'] = array_map($this->summarizeArgument(...), $args);
        }

        $snippet = $this->codeSnippet($file, $line);
        if ($snippet !== null) {
            $frame['code_snippet'] = $snippet;
        }

        return $frame;
    }

    /**
     * Reads the lines surrounding the frame directly off disk — PHP runs
     * the real source on the same machine, so (unlike the browser SDKs,
     * which need a build-time source map) there's nothing to resolve.
     *
     * @return array<int, string>|null
     */
    private function codeSnippet(string $file, ?int $line, int $padding = 5): ?array
    {
        if ($file === '' || $line === null || $line < 1) {
            return null;
        }

        if (! array_key_exists($file, $this->fileLinesCache)) {
            $split = is_readable($file) && is_file($file)
                ? preg_split("/\r\n|\n|\r/", (string) file_get_contents($file))
                : null;

            $this->fileLinesCache[$file] = $split === false ? null : $split;
        }

        $lines = $this->fileLinesCache[$file];
        if ($lines === null) {
            return null;
        }

        $snippet = [];
        $start = max(1, $line - $padding);
        $end = min(count($lines), $line + $padding);

        for ($n = $start; $n <= $end; $n++) {
            // Purely numeric string keys are coerced back to int by PHP,
            // which is also what keeps the frontend's line-number lookup
            // (keyed by the JSON-decoded int) working.
            $snippet[$n] = $lines[$n - 1];
        }

        return $snippet === [] ? null : $snippet;
    }

    private function summarizeArgument(mixed $value): mixed
    {
        return match (true) {
            is_scalar($value) || $value === null => is_string($value) && strlen($value) > 255
                ? substr($value, 0, 255).'…'
                : $value,
            is_object($value) && method_exists($value, 'getKey') => get_class($value).'#'.$value->getKey(),
            $value instanceof \Countable => get_class($value).' ('.count($value).' items)',
            is_object($value) => get_class($value),
            is_array($value) => 'array ('.count($value).' items)',
            default => (string) $value,
        };
    }
}
