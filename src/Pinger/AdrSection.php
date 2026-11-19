<?php
declare(strict_types=1);

namespace App\Pinger;

/**
 * One section of an address book file while it is being written.
 *
 * Keeps count of the bytes its key names take, as Pinger reads them into a buffer of fixed size.
 *
 * @internal
 */
final class AdrSection
{
    /**
     * The keys written so far, compared without regard to case as Windows compares them.
     *
     * @var array<string, true>
     */
    private array $keys = [];

    private int $keyBytes = 0;

    /**
     * @var list<string>
     */
    private array $lines = [];

    /**
     * @param string $title What the section is headed with.
     * @param int $maxKeyBytes Bytes the key names may take, each with its terminating null.
     */
    public function __construct(
        public readonly string $title,
        private readonly int $maxKeyBytes,
    ) {
    }

    /**
     * Whether nothing has been written into the section yet.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->keys === [];
    }

    /**
     * Whether a key of the given size still fits in.
     *
     * @param int $bytes Bytes of the key, its terminating null included.
     * @return bool
     */
    public function hasRoomFor(int $bytes): bool
    {
        return $this->keyBytes + $bytes <= $this->maxKeyBytes;
    }

    /**
     * The key, numbered when the section holds it already, as only the first of the same keys is read.
     *
     * @param string $key The key as it would be written.
     * @return string
     */
    public function uniqueKey(string $key): string
    {
        $unique = $key;
        $number = 1;
        while (isset($this->keys[mb_strtolower($unique)])) {
            $unique = $key . ' (' . ++$number . ')';
        }

        return $unique;
    }

    /**
     * Writes a line.
     *
     * @param string $key The key, unique within the section.
     * @param int $bytes Bytes of the key, its terminating null included.
     * @param string $value What the key stands for.
     * @return void
     */
    public function add(string $key, int $bytes, string $value): void
    {
        $this->keys[mb_strtolower($key)] = true;
        $this->keyBytes += $bytes;
        $this->lines[] = $key . ' = ' . $value;
    }

    /**
     * Separates what follows by an empty line.
     *
     * @return void
     */
    public function addBlankLine(): void
    {
        if ($this->lines !== [] && end($this->lines) !== '') {
            $this->lines[] = '';
        }
    }

    /**
     * The lines written, without the heading of the section.
     *
     * @return list<string>
     */
    public function lines(): array
    {
        return $this->lines;
    }
}
