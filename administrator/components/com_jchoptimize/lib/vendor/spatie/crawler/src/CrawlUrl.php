<?php

namespace _JchOptimizeVendor\Spatie\Crawler;

use _JchOptimizeVendor\Psr\Http\Message\UriInterface;

class CrawlUrl
{
    public UriInterface $url;
    public ?UriInterface $foundOnUrl = null;

    /** @var mixed */
    protected $id;

    protected function __construct(UriInterface $url, $foundOnUrl = null)
    {
        $this->url = $url;
        $this->foundOnUrl = $foundOnUrl;
    }

    public static function create(UriInterface $url, ?UriInterface $foundOnUrl = null, $id = null)
    {
        $static = new static($url, $foundOnUrl);
        if (null !== $id) {
            $static->setId($id);
        }

        return $static;
    }

    /**
     * @return null|mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
