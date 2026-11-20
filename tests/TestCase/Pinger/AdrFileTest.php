<?php
declare(strict_types=1);

namespace App\Test\TestCase\Pinger;

use App\Pinger\AdrFile;
use App\Pinger\AdrSection;
use App\Pinger\Block;
use App\Pinger\Host;
use App\Pinger\Tab;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Pinger\AdrFile Test Case
 */
#[UsesClass(AdrFile::class)]
#[UsesClass(AdrSection::class)]
class AdrFileTest extends TestCase
{
    /**
     * A tab is a section, a block a heading followed by its addresses, a contact is marked with a
     * plus, and the lines end the way Windows ends them.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testATabIsASectionOfHeadingsAndAddresses(): void
    {
        $file = new AdrFile('UTF-8');

        $content = $file->render([
            new Tab('North', [
                new Block('North A', [
                    new Host('North A-1', '10.0.0.32'),
                    new Host('Landlord', '192.0.2.2', true),
                ]),
            ]),
            new Tab('South', [new Block('South A', [new Host('South A-1', '10.0.1.32')])]),
        ]);

        $this->assertSame(
            "[North]\r\n- North A - = -\r\nNorth A-1 = 10.0.0.32\r\n+Landlord = 192.0.2.2\r\n\r\n"
            . "[South]\r\n- South A - = -\r\nSouth A-1 = 10.0.1.32\r\n\r\n",
            $content,
        );
    }

    /**
     * Only the first of the same keys is read, and Windows compares them without regard to case,
     * so the others are numbered. Tabs of the same title are numbered the same way.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testKeysAndTitlesThatRepeatAreNumbered(): void
    {
        $file = new AdrFile('UTF-8');

        $content = $file->render([
            new Tab('Same', [
                new Block('Block', [
                    new Host('Namesake', '10.0.0.1'),
                    new Host('namesake', '10.0.0.2'),
                    new Host('Namesake', '10.0.0.3'),
                ]),
            ]),
            new Tab('same', [new Block('Block', [new Host('Namesake', '10.0.0.4')])]),
        ]);

        $this->assertSame(
            "[Same]\r\n- Block - = -\r\nNamesake = 10.0.0.1\r\nnamesake (2) = 10.0.0.2\r\nNamesake (3) = 10.0.0.3\r\n\r\n"
            . "[same (2)]\r\n- Block - = -\r\nNamesake = 10.0.0.4\r\n\r\n",
            $content,
        );
    }

    /**
     * A block that does not fit into what is left of a section starts the next one, as long as it
     * fits into a section of its own.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testABlockIsKeptWholeInTheNextSection(): void
    {
        // the keys of the first block take 21 bytes, those of the second 17
        $file = new AdrFile('UTF-8', 30);

        $content = $file->render([
            new Tab('Tab', [
                new Block('B1', [
                    new Host('one', '10.0.0.1'),
                    new Host('two', '10.0.0.2'),
                    new Host('three', '10.0.0.3'),
                ]),
                new Block('B2', [new Host('four', '10.0.0.4'), new Host('five', '10.0.0.5')]),
            ]),
        ]);

        $this->assertSame(
            "[Tab]\r\n- B1 - = -\r\none = 10.0.0.1\r\ntwo = 10.0.0.2\r\nthree = 10.0.0.3\r\n\r\n"
            . "[Tab - 2]\r\n- B2 - = -\r\nfour = 10.0.0.4\r\nfive = 10.0.0.5\r\n\r\n",
            $content,
        );
    }

    /**
     * A block too large for any section goes on in the next one, headed again.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testABlockTooLargeForASectionGoesOnInTheNext(): void
    {
        // the heading and the first two addresses take the 20 bytes exactly
        $file = new AdrFile('UTF-8', 20);

        $content = $file->render([
            new Tab('Tab', [
                new Block('Big', [
                    new Host('alpha', '10.0.0.1'),
                    new Host('bravo', '10.0.0.2'),
                    new Host('charlie', '10.0.0.3'),
                ]),
            ]),
        ]);

        $this->assertSame(
            "[Tab]\r\n- Big - = -\r\nalpha = 10.0.0.1\r\nbravo = 10.0.0.2\r\n\r\n"
            . "[Tab - 2]\r\n- Big - = -\r\ncharlie = 10.0.0.3\r\n\r\n",
            $content,
        );
    }

    /**
     * What the file would read differently is left out of a name, a name left empty is replaced
     * by the address, and a title does not start like the sections Pinger keeps for itself.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testWhatTheFileWouldReadDifferentlyIsLeftOut(): void
    {
        $file = new AdrFile('UTF-8');

        $content = $file->render([
            new Tab('*Icons', [
                new Block('[Block]', [
                    new Host("; +Name = [x]\nnext", '10.0.0.1'),
                    new Host(' ', '10.0.0.2'),
                ]),
            ]),
        ]);

        $this->assertSame(
            "[Icons]\r\n- Block - = -\r\nName x next = 10.0.0.1\r\n10.0.0.2 = 10.0.0.2\r\n\r\n",
            $content,
        );
    }

    /**
     * The file is in the code page Pinger reads, and a letter the code page does not have is
     * spelled without its accents.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testTheFileIsInTheCodePage(): void
    {
        $file = new AdrFile('cp1250');

        $content = $file->render([
            new Tab('Metyšova', [new Block('Москва', [new Host('Žluťoučký kůň', '10.0.0.1')])]),
        ]);

        $this->assertSame(
            "[Mety\x9aova]\r\n- Moskva - = -\r\n\x8elu\x9dou\xe8k\xfd k\xf9\xf2 = 10.0.0.1\r\n\r\n",
            $content,
        );
    }

    /**
     * The bytes a key takes are counted in the code page, where a letter with an accent takes one,
     * not in UTF-8, where it takes two.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testTheBytesAreCountedInTheCodePage(): void
    {
        // the heading takes 6 bytes and the address 5, against 9 in UTF-8
        $file = new AdrFile('cp1250', 11);

        $content = $file->render([new Tab('Tab', [new Block('B', [new Host('čččč', '10.0.0.1')])])]);

        $this->assertSame("[Tab]\r\n- B - = -\r\n\xe8\xe8\xe8\xe8 = 10.0.0.1\r\n\r\n", $content);
    }

    /**
     * Nothing to list is an empty file.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testNothingToListIsAnEmptyFile(): void
    {
        $this->assertSame('', (new AdrFile())->render([]));
    }

    /**
     * Full-width brackets, equals signs, semicolons and plus signs, which the code page spells as
     * plain ones, are left out as the plain ones are: no tab, heading or contact can be slipped in.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testFullWidthMarksAreLeftOutAsThePlainOnes(): void
    {
        $file = new AdrFile('cp1250');

        $content = $file->render([
            new Tab('［［Tab］］', [
                new Block('＝Block＝', [
                    new Host('［Injected］ ＝ x', '10.0.0.2'),
                    new Host('；comment', '10.0.0.3'),
                    new Host('＋fake contact', '10.0.0.4'),
                ]),
            ]),
        ]);

        $this->assertSame(
            "[Tab]\r\n- Block - = -\r\nInjected x = 10.0.0.2\r\ncomment = 10.0.0.3\r\n"
            . "fake contact = 10.0.0.4\r\n\r\n",
            $content,
        );
    }

    /**
     * A block larger than a section does not leave its heading alone at the end of the section
     * before: it starts in a new one where its first address does not fit beside the heading.
     *
     * @return void
     * @link \App\Pinger\AdrFile::render()
     */
    public function testAHeadingIsNotLeftAloneAtTheEndOfASection(): void
    {
        $file = new AdrFile('UTF-8', 20);

        $content = $file->render([
            new Tab('Tab', [
                new Block('A', [new Host('x', '10.0.0.9')]),
                new Block('Big', [
                    new Host('alpha', '10.0.0.1'),
                    new Host('bravo', '10.0.0.2'),
                    new Host('charlie', '10.0.0.3'),
                ]),
            ]),
        ]);

        $this->assertSame(
            "[Tab]\r\n- A - = -\r\nx = 10.0.0.9\r\n\r\n"
            . "[Tab - 2]\r\n- Big - = -\r\nalpha = 10.0.0.1\r\nbravo = 10.0.0.2\r\n\r\n"
            . "[Tab - 3]\r\n- Big - = -\r\ncharlie = 10.0.0.3\r\n\r\n",
            $content,
        );
    }
}
