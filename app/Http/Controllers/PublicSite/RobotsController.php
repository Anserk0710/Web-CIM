<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        if (! app()->environment('production')) {
            $content = "User-agent: *\nDisallow: /\n";
        } else {
            $content = implode("\n", [
                'User-agent: *',
                'Allow: /',
                'Sitemap: '.route('sitemap'),
                '',
            ]);
        }

        return response(
            $content,
            200,
            [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
