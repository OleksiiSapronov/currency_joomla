<?php

namespace _JchOptimizeVendor\Spatie\Robots;

class RobotsMeta
{
    protected $robotsMetaTagProperties = [];

    public function __construct(string $html)
    {
        $this->robotsMetaTagProperties = $this->findRobotsMetaTagProperties($html);
    }

    public static function readFrom(string $source): self
    {
        $content = @\file_get_contents($source);
        if (\false === $content) {
            throw new \InvalidArgumentException("Could not read from source `{$source}`");
        }

        return new self($content);
    }

    public static function create(string $source): self
    {
        return new self($source);
    }

    public function mayIndex(): bool
    {
        return !$this->noindex();
    }

    public function mayFollow(): bool
    {
        return !$this->nofollow();
    }

    public function noindex(): bool
    {
        return $this->robotsMetaTagProperties['noindex'] ?? \false;
    }

    public function nofollow(): bool
    {
        return $this->robotsMetaTagProperties['nofollow'] ?? \false;
    }

    protected function findRobotsMetaTagProperties(string $html): array
    {
        $metaTagLine = $this->findRobotsMetaTagLine($html);

        return ['noindex' => $metaTagLine ? \false !== \strpos(\strtolower($metaTagLine), 'noindex') : \false, 'nofollow' => $metaTagLine ? \false !== \strpos(\strtolower($metaTagLine), 'nofollow') : \false];
    }

    protected function findRobotsMetaTagLine(string $html): ?string
    {
        if (\preg_match('/\\<meta name="robots".*?\\>/mis', $html, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
