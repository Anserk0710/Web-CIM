<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\PublicSite\PageContent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class CompanyController extends Controller
{
    public function __invoke(PageContent $content): View
    {
        $pageData = $content->forKey('COMPANY');
        $page = $pageData['page'];
        $sections = $pageData['sections'];

        $hero = $content->data($sections, 'hero');
        $story = $content->data($sections, 'story');
        $visionMission = $content->data(
            $sections,
            'vision_mission',
        );
        $valuesData = $content->data($sections, 'values');
        $milestonesData = $content->data(
            $sections,
            'milestones',
        );
        $metricsData = $content->data($sections, 'metrics');
        $coverageData = $content->data($sections, 'coverage');

        $storyImage = isset($story['image_id'])
            ? Media::query()
                ->whereKey((int) $story['image_id'])
                ->where('mime', 'like', 'image/%')
                ->first()
            : null;

        $metrics = collect(
            $content->listValue(
                $metricsData['items'] ?? [],
            ),
        )
            ->filter(
                function (mixed $item): bool {
                    if (
                        ! is_array($item)
                        || empty($item['label'])
                        || ! isset($item['value'])
                        || empty($item['verified_at'])
                    ) {
                        return false;
                    }

                    try {
                        return Carbon::parse(
                            $item['verified_at'],
                        )->lessThanOrEqualTo(now());
                    } catch (\Throwable) {
                        return false;
                    }
                },
            )
            ->values();

        return view('public.company', [
            'page' => $page,
            'sections' => $sections,
            'hero' => $hero,
            'story' => $story,
            'storyImage' => $storyImage,
            'visionMission' => $visionMission,
            'valuesHeading' => $valuesData['heading'] ?? null,
            'values' => $content->listValue(
                $valuesData['items'] ?? [],
            ),
            'milestonesHeading' => $milestonesData['heading']
                ?? null,
            'milestones' => $content->listValue(
                $milestonesData['items'] ?? [],
            ),
            'metrics' => $metrics,
            'coverageData' => $coverageData,
        ]);
    }
}
