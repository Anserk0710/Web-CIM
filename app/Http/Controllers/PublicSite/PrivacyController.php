<?php

// path: app/Http/Controllers/PublicSite/PrivacyController.php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Illuminate\Contracts\View\View;

class PrivacyController extends Controller
{
    public function __invoke(): View
    {
        $page = SitePage::query()
            ->where('key', 'PRIVACY')
            ->with('sections')
            ->first();

        $section = $page?->sections
            ->firstWhere('key', 'policy');

        $policy = (
            $section !== null
            && $section->is_enabled
        )
            ? ($section->data ?? [])
            : [];

        return view(
            'public.privacy',
            [
                'page' => $page,
                'policy' => $policy,
            ],
        );
    }
}
