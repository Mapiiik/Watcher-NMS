<?php
declare(strict_types=1);

namespace App\Pinger;

use Transliterator;
use UConverter;
use UnexpectedValueException;

/**
 * An address book written the way McLEI's Pinger reads it.
 *
 * Pinger reads an INI file: a section is a tab, a key is the name shown and its value the address
 * pinged, a key whose value is no address is a heading. It reads the key names of a section into
 * a buffer of 16 KiB, so a larger tab goes on in another section.
 */
final class AdrFile
{
    /**
     * Bytes the key names of one section may take, kept under the buffer Pinger reads them into.
     */
    public const SECTION_KEY_BYTES = 16000;

    /**
     * What the key of a heading stands for, which is no address.
     */
    private const HEADING_VALUE = '-';

    private ?Transliterator $transliterator = null;

    /**
     * @param string $charset The code page of the Windows that Pinger runs on, as ICU names it.
     * @param int $sectionKeyBytes Bytes the key names of one section may take.
     */
    public function __construct(
        private readonly string $charset = 'cp1250',
        private readonly int $sectionKeyBytes = self::SECTION_KEY_BYTES,
    ) {
    }

    /**
     * The file, in the code page Pinger reads it in.
     *
     * @param list<\App\Pinger\Tab> $tabs The tabs in the order they are shown in.
     * @return string
     */
    public function render(array $tabs): string
    {
        $lines = [];
        /** @var array<string, true> $titles */
        $titles = [];

        foreach ($tabs as $tab) {
            foreach ($this->sections($tab) as $section) {
                // a section named twice is read as one, so a title is numbered like a key is
                $base = $this->representable($this->cleanTitle($section->title));
                $title = $base;
                $number = 1;
                while (isset($titles[mb_strtolower($title)])) {
                    $title = $base . ' (' . ++$number . ')';
                }
                $titles[mb_strtolower($title)] = true;

                $lines[] = '[' . $title . ']';
                array_push($lines, ...$section->lines());
                if (end($lines) !== '') {
                    $lines[] = '';
                }
            }
        }

        return $this->toCharset(implode("\r\n", $lines) . ($lines === [] ? '' : "\r\n"));
    }

    /**
     * The sections a tab takes, a block kept whole in one of them where it fits into one.
     *
     * @param \App\Pinger\Tab $tab The tab.
     * @return list<\App\Pinger\AdrSection>
     */
    private function sections(Tab $tab): array
    {
        $sections = [];
        $section = new AdrSection($tab->title, $this->sectionKeyBytes);

        foreach ($tab->blocks as $block) {
            $heading = $this->headingKey($block->heading);
            $entries = [];
            foreach ($block->hosts as $host) {
                $entries[] = [$this->hostKey($host), $host->ipAddress];
            }

            $blockBytes = $this->keyBytes($heading);
            foreach ($entries as [$key]) {
                $blockBytes += $this->keyBytes($key);
            }
            if (!$section->isEmpty() && !$section->hasRoomFor($blockBytes) && $blockBytes <= $this->sectionKeyBytes) {
                $sections[] = $section;
                $section = $this->nextSection($tab, $sections);
            }

            $this->addLine($section, $heading, self::HEADING_VALUE);
            foreach ($entries as [$key, $value]) {
                if (!$section->hasRoomFor($this->keyBytes($section->uniqueKey($key)))) {
                    // the block goes on in the next section, headed again so it says whose it is
                    $sections[] = $section;
                    $section = $this->nextSection($tab, $sections);
                    $this->addLine($section, $heading, self::HEADING_VALUE);
                }
                $this->addLine($section, $key, $value);
            }
            $section->addBlankLine();
        }

        if (!$section->isEmpty()) {
            $sections[] = $section;
        }

        return $sections;
    }

    /**
     * The section a tab goes on in, numbered after the ones it took already.
     *
     * @param \App\Pinger\Tab $tab The tab.
     * @param list<\App\Pinger\AdrSection> $sections The sections taken already.
     * @return \App\Pinger\AdrSection
     */
    private function nextSection(Tab $tab, array $sections): AdrSection
    {
        return new AdrSection($tab->title . ' - ' . (count($sections) + 1), $this->sectionKeyBytes);
    }

    /**
     * Writes a line under a key not written in the section yet.
     *
     * @param \App\Pinger\AdrSection $section The section.
     * @param string $key The key as it would be written.
     * @param string $value What the key stands for.
     * @return void
     */
    private function addLine(AdrSection $section, string $key, string $value): void
    {
        $key = $section->uniqueKey($key);
        $section->add($key, $this->keyBytes($key), $value);
    }

    /**
     * The key of a heading.
     *
     * @param string $heading What the block is headed with.
     * @return string
     */
    private function headingKey(string $heading): string
    {
        return '- ' . $this->representable($this->cleanKey($heading)) . ' -';
    }

    /**
     * The key of an address, marked with a plus for a contact of an access point.
     *
     * @param \App\Pinger\Host $host The address.
     * @return string
     */
    private function hostKey(Host $host): string
    {
        $name = $this->representable($this->cleanKey($host->name));
        if ($name === '') {
            $name = $host->ipAddress;
        }

        return ($host->isAccessPointContact ? '+' : '') . $name;
    }

    /**
     * The text without what the file would read differently: line ends, the equals sign, brackets,
     * and a semicolon or plus at the start.
     *
     * @param string $text The text.
     * @return string
     */
    private function cleanKey(string $text): string
    {
        $text = (string)preg_replace('/[\x00-\x1F\x7F=\[\]]+/u', ' ', $text);
        $text = trim((string)preg_replace('/\s+/u', ' ', $text));

        return ltrim($text, ';+ ');
    }

    /**
     * The title of a section, which is not to start with the asterisk of the sections Pinger keeps for itself.
     *
     * @param string $text The title.
     * @return string
     */
    private function cleanTitle(string $text): string
    {
        $title = ltrim($this->cleanKey($text), '*; ');

        return $title === '' ? '-' : $title;
    }

    /**
     * Bytes a key takes in the buffer Pinger reads the keys into.
     *
     * @param string $key The key.
     * @return int
     */
    private function keyBytes(string $key): int
    {
        return strlen($this->toCharset($key)) + 1;
    }

    /**
     * The text in letters the code page has, the others spelled without their accents.
     *
     * @param string $text The text.
     * @return string
     */
    private function representable(string $text): string
    {
        if ($this->fromCharset($this->toCharset($text)) === $text) {
            return $text;
        }

        $this->transliterator ??= Transliterator::create('Any-Latin; Latin-ASCII');
        $transliterated = $this->transliterator?->transliterate($text);

        return is_string($transliterated) ? $transliterated : $text;
    }

    /**
     * The text in the code page, a character it does not have written as a question mark.
     *
     * @param string $text The text in UTF-8.
     * @return string
     * @throws \UnexpectedValueException When the code page is not known.
     */
    private function toCharset(string $text): string
    {
        $converted = UConverter::transcode($text, $this->charset, 'UTF-8', ['to_subst' => '?']);
        if (!is_string($converted)) {
            throw new UnexpectedValueException(sprintf('The code page %s is not known.', $this->charset));
        }

        return $converted;
    }

    /**
     * The text read back from the code page.
     *
     * @param string $text The text in the code page.
     * @return string
     */
    private function fromCharset(string $text): string
    {
        $converted = UConverter::transcode($text, 'UTF-8', $this->charset);

        return is_string($converted) ? $converted : '';
    }
}
