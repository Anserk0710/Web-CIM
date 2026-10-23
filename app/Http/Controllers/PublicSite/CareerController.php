<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\CareerStatus;
use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\ConsentVersion;
use App\Support\PublicSite\PageContent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index(
        Request $request,
        PageContent $content,
    ): View {
        $pageData = $content->forKey(
            'CAREER',
        );

        $page = $pageData['page'];
        $sections = $pageData['sections'];

        $hero = $content->data(
            $sections,
            'hero',
        );

        $branding = $content->data(
            $sections,
            'career_branding',
        );

        $search = mb_substr(
            trim(
                (string) $request->query(
                    'q',
                    '',
                ),
            ),
            0,
            100,
        );

        $now = now();

        $query = Career::query()
            ->where(
                'status',
                CareerStatus::Open->value,
            )
            ->where(
                fn (Builder $inner) => $inner
                    ->whereNull('opens_at')
                    ->orWhere(
                        'opens_at',
                        '<=',
                        $now,
                    ),
            )
            ->where(
                fn (Builder $inner) => $inner
                    ->whereNull('closes_at')
                    ->orWhere(
                        'closes_at',
                        '>',
                        $now,
                    ),
            );

        if ($search !== '') {
            $query->where(
                fn (Builder $inner) => $inner
                    ->where(
                        'title',
                        'like',
                        '%'.$search.'%',
                    )
                    ->orWhere(
                        'department',
                        'like',
                        '%'.$search.'%',
                    )
                    ->orWhere(
                        'location',
                        'like',
                        '%'.$search.'%',
                    ),
            );
        }

        $careers = $query
            ->orderBy('closes_at')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view(
            'public.careers.index',
            [
                'page' => $page,
                'hero' => $hero,
                'branding' => $branding,
                'careers' => $careers,

                'filters' => [
                    'q' => $search,
                ],
            ],
        );
    }

    public function show(
        string $slug,
        PageContent $content,
    ): View {
        $career = Career::query()
            ->where(
                'slug',
                $slug,
            )
            ->whereIn(
                'status',
                [
                    CareerStatus::Open->value,
                    CareerStatus::Closed->value,
                ],
            )
            ->firstOrFail();

        if (
            $career->status
            === CareerStatus::Open
            && $career->opens_at !== null
            && $career->opens_at->isFuture()
        ) {
            abort(404);
        }

        $acceptingApplications = $career
            ->isAcceptingApplicationsAt(
                now(),
            );

        $activeConsent = ConsentVersion::query()
            ->effective()
            ->first();

        $privacyPage = $content->forKey(
            'PRIVACY',
        );

        $privacyPolicy = $content->data(
            $privacyPage['sections'],
            'policy',
        );

        $privacyReady = trim(
            (string) (
                $privacyPolicy['body']
                ?? ''
            ),
        ) !== ''
            && trim(
                (string) (
                    $privacyPolicy['version']
                    ?? ''
                ),
            ) !== '';

        $canApply = $acceptingApplications
            && $activeConsent !== null
            && $privacyReady;

        $jobPosting = null;

        if ($canApply) {
            $jobPosting = [
                '@context' => 'https://schema.org',
                '@type' => 'JobPosting',

                'title' => $career->title,

                'description' => strip_tags(
                    (string) (
                        $career->summary
                        ?: $career->responsibilities
                    ),
                ),

                'datePosted' => $career
                    ->created_at
                    ->toIso8601String(),

                'employmentType' => $career
                    ->employment_type
                    ->value,

                'hiringOrganization' => [
                    '@type' => 'Organization',

                    'name' => (string) config(
                        'app.name',
                        'PT Cahya Intan Medika',
                    ),

                    'sameAs' => url('/'),
                ],

                'jobLocation' => [
                    '@type' => 'Place',

                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressLocality' => $career->location,
                        'addressCountry' => 'ID',
                    ],
                ],
            ];

            if ($career->closes_at !== null) {
                $jobPosting[
                    'validThrough'
                ] = $career
                    ->closes_at
                    ->toIso8601String();
            }
        }

        return view(
            'public.careers.show',
            [
                'career' => $career,
                'canApply' => $canApply,

                'acceptingApplications' => $acceptingApplications,

                'activeConsent' => $activeConsent,
                'privacyReady' => $privacyReady,
                'privacyPolicy' => $privacyPolicy,
                'jobPosting' => $jobPosting,
            ],
        );
    }
}
