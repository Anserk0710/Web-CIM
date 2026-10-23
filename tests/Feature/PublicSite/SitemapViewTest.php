<?php

namespace Tests\Feature\PublicSite;

use DOMDocument;
use Tests\TestCase;

class SitemapViewTest extends TestCase
{
    public function test_sitemap_renders_valid_xml_with_declaration_and_escaped_urls(): void
    {
        $url = 'https://example.com/products?category=medical&brand=cim';

        $content = view('public.sitemap', [
            'urls' => [
                ['loc' => $url, 'lastmod' => '2026-10-21 12:30:00'],
                ['loc' => 'https://example.com/', 'lastmod' => null],
            ],
        ])->render();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $content);

        $xml = new DOMDocument;

        $this->assertTrue($xml->loadXML($content));
        $this->assertSame('http://www.sitemaps.org/schemas/sitemap/0.9', $xml->documentElement->namespaceURI);
        $this->assertSame(2, $xml->getElementsByTagName('url')->length);
        $this->assertSame($url, $xml->getElementsByTagName('loc')->item(0)->textContent);
        $this->assertSame(1, $xml->getElementsByTagName('lastmod')->length);
        $this->assertSame('2026-10-21', $xml->getElementsByTagName('lastmod')->item(0)->textContent);
    }
}
