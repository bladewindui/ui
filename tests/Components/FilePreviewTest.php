<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FilePreviewTest extends TestCase
{
    use RendersComponents;

    private const ROOT = '//div[contains(concat(" ", normalize-space(@class), " "), " bw-file-preview ")]';
    private const REMOVE = '//a[@data-bw-file-preview-remove]';
    private const DOWNLOAD = '//a[@download]';
    private const IMG = '//img';
    private const ICON = '//*[local-name()="svg"]';

    #[Test]
    public function it_renders_the_filename_and_a_generic_icon_by_default(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" />');

        $this->assertStringContainsString('report.pdf', $html);
        $this->assertNoElement($html, self::IMG);
    }

    #[Test]
    public function a_thumbnail_shows_an_image_instead_of_the_icon(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="photo.jpg" thumbnail="/img/photo-thumb.jpg" />');

        $this->assertAttribute($html, self::IMG, 'src', '/img/photo-thumb.jpg');
    }

    #[Test]
    public function the_size_is_formatted_into_human_units(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" size="2621440" />');

        $this->assertStringContainsString('2.5 MB', $html);
    }

    #[Test]
    public function a_non_numeric_size_renders_no_size_line(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" size="unknown" />');

        $this->assertStringNotContainsString('unknown', $html);
    }

    #[Test]
    public function removable_defaults_to_a_delegated_remove_control(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" />');

        $this->assertElementCount($html, self::REMOVE, 1);
    }

    #[Test]
    public function removable_false_hides_the_remove_control(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" removable="false" />');

        $this->assertNoElement($html, self::REMOVE);
    }

    #[Test]
    public function a_custom_on_remove_replaces_the_delegated_default(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" on-remove="deleteFile(1)" />');

        $this->assertNoElement($html, self::REMOVE);
        $this->assertAttribute($html, '//a', 'onclick', 'deleteFile(1)');
    }

    #[Test]
    public function a_url_renders_a_download_link(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" url="/files/report.pdf" />');

        $this->assertAttribute($html, self::DOWNLOAD, 'href', '/files/report.pdf');
    }

    #[Test]
    public function no_url_renders_no_download_link(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" />');

        $this->assertNoElement($html, self::DOWNLOAD);
    }

    #[Test]
    public function downloadable_false_hides_the_download_link_even_with_a_url(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" url="/files/report.pdf" downloadable="false" />');

        $this->assertNoElement($html, self::DOWNLOAD);
    }

    #[Test]
    public function consumer_classes_are_appended(): void
    {
        $html = $this->render('<x-bladewind::file-preview name="report.pdf" class="mt-4" />');

        $this->assertHasClasses($html, self::ROOT, ['mt-4']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config(['bladewind.file_preview.removable' => false]);

        $html = $this->render('<x-bladewind::file-preview name="report.pdf" />');

        $this->assertNoElement($html, self::REMOVE);
    }
}
