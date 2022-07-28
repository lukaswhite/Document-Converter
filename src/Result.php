<?php


namespace Lukaswhite\DocumentConverter;


class Result
{
    const SUCCESS = 'success';
    const FAIL = 'fail';

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $filepath;

    /**
     * @var int
     */
    protected $took;

    /**
     * Result constructor.
     * @param string $status
     */
    public function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return static
     */
    public static function success(): self
    {
        return new self(self::SUCCESS);
    }

    /**
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->status === self::SUCCESS;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getFilepath(): string
    {
        return $this->filepath;
    }

    /**
     * @param string $filepath
     * @return self
     */
    public function setFilepath(string $filepath): self
    {
        $this->filepath = $filepath;
        $this->filename = pathinfo($this->filepath, PATHINFO_BASENAME);
        return $this;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return pathinfo($this->filepath, PATHINFO_EXTENSION);
    }

    /**
     * @return int
     */
    public function getFilesize(): int
    {
        return filesize($this->filepath);
    }

    /**
     * @param float $start
     * @param float $end
     * @return $this
     */
    public function setTimes(float $start, float $end): self
    {
        $this->took = $end - $start;
        return $this;
    }

    /**
     * @return float
     */
    public function getTimeElapsed(): float
    {
        return $this->took;
    }


}