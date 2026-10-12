<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::query()
            ->published()
            ->orderBy('id')
            ->get([
                'public_slug',
                'updated_at',
            ]);

        return response()
            ->view(
                'public.sitemap',
                [
                    'products' => $products,
                ],
            )
            ->header(
                'Content-Type',
                'application/xml; charset=UTF-8',
            );
    }
}
